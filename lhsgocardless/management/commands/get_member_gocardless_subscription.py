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
    help = "Gets the member's subscription info from GoCardless"

    def add_arguments(self, parser):
        parser.add_argument('MemberNumber', type=int)

    def handle(self, *args, **options):
        user_id = int(options['MemberNumber'])

        subscription_id = get_member_subscription_id(user_id)

        if subscription_id is not None:
            print(f"Member {user_id} has subscription ID: {subscription_id}")
            subscription = gc_client.subscriptions.get(subscription_id)
            pprint(subscription.attributes['retry_if_possible'])
            #pprint(vars(json.loads(subscription.attributes)))

