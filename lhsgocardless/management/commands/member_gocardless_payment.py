from email import parser

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

def get_member_customer_record(user_id):
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
    
    return customer



class Command(BaseCommand):
    help = "Creates a payment for the member on GoCardless"

    def add_arguments(self, parser):
        parser.add_argument('MemberNumber', type=int)        
        parser.add_argument('Amount', type=float)        
        parser.add_argument('Description', type=str)        


    def handle(self, *args, **options):
        user_id = int(options['MemberNumber'])

        customer_record = get_member_customer_record(user_id)
        amount = int(100 * float(options['Amount']))
        description = options['Description']

        if customer_record is not None:

            params = {
                "amount": str(amount),
                "currency": "GBP",
                "description": description,
                "retry_if_possible": True,
                "links": {
                    "mandate": customer_record.mandate
                }
            }

            payment = gc_client.payments.create(params=params)
            print(f"Payment created")

