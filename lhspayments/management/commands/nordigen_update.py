
from datetime import datetime
import json

from django.core.mail import send_mail
from django.core.management.base import BaseCommand
from lhspayments.nordigen import NordProcessor


class Command(BaseCommand):
    help = "Connect to Nordigen OB Api and updates transactions (or test data)"

    def add_arguments(self, parser):
        "override the API call, provide transactions as json file"
        parser.add_argument('-t', '--transactions', type=str,
                            help='provide json file of transactions for import'
                            )
        parser.add_argument('--dry-run', action='store_true',
                            help='don\'t make any changes, just show what would'
                            )
        parser.add_argument('--force-email', action='store_true',
                            help='use with caution. with dry run, try'
                            'to send email. used with docker mail server'
                            )
        parser.add_argument('--subs-date', type=str,
                            help='for testing, alternative "now" date for '
                            ' comparison against expiry etc'
                            )

    def handle(self, *args, **options):

        obj_args = {
            "dry_run": options['dry_run'],
            "force_email": options['force_email'],
            "verbosity": options['verbosity']
        }

        if options['subs_date']:
            subs_date = datetime.fromisoformat(
                options['subs_date'].replace('Z', '+00:00'))
            obj_args['subs_date'] = subs_date

        nord_processer = NordProcessor(**obj_args)

        # print(options)

        json_data = options['transactions']

        if json_data:
            f = open(json_data, "r")
            transactions = json.loads(f.read())["booked"]
            f.close()
            nord_processer.transactions = transactions
            nord_processer.pr_pre_summary()
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

        nord_processer.pr_post_summary()

        # send_mail(
        #     'Subject here',
        #     'Here is the message.',
        #     'from@example.com',
        #     ['to@example.com'],
        #     fail_silently=False,
        # )
