import json

from django.http import HttpResponse
from django.conf import settings
from django.views.decorators.csrf import csrf_exempt

from gocardless_pro import webhooks
from gocardless_pro.errors import InvalidSignatureError
import gocardless_pro as gocardless

from .models import EventLog, Subscription, Customer

from lhspayments.models import Payment
from lhspayments import membershiptools

gc_client = gocardless.Client(
    access_token=settings.GOCARDLESS_CREDENTIALS['access_token'],
    environment=settings.GOCARDLESS_ENV
)


def handle_payment_event(requst, event):
    gc_payment = gc_client.payments.get(event.links.payment)
    lhs_payment = Payment.objects.filter(id=event.links.payment).first()
    lhs_customer = Customer.objects.filter(mandate=gc_payment.links.mandate).first()
    
    # lhs_customer may be none - I think this is actually only
    # in the sandbox environment when you send test webhooks,
    # however to be safe, don't fail and update the payment
    # status if we can

    # should this activate the membership?
    if lhs_customer is not None and not lhs_customer.user.subscribed and Payment.gocardless_status_to_status(gc_payment.status) != Payment.STATE_FAILED:
        membershiptools.on_new_member(lhs_customer.user)

    if lhs_customer is not None and lhs_payment is None:
        lhs_payment = Payment.create_from_gocardless_payment(gc_payment, lhs_customer.user)
        lhs_payment.save()
        return

    lhs_payment.update_from_gocardless_payment(gc_payment)

def handle_mandate_event(request, event):
    # the main event we care about here is them becoming invalid
    if event.action == 'cancelled' or event.action == 'failed' or event.action == 'expired':
        lhs_customer = Customer.objects.filter(mandate=event.links.mandate).first()

        if lhs_customer: 
            lhs_customer.delete()

def handle_subscription_event(request, event):
    gc_subscription = gc_client.subscriptions.get(event.links.subscription)
    lhs_subscription = Subscription.objects.filter(subscription=event.links.subscription).first()

    # add records for unknown non-expired subscriptions
    if lhs_subscription is None and not (gc_subscription.status == 'finished' or gc_subscription.status == 'cancelled'):
        lhs_subscription = Subscription.create_subscription_from_gocardless_subscription(gc_subscription)
        lhs_subscription.save()

    # remove old subscriptions
    if lhs_subscription is not None and (gc_subscription.status == 'finished' or gc_subscription.status == 'cancelled'):
        lhs_subscription.delete()

    # there isn't really anything we store here that should be updated

@csrf_exempt
def webhook(request):
    secret = settings.GOCARDLESS_CREDENTIALS['webhook_secret']

    signature = request.META["HTTP_WEBHOOK_SIGNATURE"]
    body = request.body.strip()

    try:
        events = webhooks.parse(body, secret, signature)

        for event in events:
            log_event = EventLog()
            log_event.id = event.id
            log_event.created_at = event.created_at
            log_event.resource_type = event.resource_type
            log_event.action = event.action
            # the attributes property is the unpacked json object
            log_event.links = json.dumps(event.links.attributes)
            log_event.details = json.dumps(event.details.attributes)
            log_event.save()

            if event.resource_type == 'payments':
                handle_payment_event(request, event)
                log_event.processed = True
                log_event.save()
            elif event.resource_type == 'mandates':
                handle_mandate_event(request, event)
                log_event.processed = True
                #log_event.save()
            elif event.resource_type == 'subscriptions':
                handle_subscription_event(request, event)
                log_event.processed = True
                log_event.save()

    except InvalidSignatureError:
        # as per the GoCardless docs, return 498 here
        return HttpResponse(status=498)

    return HttpResponse(status=204)
