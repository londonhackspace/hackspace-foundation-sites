from django.core.mail import send_mail
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

        # print(options)

        if json_data:
            f = open(json_data, "r")
            transactions = json.loads(f.read())["booked"]
            f.close()
            nord_processer.transactions = transactions
            nord_processer.process_transactions()
        else:
            nord_processer.process_new_transactions()

        for row in nord_processer.report:
            if row["level"] > options['verbosity']:
                continue

            # self.stdout.write(str(row["level"]))

            if row["level"] == 0:
                self.stdout.write(self.style.ERROR(row["message"]))
            elif row["level"] == 1:
                self.stdout.write(self.style.SUCCESS(row["message"]))
            elif row["level"] == 2:
                # self.stdout.write(self.style.WARNING(row["message"]))
                self.stdout.write(row["message"])
            elif row["level"] == 3:
                self.stdout.write(row["message"])


        # send_mail(
        #     'Subject here',
        #     'Here is the message.',
        #     'from@example.com',
        #     ['to@example.com'],
        #     fail_silently=False,
        # )
