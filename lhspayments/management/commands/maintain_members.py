from django.core.management.base import BaseCommand
from datetime import datetime, timedelta, timezone
import subprocess

from lhsauth.models import User
from lhspayments.models import Payment
from lhspayments.membershiptools import unsubscribe_member


class Command(BaseCommand):
    help = "Checks for inactive members"

    def handle(self, *args, **options):
        # Find ex-members

        # calculate the latest payment that's no longer valid
        d = datetime.now(timezone.utc) - timedelta(weeks=6)

        for u in User.objects.filter(subscribed=True):
            # essentially, has the user made a non-failed payment in the last six weeks?
            payment = Payment.objects.filter(user=u).filter(timestamp__gte=d).exclude(payment_state=Payment.STATE_FAILED).first()
            if payment is None:
                # As a special case, if there are no payments (even failed ones) then they're
                # a permanent member like the cleaner or something
                if Payment.objects.filter(user=u).count() == 0:
                    print("%s has no payments, assuming a special user" % (u.full_name,))
                    continue

                print("%s is no longer a member. Unsubscribing." % (u.full_name,))
                unsubscribe_member(u)

