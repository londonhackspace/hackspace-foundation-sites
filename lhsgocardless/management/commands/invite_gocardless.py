from django.core.management.base import BaseCommand

from lhsauth.models import User
from lhspayments.models import Payment
from lhspayments.email import invite_to_gocardless

class Command(BaseCommand):
    help = "Invites user to gocardless"

    def add_arguments(self, parser):
        parser.add_argument('id', type=int)

    def handle(self, *args, **options):
        uid = int(options['id'])

        user = User.objects.get(id=uid)
        user.gocardless_user = True
        user.save()
        invite_to_gocardless(user)