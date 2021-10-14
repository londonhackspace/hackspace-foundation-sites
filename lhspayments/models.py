from django.db import models

from lhsauth.models import User

class Payment(models.Model):
    TYPE_BANKPAYMENT = 1
    TYPE_GOCARDLESS = 2

    STATE_PENDING = 1
    STATE_SUCCEEDED = 2
    STATE_FAILED = 3
    
    PAYMENT_TYPES = [
        (TYPE_BANKPAYMENT, "Bank Payment"),
        (TYPE_GOCARDLESS, "GoCardless")
    ]

    PAYMENT_STATES = [
        (STATE_PENDING, "Pending"),
        (STATE_SUCCEEDED, "Succeeded"),
        (STATE_FAILED, "Failed")
    ]

    id = models.CharField(primary_key=True, max_length=255)
    timestamp = models.DateTimeField()
    user = models.ForeignKey(User, on_delete=models.PROTECT)
    amount = models.DecimalField(max_digits=6, decimal_places=2)
    payment_type = models.IntegerField(choices=PAYMENT_TYPES)
    payment_state = models.IntegerField(choices=PAYMENT_STATES)

    @staticmethod
    def gocardless_status_to_status(gcstatus):
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
        
        return status_map[gcstatus]

    @staticmethod
    def create_from_gocardless_payment(payment, user):
        p = Payment()
        p.user = user
        p.timestamp = payment.charge_date
        p.id = payment.id
        p.payment_state = Payment.gocardless_status_to_status(payment.status)
        p.payment_type = Payment.TYPE_GOCARDLESS
        p.amount = payment.amount/100

        return p

    def update_from_gocardless_payment(self, payment):
        new_status = Payment.gocardless_status_to_status(payment.status)
        new_amount = payment.amount/100

        updated = False

        # I doubt this will change but eh...
        if self.amount != new_amount:
            self.amount = new_amount
            updated = True

        if self.payment_state != new_status:
            self.payment_state = new_status
            updated = True

        if updated:
            self.save()
