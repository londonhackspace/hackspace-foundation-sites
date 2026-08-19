from django.db import models
import datetime as dt

from lhsauth.models import User

import logging
logger = logging.getLogger(__name__)

class Payment(models.Model):
    TYPE_BANKPAYMENT = 1
    TYPE_GOCARDLESS = 2

    STATE_PENDING = 1
    STATE_SUCCEEDED = 2
    STATE_FAILED = 3
    STATE_RETRYING = 4
    
    PAYMENT_TYPES = [
        (TYPE_BANKPAYMENT, "Bank Payment"),
        (TYPE_GOCARDLESS, "GoCardless")
    ]

    PAYMENT_STATES = [
        (STATE_PENDING, "Pending"),
        (STATE_SUCCEEDED, "Succeeded"),
        (STATE_FAILED, "Failed"),
        (STATE_RETRYING, "Retrying")
    ]

    id = models.CharField(primary_key=True, max_length=255)
    timestamp = models.DateTimeField()
    user = models.ForeignKey(User, on_delete=models.PROTECT)
    amount = models.DecimalField(max_digits=6, decimal_places=2)
    payment_type = models.IntegerField(choices=PAYMENT_TYPES)
    payment_state = models.IntegerField(choices=PAYMENT_STATES)

    @staticmethod
    def gocardless_status_to_status(action, gcstatus, previous_status = None):
        logger.info(f'Mapping GoCardless status to internal status. Action: {action}, GoCardless status: {gcstatus}, Previous status: {previous_status}')
        status_map = {
            'pending_customer_approval': Payment.STATE_PENDING,
            'pending_submission': Payment.STATE_PENDING,
            'submitted': Payment.STATE_PENDING,
            'confirmed': Payment.STATE_SUCCEEDED,
            'paid_out': Payment.STATE_SUCCEEDED,
            'cancelled': Payment.STATE_FAILED,
            'customer_approval_denied': Payment.STATE_FAILED,
            'failed': Payment.STATE_FAILED,
            'charged_back': Payment.STATE_FAILED,
        }
        status = status_map[gcstatus]
        logger.info(f'Previous status: {previous_status}, GoCardless status: {gcstatus}, mapped status: {status}')
        if action == 'failed' and status == Payment.STATE_PENDING:
            logger.info ("Payment is retrying")
            status = Payment.STATE_RETRYING
        elif (previous_status == Payment.STATE_FAILED or previous_status == Payment.STATE_RETRYING) and status == Payment.STATE_PENDING:
            # if it failed and now it's pending, it's retrying
            logger.info ("Payment is retrying")
            status = Payment.STATE_RETRYING
        return status
    
    @staticmethod
    def set_gocardless_payment_timestamp(payment, gc_payment):
        if payment.payment_state == Payment.STATE_PENDING or payment.payment_state == Payment.STATE_RETRYING:
            logger.info(f'Pending payment date {gc_payment.charge_date}')
            payment.timestamp = dt.datetime.combine(dt.date.fromisoformat(gc_payment.charge_date), dt.time.max)
        else:
            logger.info(f'Non-pending payment date {gc_payment.created_at}')
            try:
                payment.timestamp = gc_payment.created_at
            except Exception as e:
                logger.error(f'Error setting timestamp for payment {payment.id}: {e}')
                payment.timestamp = dt.datetime.now()
        logger.info(f'Date Set')

    @staticmethod
    def create_from_gocardless_payment(action, payment, user):
        p = Payment()
        p.user = user

        p.id = payment.id
        p.payment_state = Payment.gocardless_status_to_status(action, payment.status)
        p.payment_type = Payment.TYPE_GOCARDLESS
        p.amount = payment.amount/100
        Payment.set_gocardless_payment_timestamp(p, payment)

        return p

    def update_from_gocardless_payment(self, action, payment):
        logger.info(f'Updating payment {self.id} from GoCardless payment {payment.id}. Action: {action}, GoCardless status: {payment.status}, Current internal status: {self.payment_state}')
        new_status = Payment.gocardless_status_to_status(action, payment.status, self.payment_state)
        new_amount = payment.amount/100

        # I doubt this will change but eh...
        if self.amount != new_amount:
            self.amount = new_amount

        if self.payment_state != new_status:
            self.payment_state = new_status

        Payment.set_gocardless_payment_timestamp(self, payment)
        logger.info(f'Saving payment')
        self.save()
