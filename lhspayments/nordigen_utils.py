
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

    # list of results from transaction processing attempts
    # currently handed to the django command object for rendering
    report = []

    def __init__(self, **kwargs):
        # this is passed through from django command invocation atm
        self.verbosity = kwargs.pop("verbosity", 1)
        # set this to true to force inline output during processing
        self.stdout = kwargs.pop("stdout", False)
        self.dry_run = kwargs.pop("dry_run", False)
        self.subs_date = kwargs.pop("subs_date", False)

    def process_new_transactions(self):
        self.transactions = self.get_new_transactions()
        self.process_transactions()

    def get_new_transactions(self):
        "get transactions from remote API"
        data = self.client.account.transactions(self.account)
        # do we care about pending transactions?
        # self.transactions = data["transactions"]["booked"]
        return data["transactions"]["booked"]

    def process_transactions(self):
        for trn in self.transactions:
            t = NordigenAccountTransaction.from_json(trn)

            # filter out unwanted transaction types
            if not t.is_deposit():
                self.pr_t(t, f"was disbursement of funds to \"{t.info}\"", 2)
            elif t.currency != 'GBP':
                self.pr_t(t,
                          f"was non uk currency ({t.currency}) transactions "
                          f"for \"{t.info}\"",
                          1)
            elif t.gocardless:
                self.pr_t(t, f"was gocardless deposit \"{t.info}\"", 2)
            elif not t.user_id:
                self.pr_t(t,
                          f"unable to parse info for user \"{t.info}\" "
                          f"might be pledge or donations",
                          2)
            elif t.amount < 5.00:
                self.pr_t(t, f"amount was less than £5.00 \"{t.info}\"", 2)
            else:
                self.process_transaction(t)

    def process_transaction(self, t):

        messages = []

        try:
            user = User.objects.get(pk=t.user_id)
        except User.DoesNotExist:
            self.pr_t(t,
                      f"user {t.user_id} not found in db \"{t.info}\"",
                      level=0)
            return

        # search for duplicates of this exact tx (i.e. inserted
        # by this script on a previous run (using concatenated key for trn_id)
        try:
            existing_payment = Payment.objects.get(pk=t.trn_id)
            self.pr_t(t,
                      f"found existing transaction: \"{t.trn_id}\"", level=2)
            return
        except Payment.DoesNotExist:
            existing_payment = None

        # search for payments assigned to this user with similar
        # values for multi field matching. i.e. inserted by reconcile.rb
        ofx_payments = Payment.objects.filter(
            user=t.user_id,
            timestamp__date=t.tzdt,
            amount=t.amount,
            payment_type=Payment.TYPE_BANKPAYMENT
        )

        if(ofx_payments.count() > 0):
            self.pr_t(t,
                      "found existing transaction with non-matching id"
                      f' {t.user_id} - info is {t.info}'
                      )
            return

        # handle edge cases from reconcile.rb
        if user.terminated:
            self.pr_t(t,
                      "terminated user "
                      f' {t.user_id} - info is {t.info}',
                      0
                      )
            return

        if not user.address:
            self.pr_t(t,
                      "user address is empty "
                      f' {t.user_id} - info is {t.info}',
                      0
                      )
            return

        p = Payment()
        p.user = user
        p.timestamp = t.tzdt
        p.id = t.trn_id
        p.payment_state = Payment.STATE_SUCCEEDED
        p.payment_type = Payment.TYPE_BANKPAYMENT
        p.amount = t.amount

        if not self.dry_run:
            p.save()

        if not user.subscribed:
            messages.append(f"(subscribing user)")
            user.subscribed = True
            if not self.dry_run:
                user.save()

        self.pr_t(t,
                  f"payment created for user_id: {t.user_id} ({user})"
                  f' {" ".join(messages)}'
                  )

    def pr(self, message, level=1):
        if self.stdout and level <= self.verbosity:
            print(message)
        self.report.append({"message": message,
                            "level": level})

    def pr_t(self, t, message, level=1):
        self.pr(f'{t.value_date} {t.amount:6.2f} {t.currency}  {message}', level)


class NordigenAccountTransaction():

    def __init__(self, **kwargs):
        self.amount = kwargs.pop("amount", None)
        self.booking_date = kwargs.pop("booking_date", None)
        self.currency = kwargs.pop("currency", None)
        self.info = kwargs.pop("info", None)
        self.value_date = kwargs.pop("value_date", None)
        self.gocardless = kwargs.pop("gocardless", False)
        self.member = kwargs.pop("member", False)

        self.tzdt = kwargs.pop("tzdt", self.parse_date_to_tzdatetime())
        self.user_id = kwargs.pop("user_id", self.parse_info_for_user())

        self.trn_id = kwargs.pop("trn_id", self.create_trn_id())

    @staticmethod
    def from_json(data):
        if isinstance(data, str):
            data = json.loads(data)

        return NordigenAccountTransaction(
            amount=float(data['transactionAmount']['amount']),
            booking_date=data['bookingDate'],
            currency=data['transactionAmount']['currency'],
            info=data['remittanceInformationUnstructured'],
            value_date=data['valueDate']
        )

    def parse_info_for_user(self):
        """
        takes raw string of <PAYEE NAME>  <BANK REF> <PAYMENT TYPE> and
        populates user_id. if this is some other type of deposit, such as
        pledge, or the user_id can't be parsed handle it
        """
        if(re.match("GC C1\s+", self.info, re.I)):
            self.gocardless = True
            return None
        match = re.search("\sHSO?(\d{4,6})", self.info, re.I)
        if(match):
            user_id = int(match.group(1))
            self.member = True
            return user_id
        else:
            # probably a pledge or donation
            return None

    def parse_date_to_tzdatetime(self):
        """create a TZ qualified datetime from value_date"""
        return datetime.strptime(
            self.value_date, "%Y-%m-%d").replace(tzinfo=timezone.utc)

    def create_trn_id(self):
        """create a mostly unique trn_id from other fields"""
        # info_hash = self.generate_info_hash(info)
        info_hash = self.info.replace(" ", "_")
        return f"{self.value_date}-{self.amount}-{info_hash}"

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

    def is_deposit(self):
        return self.amount > 0

    def __str__(self):
        message = f"""{self.value_date} - {self.amount} - {self.currency}
            {self.info}
            {self.user_id}xxx"""
        return message

