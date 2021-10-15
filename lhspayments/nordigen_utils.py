
import base64
import hashlib
import json
import re

from datetime import datetime, timezone

from django.utils.dateparse import parse_date
from django.conf import settings

from lhsauth.models import User
from lhspayments.models import Payment

from nordigen import Client


class NordProcessor():

    client = Client(token=settings.NORDIGEN_TOKEN)
    account = settings.NORDIGEN_ACCOUNT

    report = []

    def __init__(self, **kwargs):
        self.verbosity = kwargs.pop("verbosity", 1)
        self.stdout = kwargs.pop("stdout", False)
        self.dry_run = kwargs.pop("dry_run", False)

    def process_new_transactions(self):
        self.transactions = self.get_new_transactions()
        self.process_transactions()

    def get_new_transactions(self):
        data = self.client.account.transactions(self.account)
        # do we care about pending transactions?
        # self.transactions = data["transactions"]["booked"]
        return data["transactions"]["booked"]

    def process_transactions(self):
        for trn in self.transactions:
            # only interested in credits, not payments to suppliers
            if(float(trn["transactionAmount"]["amount"]) < 0):
                self.pr("supplier payment   :"
                        f'\"{trn["remittanceInformationUnstructured"]}\"', level=2)
                # self.pr(trn["remittanceInformationUnstructured"])
                # self.pr(trn["transactionAmount"])
                continue
            # can't handle non-GBP currency
            elif(trn["transactionAmount"]["currency"] != "GBP"):
                self.pr(
                    f'bad currency     : \"{trn["transactionAmount"]["currency"]}\" '
                    f'{trn["transactionAmount"]["amount"]}'
                    f'\"{trn["remittanceInformationUnstructured"]}\"',
                    level=0)
                continue
            # ignore well known payment sources, e.g. gocardless
            elif(re.match("GC C1\s+", trn["remittanceInformationUnstructured"], re.I)):
                remittence = trn["remittanceInformationUnstructured"]
                self.pr(
                    f"ignoring gocardless payment \"{remittence}\"",
                    level=3)
                # self.pr(trn)
                continue
            self.process_transaction(trn)

    def process_transaction(self, transaction):

        try:
            user_id, info, amount, date, dt, trn_id = self.parse_transaction(
                transaction)
        except ParseInfoException:
            self.pr(
                f"unable to parse  : {transaction['remittanceInformationUnstructured']}", level=2)
            return

        try:
            user = User.objects.get(pk=user_id)
        except User.DoesNotExist:
            self.pr(f"not found "
                    f"user_id: {user_id:6} remittance: \"{info}\"", level=0)
            # self.pr(transaction)
            return

        # search for duplicates of this exact tx (i.e. inserted previously
        # by this script on a previous run.
        payments = Payment.objects.filter(id=trn_id)

        if(payments.count() == 1):
            self.pr(f"found existing transaction: \"{trn_id}\"", level=2)
            return
        elif(len(payments) > 1):
            raise ValueError(
                f"found more than 1 payment {payments} for: "
                f"HS{user_id:05} and {trn_id}")
            return

        # search for payments assigned to this user
        ofx_payments = Payment.objects.filter(
            user=user_id,
            timestamp__date=dt,
            amount=amount,
            payment_type=Payment.TYPE_BANKPAYMENT
        )

        if(ofx_payments.count() > 0):
            self.pr("found existing transaction with non-matching id")
            return

        p = Payment()
        p.user = user
        p.timestamp = dt
        p.id = trn_id
        p.payment_state = Payment.STATE_SUCCEEDED
        p.payment_type = Payment.TYPE_BANKPAYMENT
        p.amount = amount

        if not self.dry_run:
            p.save()

        self.pr(f"payment created for {user} date {dt} amount {amount}")

    def parse_transaction(self, transaction):
        """parse transaction into dict"""

        info = transaction["remittanceInformationUnstructured"]
        user_id = self.parse_remittance_field(info)
        amount = transaction["transactionAmount"]["amount"]
        date = transaction['valueDate']
        # convert the YYYY-mm-dd date into a tz enabled datetime
        dt = datetime.strptime(
            date, "%Y-%m-%d").replace(tzinfo=timezone.utc)
        # create the primary key for this transaction
        trn_id = self.create_key(user_id, info, amount, date, dt)
        return [user_id, info, amount, date, dt, trn_id]

    def parse_remittance_field(self, info_field):
        """parse remittence information for HS user id value"""

        match = re.search("\sHSO?(\d{4,6})", info_field, re.I)
        if(match):
            return int(match.group(1))
        else:
            self.pr(f"field is {info_field}")
            raise ParseInfoException(
                'An exception occurred parsing remittance info')

    def create_key(self, user_id, info, amount, date, dt):
        """generate a unique key for this transaction based off fields"""

        # info_hash = self.generate_info_hash(info)
        # just use the remittence info as suffix to id string
        info_hash = info.replace(" ", "_")

        # key based off multiple fields (as no fit_id is available)
        trn_id = f"HS{user_id:05}-{date}-{amount}-{info_hash}"

        # self.pr(trn_id)
        return trn_id

    def generate_info_hash(self, info):
        "hash the remittence info field into a short relatively unique string"

        # base64 encoded
        # info_hash = base64.b64encode(
        #     hashlib.sha256(
        #         info.encode('utf-8')
        #     ).digest()
        # ).decode("utf-8")[:8]

        info_hash = hashlib.sha256(
            info.encode('utf-8')).hexdigest().upper()[:8]

        return info_hash

    def pr(self, message, level=1):
        if self.stdout and level <= self.verbosity:
            print(message)
        self.report.append({"message": message,
                            "level": level})


class ParseInfoException(Exception):
    "unable to parse the remittence info into something sensible"
    pass
