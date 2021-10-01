import json
import re
from datetime import datetime, timezone
import hashlib
import base64

from django.utils.dateparse import parse_date
from django.conf import settings

from lhsauth.models import User
from lhspayments.models import Payment

from nordigen import Client


class NordProcessor():

    client = Client(token=settings.NORDIGEN_TOKEN)
    account = settings.NORDIGEN_ACCOUNT

    def process_transactions(self):

        data = self.client.account.transactions(self.account)
        transactions = data["transactions"]["booked"]

        f = open('lhs-transactions-data.json', "r")
        transactions = json.loads(f.read())["booked"]
        f.close()

        for trn in transactions:
            # only interested in payments
            if(float(trn["transactionAmount"]["amount"]) < 0):
                # print(trn["remittanceInformationUnstructured"])
                continue
            if(trn["transactionAmount"]["currency"] != "GBP"):
                print("unable to process tranactions due to currency")
                print(trn)
                continue
            # sanity check that info field contains HS user id
            if(not re.search("HS\d{5,6}", trn["remittanceInformationUnstructured"], re.I)):
                print("unable to process tranactions due non HS user match")
                print(trn)
                continue
            self.process_transaction(trn)

    def process_transaction(self, tx):

        info = tx["remittanceInformationUnstructured"]
        user_id = self.parse_remittance_field(info).upper()
        amount = tx["transactionAmount"]["amount"]
        # generate a hash of the remittance info
        info_hash = base64.b64encode(hashlib.sha256(
            info.encode('utf-8')).digest()).decode("utf-8")[:8]
        date = tx['valueDate']

        # convert the YYYY-mm-dd date into a tz enabled datetime
        dt = datetime.strptime(
            date, "%Y-%m-%d").replace(tzinfo=timezone.utc)

        # key based off multiple fields (as no fit_id is available)
        trn_id = f"HS{user_id}-{date}-{amount}-{info_hash}"

        # get the user for this transaction
        user = User.objects.filter(id=user_id).first()

        if(not user):
            # print(f"not found user for user_id: {user_id} remittance: {info}")
            return

        print(tx)

        payments = Payment.objects.filter(user=user_id).filter(id=trn_id)

        if(payments.count() == 1):
            return
        elif(len(payments) > 1):
            raise ValueError(
                f"found more than 1 payment {payments} for: {user_id} and {trn_id}")

        p = Payment()
        p.user = user
        p.timestamp = dt
        p.id = trn_id
        p.payment_state = Payment.STATE_SUCCEEDED
        p.payment_type = Payment.TYPE_NORDAPI
        p.amount = amount
        p.save()

    def parse_remittance_field(self, field):
        match = re.search("\sHS(\d{5,6})", field, re.I)
        if(match):
            return match.group(1)
        else:
            print(f"field is {field}")
            raise ValueError('An exception occurred parsing remit infos')
