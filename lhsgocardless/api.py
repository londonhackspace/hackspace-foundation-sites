import json

from django.http import HttpResponse
from django.conf import settings
from django.views.decorators.csrf import csrf_exempt

from gocardless_pro import webhooks
from gocardless_pro.errors import InvalidSignatureError
import gocardless_pro as gocardless

from .models import EventLog, Subscription, Customer, old_mandates, old_subscriptions

from lhspayments.models import Payment
from lhspayments import membershiptools

import logging
logger = logging.getLogger(__name__)

gc_client = gocardless.Client(
    access_token=settings.GOCARDLESS_CREDENTIALS['access_token'],
    environment=settings.GOCARDLESS_ENV
)


def handle_payment_event(requst, event):
    gc_payment = gc_client.payments.get(event.links.payment)
    lhs_payment = Payment.objects.filter(id=event.links.payment).first()
    lhs_customer = Customer.objects.filter(mandate=gc_payment.links.mandate).first()
    
    logger.info(f'Handling payment event: {event.id}, action: {event.action}, status: {gc_payment.status}')
    
    # lhs_customer may be none - I think this is actually only
    # in the sandbox environment when you send test webhooks,
    # however to be safe, don't fail and update the payment
    # status if we can

    # should this activate the membership?
    if lhs_customer is not None and not lhs_customer.user.subscribed and not lhs_customer.user.terminated and Payment.gocardless_status_to_status(gc_payment.status) != Payment.STATE_FAILED:
        membershiptools.on_new_member(lhs_customer.user)

    if lhs_customer is not None and lhs_payment is None:
        lhs_payment = Payment.create_from_gocardless_payment(event.action, gc_payment, lhs_customer.user)
        lhs_payment.save()
        return lhs_customer.user

    if lhs_payment is not None:
        lhs_payment.update_from_gocardless_payment(event.action, gc_payment)
        return lhs_payment.user

    return lhs_customer.user if lhs_customer is not None else None

def handle_mandate_event(request, event):
    lhs_customer = Customer.objects.filter(mandate=event.links.mandate).first()
    user_id = old_mandates.get(event.links.mandate) if lhs_customer is None else lhs_customer.user_id

    logger.info(f'Handling mandate event: {event.id}, action: {event.action}, user_id: {user_id}')
    # the main event we care about here is them becoming invalid
    if event.action == 'cancelled' or event.action == 'failed' or event.action == 'expired':
        if lhs_customer: 
            old_mandates.add(lhs_customer.mandate, lhs_customer.user_id)
            lhs_customer.delete()
    elif event.action == 'created' and lhs_customer:
        billing_events = EventLog.objects.filter(resource_type='billing_requests', user_id = None, links__contains=f'"billing_request": "{event.links.billing_request}"')
        for billing_event in billing_events:
            billing_event.user_id = user_id
            billing_event.save()

    return user_id

def handle_subscription_event(request, event):
    gc_subscription = gc_client.subscriptions.get(event.links.subscription)
    lhs_subscription = Subscription.objects.filter(subscription=event.links.subscription).first()
    lhs_customer = Customer.objects.filter(mandate=gc_subscription.links.mandate).first()
    user_id = old_subscriptions.get(event.links.subscription) if lhs_customer is None else lhs_customer.user_id

    logger.info(f'Handling subscription event: {event.id}, action: {event.action}, status: {gc_subscription.status}, user_id: {user_id}')
    
    # add records for unknown non-expired subscriptions
    if lhs_subscription is None and not (gc_subscription.status == 'finished' or gc_subscription.status == 'cancelled'):
        lhs_subscription = Subscription.create_subscription_from_gocardless_subscription(gc_subscription)
        lhs_subscription.save()
        user_id = lhs_customer.user_id

    # remove old subscriptions
    if lhs_subscription is not None and (gc_subscription.status == 'finished' or gc_subscription.status == 'cancelled'):
        old_subscriptions.add(lhs_customer.subscription, lhs_customer.user_id)
        lhs_subscription.delete()

    # there isn't really anything we store here that should be updated
    
    return user_id

@csrf_exempt
def webhook(request):
    secret = settings.GOCARDLESS_CREDENTIALS['webhook_secret']

    signature = request.headers["webhook-signature"]
    body = request.body.strip()

    try:
        events = webhooks.parse(body.decode('utf-8'), secret, signature)

        for event in events:
            log_event = EventLog()
            log_event.id = event.id
            log_event.created_at = event.created_at
            log_event.resource_type = event.resource_type
            log_event.action = event.action
            log_event.processed = False
            # the attributes property is the unpacked json object
            log_event.links = json.dumps(event.links.attributes)
            log_event.details = json.dumps(event.details.attributes)

            try: 
                if event.resource_type == 'payments':
                    log_event.user = handle_payment_event(request, event)
                    log_event.processed = True
                elif event.resource_type == 'mandates':
                    log_event.user_id = handle_mandate_event(request, event)
                    log_event.processed = True
                elif event.resource_type == 'subscriptions':
                    log_event.user_id = handle_subscription_event(request, event)
                    log_event.processed = True
                elif hasattr(event.links, 'customer'):
                    lhs_customer = Customer.objects.filter(customer=event.links.customer).first()
                    log_event.user = lhs_customer.user if lhs_customer is not None else None
                elif hasattr(event.links, 'mandate'):
                    lhs_customer = Customer.objects.filter(mandate=event.links.mandate).first()
                    log_event.user = lhs_customer.user if lhs_customer is not None else None
                elif hasattr(event.links, 'subscription'):
                    lhs_subscription = Subscription.objects.filter(subscription=event.links.subscription).first()
                    log_event.user = lhs_subscription.customer.user if lhs_subscription is not None else None
                elif hasattr(event.links, 'payment'):
                    lhs_payment = Payment.objects.filter(id=event.links.payment).first()
                    log_event.user = lhs_payment.user if lhs_payment is not None else None
            except Exception as e:
                logger.error(f'Error processing gocardless log event {event.id}({event.resource_type}): {e}')
            finally:
                log_event.save()
        

    except InvalidSignatureError:
        # as per the GoCardless docs, return 498 here
        return HttpResponse(status=498)

    return HttpResponse(status=204)
