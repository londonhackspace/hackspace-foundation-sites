from django.core.management.base import BaseCommand

from lhsauth.models import User
from lhspayments.models import Payment
from lhspayments.email import invite_to_gocardless

class Command(BaseCommand):
    help = "Invites A number of random members to gocardless (for rollout)"

    def add_arguments(self, parser):
        parser.add_argument('count', type=int)

    def handle(self, *args, **options):
        count = int(options['count'])

        users = User.objects.filter(subscribed=True, gocardless_user = False).order_by('?')
        for user in users:
            print("Inviting {} to GoCardless".format(user.full_name))
            user.gocardless_user = True
            user.save()
            invite_to_gocardless(user)