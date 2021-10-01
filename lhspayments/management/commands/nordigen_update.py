from django.core.management.base import BaseCommand
from lhspayments.nordigen_utils import NordProcessor


class Command(BaseCommand):
    help = "Connect to Nordigen OB Api and updates transactions"

    def handle(self, *args, **options):
        # import transctions using nordigen

        nord_processer = NordProcessor()
        nord_processer.process_transactions()
