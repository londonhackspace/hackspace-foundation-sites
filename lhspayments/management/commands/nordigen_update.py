import json

from django.core.management.base import BaseCommand
from lhspayments.nordigen_utils import NordProcessor


class Command(BaseCommand):
    help = "Connect to Nordigen OB Api and updates transactions (or test data)"

    def add_arguments(self, parser):
        # override the API call, force using local list of transactions
        parser.add_argument('-t', '--transactions', type=str,
                            help='provide json file of transactions for import'
                            )
        parser.add_argument('--dry-run', action='store_true',
                            help='don\'t make any changes, just show what would'
                            )

    def handle(self, *args, **options):
        # import transctions using nordigen

        json_data = options['transactions']

        nord_processer = NordProcessor(
            dry_run=options['dry_run'],
            verbosity=options['verbosity']
        )

        print(options)

        if json_data:
            f = open(json_data, "r")
            transactions = json.loads(f.read())["booked"]
            f.close()
            nord_processer.transactions = transactions
            nord_processer.process_transactions()
        else:
            nord_processer.process_new_transactions()
