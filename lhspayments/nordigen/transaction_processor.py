
import base64
import enum
import hashlib
import json
import pprint
import re


from datetime import datetime, timezone, time

from django.utils.dateparse import parse_date
from django.conf import settings

from lhsauth.models import User
from lhspayments.models import Payment
from lhspayments.email import new_user_email
from lhspayments.membershiptools import on_new_member

from nordigen import Client
from .account_transaction import BarcBusinessAccountTransaction as AccountTransaction


class Verbosity(enum.Enum):
    QUIET = 0
    DEFAULT = 1
    MORE = 2
    ALL = 3


class NordProcessor():

    client = Client(token=settings.NORDIGEN_TOKEN)
    account = settings.NORDIGEN_ACCOUNT

    # store processing reports for passing back to django
    report = []
    statistics = {
        "deposits": 0,
        "dispersements": 0,
        "noparse": 0,
        "unknown_user_id": 0,
        "filtered": 0,
        "duplicates": 0,
        "duplicates_ofx": 0,
        "payment_in_future": 0,
        "payment_out_grace": 0,
        "payment_subbed": 0,
        "payment": 0
    }

    def __init__(self, **kwargs):
        # this is passed through from django command invocation
        self.verbosity = kwargs.pop("verbosity", Verbosity.DEFAULT.value)
        # set this to true to force inline output during processing
        self.stdout = kwargs.pop("stdout", False)
        self.dry_run = kwargs.pop("dry_run", False)
        # override the "now" date for date comparisons for subscriptions
        self.subs_date = kwargs.pop(
            "subs_date",
            # otherwise use the end of today in UTC
            datetime.combine(
                datetime.now(timezone.utc).astimezone(),
                time.max,
                timezone.utc
            )
        )
        d = self.subs_date
        if d.tzinfo is None or d.tzinfo.utcoffset(d) is None:
            raise ValueError(f"subs date can't be naive {d}")

    def process_new_transactions(self):
        self.transactions = self.get_new_transactions()
        self.pr_pre_summary()
        self.process_transactions()
        self.pr_post_summary()

    def get_new_transactions(self):
        "get transactions from remote API"
        data = self.client.account.transactions(self.account)
        # do we care about pending transactions?
        # self.transactions = data["transactions"]["booked"]
        return data["transactions"]["booked"]

    def process_transactions(self):
        """
        requires that self.transactions has been populated by some method
        """
        for transaction in self.transactions:
            t = AccountTransaction.from_json(transaction)

            # filter out unwanted transaction types
            if t.is_dispersements():
                self.statistics['dispersements'] += 1
                self.pr_t(t,
                          f"disbursement to \"{t.info:.60}\"",
                          Verbosity.MORE
                          )
            elif t.gocardless:
                self.statistics['deposits'] += 1
                self.pr_t(t,
                          f"gocardless deposit \"{t.info}\"",
                          Verbosity.MORE
                          )
            elif not t.user_id:
                self.statistics['noparse'] += 1
                self.pr_t(t,
                          f"unable to parse user from info \"{t.info}\" ",
                          Verbosity.DEFAULT
                          )
            else:
                self.process_transaction(t)

    def process_transaction(self, t):

        # log of non-fatal processing messages
        messages = []

        try:
            user = User.objects.get(pk=t.user_id)
        except User.DoesNotExist:
            self.statistics['unknown_user_id'] += 1
            self.pr_t(t,
                      f"user:{t.user_id} not found in db info:\"{t.info}\"",
                      Verbosity.MORE,
                      t.user_id
                      )
            return

        # handle cases where we don't want to process the payment

        if user.terminated:
            self.statistics['filtered'] += 1
            self.pr_t(t,
                      "terminated user "
                      f' {t.user_id} - info is {t.info}',
                      Verbosity.QUIET,
                      t.user_id
                      )
            return

        if t.currency != 'GBP':
            self.statistics['filtered'] += 1
            self.pr_t(t,
                      f"non uk currency ({t.currency}) "
                      f"for info: \"{t.info}\"",
                      Verbosity.DEFAULT,
                      t.user_id
                      )
            return

        if t.amount < settings.SUBS_MIN_PAYMENT:
            self.statistics['filtered'] += 1
            self.pr_t(t,
                      f"amount was less than "
                      f"£{settings.SUBS_MIN_PAYMENT:.2f} "
                      f"info:\"{t.info}\"",
                      Verbosity.MORE,
                      t.user_id
                      )
            return

        if not user.address:
            self.statistics['filtered'] += 1
            self.pr_t(t,
                      "user address field is empty "
                      f' {t.user_id} - info is {t.info}',
                      Verbosity.DEFAULT,
                      t.user_id
                      )
            return

        # find duplicate transactions (inserted by this script)"
        try:
            existing_payment = Payment.objects.get(pk=t.trn_id)
            self.statistics['duplicates'] += 1
            self.pr_t(t,
                      f"found existing transaction: \"{t.trn_id}\"",
                      Verbosity.MORE,
                      t.user_id
                      )
            return
        except Payment.DoesNotExist:
            existing_payment = None

        # find duplicate payments (inserted by reconcile.rb - OFX transactions)"
        ofx_payments = Payment.objects.filter(
            user=t.user_id,
            timestamp__date=t.tzdt,
            amount=t.amount,
            payment_type=Payment.TYPE_BANKPAYMENT
        )

        if(ofx_payments.count() > 0):
            self.statistics['duplicates_ofx'] += 1
            self.pr_t(t,
                      "found existing transaction (with non-matching trn_id)"
                      f' {t.user_id} - info:{t.info}',
                      Verbosity.DEFAULT,
                      t.user_id
                      )
            return

        # if we have passed all the filters, this is probably a good one

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
            # filter payments in future. This is not likely to be a problem
            # for API data, but might come up in testing
            if t.tzdt > self.subs_date:
                self.statistics['payment_in_future'] += 1
                messages.append(
                    f"(payment in future - not subscribing)"
                )
            elif t.tzdt < self.subs_date - settings.SUBS_GRACE_PERIOD:
                self.statistics['payment_out_grace'] += 1
                messages.append(
                    f"(payment out of grace period - not subscribing)"
                )
            else:
                # user status is changed to subscribed here
                self.statistics['payment_subbed'] += 1
                messages.append(
                    f"(subscribing user)"
                )

                if not self.dry_run:
                    on_new_member(user)

        else:
            self.statistics['payment'] += 1

        # log transaction as a payment, indicate whether subs status changed
        self.pr_t(t,
                  f"payment created for user_id: {t.user_id} ({user})"
                  f' {" ".join(messages)}',
                  Verbosity.DEFAULT,
                  t.user_id
                  )

    # utility method for filtering based on verbosity
    def pr(self, message, level=1):
        if self.stdout and level <= self.verbosity:
            print(message)
        self.report.append({"message": message,
                            "level": level})

    # format a transaction aware log message
    def pr_t(self, t, message, level=Verbosity.DEFAULT, user_id=None):
        level = level.value   # django uses verbosity 0-3
        tmpmessage = f'{t.value_date} {t.amount:8.2f} {t.currency} '
        if user_id:
            tmpmessage += f"{('HS' + str(user_id).zfill(5)).ljust(8)} "
        else:
            tmpmessage += f"{' '.rjust(8)} "
        tmpmessage += f'{message}'
        self.pr(
            tmpmessage,
            level
        )

    def pr_pre_summary(self):
        print(
            f'processing {len(self.transactions)} transactions'
        )
        print(
            f"subs date is {self.subs_date}"
        )

    def pr_post_summary(self):

        stats = self.statistics
        print("")
        print(
            f'initial list of {len(self.transactions)} transactions'
        )
        # print(
        #     self.statistics
        # )
        # pprint.PrettyPrinter(indent=4).pprint(self.statistics)
        if 1 <= self.verbosity:
            for key in sorted(stats.keys()):
                print(
                    f'{key:20} {stats[key]}'
                )

        print(
            f'total from statistics {sum(self.statistics.values())}'
        )
