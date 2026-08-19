from email import parser

from django.core.management.base import BaseCommand

from lhsgocardless.models import Customer, Subscription, EventLog
from lhspayments.models import Payment

from django.conf import settings
import json


def add_users_to_eventlog(eventlog_model, payment_model, customer_model, subscription_model):
    payment_events = eventlog_model.objects.filter(resource_type='payments', user=None)
    print(f"Found {payment_events.count()} payment events without users")
    for event in payment_events:
        links = json.loads(event.links)
        print(f"Processing event {event.id} with links {event.links}")
        payment = payment_model.objects.filter(id=links['payment']).first()
        if payment is not None:
            event.user_id = payment.user_id
            event.save()
        else:
            print(f"Could not find payment {links['payment']} for payment event {event.id}")

    mandate_events = eventlog_model.objects.filter(resource_type='mandates', user=None)
    print(f"Found {mandate_events.count()} mandate events without users")
    for event in mandate_events:
        links = json.loads(event.links)
        print(f"Processing event {event.id} with links {event.links}")
        customer = customer_model.objects.filter(mandate=links['mandate']).first()
        if customer is not None:
            event.user_id = customer.user_id
            event.save()
        else:
            print(f"Could not find mandate {links['mandate']} for mandate event {event.id}")

    subscription_events = eventlog_model.objects.filter(resource_type='subscriptions', user=None)
    print(f"Found {subscription_events.count()} subscription events without users")
    for event in subscription_events:
        links = json.loads(event.links)
        print(f"Processing event {event.id} with links {event.links}")
        subscription = subscription_model.objects.filter(subscription=links['subscription']).first()
        if subscription is not None:
            event.user_id = subscription.customer.user_id
            event.save()
        else:
            print(f"Could not find subscription {links['subscription']} for subscription event {event.id}")

    old_subscriptions = {}
    remaining_events = eventlog_model.objects.filter(user=None).exclude(resource_type='payments').exclude(resource_type='mandates')
    print(f"Found {remaining_events.count()} other events without users - checking for payment links")
    for event in remaining_events:
        links = json.loads(event.links)
        print(f"Processing event {event.id} with links {event.links}")
        if 'payment' in links and links['payment'] is not None:
            payment = payment_model.objects.filter(id=links['payment']).first()
            if payment is not None:
                event.user_id = payment.user_id
                event.save()
                if 'subscription' in links and links['subscription'] is not None:
                    old_subscriptions[links['subscription']] = payment.user_id
    
    remaining_events = eventlog_model.objects.filter(user=None).exclude(resource_type='payments')
    print(f"Found {remaining_events.count()} other events without users, checking for customer links")
    for event in remaining_events:
        links = json.loads(event.links)
        print(f"Processing event {event.id} with links {event.links}")
        if 'customer' in links and links['customer'] is not None:
            customer = customer_model.objects.filter(customer=links['customer']).first()
            if customer is not None:
                event.user_id = customer.user_id
                event.save()
    
    remaining_events = eventlog_model.objects.filter(user=None).exclude(resource_type='payments')
    print(f"Found {remaining_events.count()} other events without users, checking for mandate links")
    for event in remaining_events:
        links = json.loads(event.links)
        print(f"Processing event {event.id} with links {event.links}")
        if 'mandate' in links and links['mandate'] is not None:
            customer = customer_model.objects.filter(mandate=links['mandate']).first()
            if customer is not None:
                event.user_id = customer.user_id
                event.save()
    
    remaining_events = eventlog_model.objects.filter(user=None).exclude(resource_type='payments')
    print(f"Found {remaining_events.count()} other events without users, checking for subscription links")
    for event in remaining_events:
        links = json.loads(event.links)
        print(f"Processing event {event.id} with links {event.links}")
        if 'subscription' in links and links['subscription'] is not None:
            if links['subscription'] in old_subscriptions:
                event.user_id = old_subscriptions[links['subscription']]
                event.save()
            else:
                subscription = subscription_model.objects.filter(subscription=links['subscription']).first()
                if subscription is not None:
                    event.user_id = subscription.customer.user_id
                    event.save()
 
    remaining_events = eventlog_model.objects.filter(user=None).exclude(resource_type='payments')
    print(f"Found {remaining_events.count()} other events without users, checking parent events")
    for event in remaining_events:
        links = json.loads(event.links)
        print(f"Processing event {event.id} with links {event.links}")
        if 'parent_event' in links and links['parent_event'] is not None:
            parent_event = eventlog_model.objects.filter(id=links['parent_event']).first()
            if parent_event is not None:
                event.user_id = parent_event.user_id
                event.save()
    
    old_mandates = {}
    subscription_events_with_parents = eventlog_model.objects.exclude(user=None).filter(resource_type = 'subscriptions', links__contains='"parent_event"')
    print(f"Found {subscription_events_with_parents.count()} events with parent events")
    for event in subscription_events_with_parents:
        links = json.loads(event.links)
        print(f"Processing event {event.id} with links {event.links}")
        if 'parent_event' in links and links['parent_event'] is not None:
            parent_event = eventlog_model.objects.filter(id=links['parent_event']).first()
            if parent_event is not None:
                print(f"parent event links: {parent_event.links}")
                parent_event.user_id = event.user_id
                parent_event.save()
                parent_links = json.loads(parent_event.links)
                if 'mandate' in parent_links and parent_links['mandate'] is not None:
                    old_mandates[parent_links['mandate']] = event.user_id
            else:
                print(f"Could not find parent event {links['parent_event']} for subscription event {event.id}")

    if old_mandates:
        print(f"Found {len(old_mandates)} old mandates to update")
        remaining_events = eventlog_model.objects.filter(user=None, links__contains='"mandate"')
        for event in remaining_events:
            links = json.loads(event.links)
            print(f"Processing event {event.id} with links {event.links}")
            if links['mandate'] in old_mandates:
                print(f"Updating event {event.id} with user id {old_mandates[links['mandate']]}")
                event.user_id = old_mandates[links['mandate']]
                event.save()
            else:  
                print(f"Could not find old mandate {links['mandate']} for event {event.id}")
    else: 
        print("No old mandates found to update")

class Command(BaseCommand):
    help = "Adds user id to GoCardless event log entries where possible" 


    def handle(self, *args, **options):

        add_users_to_eventlog(EventLog, Payment, Customer, Subscription)


