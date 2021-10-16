import json
import mock

from django.test import TestCase
import gocardless_pro as gocardless

from lhspayments.models import Payment
from lhsauth.models import User
from lhspayments.nordigen import NordProcessor

# Create your tests here.

class NordigenApiTests(TestCase):

    # bunch of users to be matched against
    fixtures = ["User.yaml"]

    def setUp(self):
        self.nord_processer = NordProcessor(
            stdout=True,
            verbosity=3
        )

    @mock.patch('lhspayments.nordigen.NordProcessor.get_new_transactions')
    def test_process_transactions_with_good_data(self, mock_transactions):
        """
        pass a transaction for a known good user
        """

        user_id = 1  # known good user inserted by fixture

        mock_transactions.return_value = json.loads("""
        [
            {
                "bookingDate": "2021-09-29",
                "remittanceInformationUnstructured": "T BLACK  HS0001 STO",
                "transactionAmount": {
                    "amount": "5.00",
                    "currency": "GBP"
                },
                "valueDate": "2021-09-29"
            }
        ]
        """)

        self.nord_processer.process_new_transactions()

        user1 = User.objects.get(pk=user_id)
        payments = Payment.objects.filter(user_id=user_id)

        self.assertEqual(payments.count(), 1)
        self.assertEqual(Payment.objects.count(), 1)
        # self.assertEqual(user1.email, 'test.user1@limepepper.co.uk')

    @mock.patch('lhspayments.nordigen.NordProcessor.get_new_transactions')
    def test_process_transactions_with_duplicate(self, mock_transactions):
        """
        test with a transaction that was already inserted by OFX processing
        """

        user_id = 1  # known good user inserted by fixture

        mock_transactions.return_value = json.loads("""
        [
            {
                "bookingDate": "2021-09-29",
                "remittanceInformationUnstructured": "T BLACK  HS0001 STO",
                "transactionAmount": {
                    "amount": "5.00",
                    "currency": "GBP"
                },
                "valueDate": "2021-09-29"
            }
        ]
        """)

        user1 = User.objects.get(pk=user_id)

        # create a payment that mocks an OFX inserted record
        p = Payment()
        p.user = user1
        p.timestamp = "2021-09-29"
        p.id = "2021092900000001"
        p.payment_state = Payment.STATE_SUCCEEDED
        p.payment_type = Payment.TYPE_BANKPAYMENT
        p.amount = 5
        p.save()

        self.assertEqual(Payment.objects.count(), 1)
        self.nord_processer.process_new_transactions()
        payments = Payment.objects.filter(user_id=user_id)
        self.assertEqual(payments.count(), 1)
        # self.assertEqual(Payment.objects.count(), 2)
        # self.assertEqual(user1.email, 'test.user1@limepepper.co.uk')

    @mock.patch('lhspayments.nordigen.NordProcessor.get_new_transactions')
    def test_process_duplicate_different_date(self, mock_transactions):
        """
        test with a transaction for a user with payments from OFX before
        and after the date
        """

        user_id = 1  # known good user inserted by fixture

        mock_transactions.return_value = json.loads("""
        [
            {
                "bookingDate": "2021-09-29",
                "remittanceInformationUnstructured": "T BLACK  HS0001 STO",
                "transactionAmount": {
                    "amount": "5.00",
                    "currency": "GBP"
                },
                "valueDate": "2021-09-29"
            }
        ]
        """)

        user1 = User.objects.get(pk=user_id)

        # create a payment that mocks an OFX inserted record
        p = Payment()
        p.user = user1
        p.timestamp = "2021-09-30"
        p.id = "2021092900000001"
        p.payment_state = Payment.STATE_SUCCEEDED
        p.payment_type = Payment.TYPE_BANKPAYMENT
        p.amount = 5
        p.save()

        self.assertEqual(Payment.objects.count(), 1)

        # create a payment that mocks an OFX inserted record
        p = Payment()
        p.user = user1
        p.timestamp = "2021-10-01"
        p.id = "2021100100000001"
        p.payment_state = Payment.STATE_SUCCEEDED
        p.payment_type = Payment.TYPE_BANKPAYMENT
        p.amount = 5
        p.save()

        self.assertEqual(Payment.objects.count(), 2)
        self.nord_processer.process_new_transactions()
        payments = Payment.objects.filter(user_id=user_id)
        self.assertEqual(payments.count(), 3)
        # self.assertEqual(Payment.objects.count(), 2)
        # self.assertEqual(user1.email, 'test.user1@limepepper.co.uk')

    @mock.patch('lhspayments.nordigen.NordProcessor.get_new_transactions')
    def test_badly_formatted_reference_001(self, mock_transactions):
        """
        this user's bank reference has a letter O instead of number zero
        after the HS   e.g. HSO1234
        """

        user_id = 1000

        mock_transactions.return_value = json.loads("""
        [
            {
                "bookingDate": "2021-09-29",
                "remittanceInformationUnstructured": "MR oh for a zero HSO1000 STO",
                "transactionAmount": {
                    "amount": "5.00",
                    "currency": "GBP"
                },
                "valueDate": "2021-09-29"
            }
        ]
        """)

        user1 = User.objects.get(pk=user_id)

        #self.assertEqual(Payment.objects.count(), 2)

        self.nord_processer.process_new_transactions()
        payments = Payment.objects.filter(user_id=user_id)

        self.assertEqual(payments.count(), 1)
        # self.assertEqual(Payment.objects.count(), 2)
        # self.assertEqual(user1.email, 'test.user1000@limepepper.co.uk')

    @mock.patch('lhspayments.nordigen.NordProcessor.get_new_transactions')
    def test_duplicates_002(self, mock_transactions):
        """
        test what happens when a bunch of similar transactions come through
        i.e. same date, amount and remittance info
        """

        user_id = 1

        mock_transactions.return_value = json.loads("""
        [
            {
                "bookingDate": "2021-09-29",
                "remittanceInformationUnstructured": "MR oh for a zero HS0001 STO",
                "transactionAmount": {
                    "amount": "5.00",
                    "currency": "GBP"
                },
                "valueDate": "2021-09-29"
            },
            {
                "bookingDate": "2021-09-29",
                "remittanceInformationUnstructured": "MR oh for a zero HS0001 STO",
                "transactionAmount": {
                    "amount": "5.00",
                    "currency": "GBP"
                },
                "valueDate": "2021-09-29"
            },
            {
                "bookingDate": "2021-09-29",
                "remittanceInformationUnstructured": "MR oh for a zero HS0001 STO",
                "transactionAmount": {
                    "amount": "5.00",
                    "currency": "GBP"
                },
                "valueDate": "2021-09-29"
            }
        ]
        """)

        user1 = User.objects.get(pk=user_id)
        #self.assertEqual(Payment.objects.count(), 2)
        self.nord_processer.process_new_transactions()
        payments = Payment.objects.filter(user_id=user_id)

        self.assertEqual(payments.count(), 1)
        # self.assertEqual(Payment.objects.count(), 2)
        # self.assertEqual(user1.email, 'test.user1@limepepper.co.uk')


