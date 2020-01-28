from django.test import TestCase
import gocardless_pro as gocardless

from .models import Subscription, Customer
from lhsauth.models import User

class GoCardlessMappingsTestCase(TestCase):

    def setUp(self):
        self.u = User()
        self.u.email = "test@london.hackspace.org.uk"
        self.u.full_name = "Test User"
        self.u.subscribed = False
        self.u.gocardless_user = True
        self.u.password = 'flourish_sha1$salt$mypassword'
        self.u.save()

        self.customer = Customer()
        self.customer.user = self.u
        self.customer.mandate = 'MD00075W09M04P'
        self.customer.save()

    def test_that_subscription_mapping_works(self):

        # taken from an actual sandbox API response
        test_data = {
            'id': 'SB0001SGCBDKCX',
            'created_at': '2019-10-21T16:40:46.802Z',
            'amount': 2000,
            'currency': 'GBP',
            'status': 'active',
            'name': 'London Hackspace',
            'start_date': '2019-11-21',
            'end_date': None,
            'interval': 1,
            'interval_unit': 'monthly',
            'day_of_month': 21,
            'month': None,
            'metadata': {},
            'payment_reference': None,
            'upcoming_payments': [
                {
                    'charge_date': '2019-12-23',
                    'amount': 2000
                },
                {
                    'charge_date': '2020-01-21',
                    'amount': 2000
                },
                {
                    'charge_date': '2020-02-21',
                    'amount': 2000
                },
                {
                    'charge_date': '2020-03-23',
                    'amount': 2000
                },
                {
                    'charge_date': '2020-04-21',
                    'amount': 2000
                },
                {
                    'charge_date': '2020-05-21',
                    'amount': 2000
                },
                {
                    'charge_date': '2020-06-22',
                    'amount': 2000
                },
                {
                    'charge_date': '2020-07-21',
                    'amount': 2000
                },
                {
                    'charge_date': '2020-08-21',
                    'amount': 2000
                },
                {
                    'charge_date': '2020-09-21',
                    'amount': 2000
                }
            ],
            'app_fee': None,
            'links': {
                'mandate': 'MD00075W09M04P'
            }
        }
        test_subscription = gocardless.resources.Subscription(test_data, 200)

        sub = Subscription.create_subscription_from_gocardless_subscription(test_subscription)

        self.assertEqual(sub.customer, self.customer)
        self.assertEqual(sub.subscription, 'SB0001SGCBDKCX')