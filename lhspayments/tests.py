from django.test import TestCase
import gocardless_pro as gocardless

from .models import Payment
from lhsauth.models import User

# Create your tests here.

class GoCardlessMappingsTestCase(TestCase):

    def setUp(self):
        self.u = User()
        self.u.email = "test@london.hackspace.org.uk"
        self.u.full_name = "Test User"
        self.u.subscribed = False
        self.u.password = 'flourish_sha1$salt$mypassword'
        self.u.save()

    def test_that_gocardless_payment_maps_to_payment_paid_out(self):

        # taken from a real sandbox API call
        test_payment_attrs = {
            'id': 'PM000SP2D0B3XY',
            'created_at': '2019-11-18T05:40:18.789Z',
            'charge_date': '2019-11-21',
            'amount': 2000,
            'description': 'London Hackspace',
            'currency': 'GBP',
            'status': 'paid_out',
            'amount_refunded': 0,
            'reference': None,
            'metadata': {},
            'fx': {
                'fx_currency': None,
                'fx_amount': None,
                'exchange_rate': None,
                'estimated_exchange_rate': None
            }, 
            'links': {
                'mandate': 'MD00075W09M04P',
                'creditor': 'CR00005YDRFFD5',
                'payout': 'PO0002BZJVX7C3',
                'subscription': 'SB0001SGCBDKCX'
            }
        }
        test_payment = gocardless.resources.Payment(test_payment_attrs, 200)

        payment = Payment.create_from_gocardless_payment(test_payment, self.u)

        self.assertEqual(payment.id, 'PM000SP2D0B3XY')
        self.assertEqual(payment.timestamp, '2019-11-18T05:40:18.789Z')
        self.assertEqual(payment.user, self.u)
        self.assertEqual(payment.amount, 20)
        self.assertEqual(payment.payment_type, Payment.TYPE_GOCARDLESS)
        self.assertEqual(payment.payment_state, Payment.STATE_SUCCEEDED)

    def test_that_gocardless_payment_maps_to_payment_pending(self):

        # taken from a real sandbox API call, modified a bit
        test_payment_attrs = {
            'id': 'PM000SP2D0B3XY',
            'created_at': '2019-11-18T05:40:18.789Z',
            'charge_date': '2019-11-21',
            'amount': 2142,
            'description': 'London Hackspace',
            'currency': 'GBP',
            'status': 'pending_submission',
            'amount_refunded': 0,
            'reference': None,
            'metadata': {},
            'fx': {
                'fx_currency': None,
                'fx_amount': None,
                'exchange_rate': None,
                'estimated_exchange_rate': None
            }, 
            'links': {
                'mandate': 'MD00075W09M04P',
                'subscription': 'SB0001SGCBDKCX'
            }
        }
        test_payment = gocardless.resources.Payment(test_payment_attrs, 200)

        payment = Payment.create_from_gocardless_payment(test_payment, self.u)

        self.assertEqual(payment.id, 'PM000SP2D0B3XY')
        self.assertEqual(payment.timestamp, '2019-11-18T05:40:18.789Z')
        self.assertEqual(payment.user, self.u)
        self.assertEqual(payment.amount, 21.42)
        self.assertEqual(payment.payment_type, Payment.TYPE_GOCARDLESS)
        self.assertEqual(payment.payment_state, Payment.STATE_PENDING)


    def test_that_gocardless_payment_can_be_updated(self):

        # taken from a real sandbox API call, modified a bit
        test_payment_attrs = {
            'id': 'PM000SP2D0B3XY',
            'created_at': '2019-11-18T05:40:18.789Z',
            'charge_date': '2019-11-21',
            'amount': 2142,
            'description': 'London Hackspace',
            'currency': 'GBP',
            'status': 'pending_submission',
            'amount_refunded': 0,
            'reference': None,
            'metadata': {},
            'fx': {
                'fx_currency': None,
                'fx_amount': None,
                'exchange_rate': None,
                'estimated_exchange_rate': None
            }, 
            'links': {
                'mandate': 'MD00075W09M04P',
                'subscription': 'SB0001SGCBDKCX'
            }
        }
        test_payment = gocardless.resources.Payment(test_payment_attrs, 200)

        payment = Payment.create_from_gocardless_payment(test_payment, self.u)
        payment.save()

        # just to check
        self.assertEqual(payment.payment_state, Payment.STATE_PENDING)

        # now flip the payment to a success status
        test_payment_attrs['status'] = 'paid_out'
        test_payment = gocardless.resources.Payment(test_payment_attrs, 200)

        payment.update_from_gocardless_payment(test_payment)
        self.assertEqual(payment.payment_state, Payment.STATE_SUCCEEDED)