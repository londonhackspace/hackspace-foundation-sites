
import base64
import hashlib
import json
import re

from datetime import datetime, timezone

from django.utils.dateparse import parse_date
from django.conf import settings


class NordigenAccountTransaction():
    """
    generic representation of the Nordigen Account Transaction as defined in
    the API docs:
    https://nordigen.com/en/docs/account-information/output/transactions/
    """

    def __init__(self, kwargs):
        self.amount = kwargs.pop("amount", None)
        self.booking_date = kwargs.pop("booking_date", None)
        self.currency = kwargs.pop("currency", None)
        self.info = kwargs.pop("info", None)
        self.value_date = kwargs.pop("value_date", None)

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

    def parse_date_to_tzdatetime(self):
        "create a TZ qualified datetime from value_date"
        return datetime.strptime(
            self.value_date, "%Y-%m-%d").replace(tzinfo=timezone.utc)

    def create_trn_id(self):
        "create a mostly unique trn_id from other fields"
        info_hash = self.info.replace(" ", "_")
        return f"{self.value_date}-{self.amount}-{info_hash}"

    def is_dispersements(self):
        return self.amount < 0

    def is_deposit(self):
        return self.amount >= 0

    def __str__(self):
        message = f"{self.value_date} {self.amount:6.2f} "
        message += f"{self.currency} "
        message += f"info:\"{self.info}\""
        return message


class BarcBusinessAccountTransaction(NordigenAccountTransaction):
    """
        represents a transaction API object returned by a Barc Business bank
        account, i.e. parses fields provided by that OB API
    """

    def __init__(self, **kwargs):
        super().__init__(kwargs)

        self.gocardless = kwargs.pop("gocardless", False)
        self.member = kwargs.pop("member", False)
        self.tzdt = kwargs.pop("tzdt", self.parse_date_to_tzdatetime())
        self.user_id = kwargs.pop("user_id", self.parse_info_for_user())
        self.trn_id = kwargs.pop("trn_id", self.create_trn_id())

    @staticmethod
    def from_json(data):
        if isinstance(data, str):
            data = json.loads(data)

        return BarcBusinessAccountTransaction(
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

    def __str__(self):
        message = f"{self.value_date} {self.amount:6.2f} "
        message += f"{self.currency} "
        if self.user_id:
            message += f"{'HS' + str(self.user_id).zfill(5)} "
        else:
            message += f"{' '.rjust(7)} "
        message += f"info:\"{self.info}\""
        return message
