from django.core.management.base import BaseCommand

from lhsauth.models import User
from lhsgocardless.models import Customer, Subscription

import gocardless_pro as gocardless
from django.conf import settings
from pprint import pprint
import json

gc_client = gocardless.Client(
    access_token=settings.GOCARDLESS_CREDENTIALS['access_token'],
    environment=settings.GOCARDLESS_ENV
)

def get_member_subscription_id(user_id):
    if user_id is None:
        print("No member id provided")
        return None
    
    try:
        user = User.objects.get(id=user_id)
    except User.DoesNotExist:
        print("Member does not exist")
        return None

    try:
        customer = Customer.objects.get(user_id=user_id)
    except Customer.DoesNotExist:
        print("Member does not have a GoCardless customer record")
        return None

    try:
        subscription = Subscription.objects.get(customer_id=customer.id)
    except Subscription.DoesNotExist:
        print("Member does not have a GoCardless subscription record")
        return None

    return subscription.subscription


class Command(BaseCommand):
    help = "Updates the member's subscription on GoCardless to auto-retry failed payments"

    def add_arguments(self, parser):
        parser.add_argument('MemberNumber', type=int)        

    def handle(self, *args, **options):
        user_id = int(options['MemberNumber'])

        subscription_id = get_member_subscription_id(user_id)

        if subscription_id is not None:
            print(f"Member {user_id} has subscription ID: {subscription_id}")
            subscription = gc_client.subscriptions.get(subscription_id)
            if subscription is None:
                print("Subscription not found on GoCardless")
                return           
            if subscription.attributes['retry_if_possible']:
                print("Subscription is already set to auto-retry failed payments")
            else:
                subscription = gc_client.subscriptions.update(subscription_id, params={'retry_if_possible' : True })
                print("Subscription updated to auto-retry failed payments")
                pprint(vars(subscription))

