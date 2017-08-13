from django.db import models
from datetime import datetime


class DateTimeDateField(models.DateField):
    """
    Some columns in the DB should be Dates, but are DateTimes instead.
    We force calculations to be done with Dates to avoid logic issues.
    """
    def get_internal_type(self):
        return 'DateTimeField'

    def to_python(self, value):
        # DateField can accept DateTimes, so nothing to do
        return super(DateTimeDateField, self).to_python(value)

    def from_db_value(self, value, expression, connection, context):
        # Override from_db_value even if though the superclass doesn't
        return self.to_python(value)

    def get_prep_value(self, value):
        value = super(DateTimeDateField, self).get_prep_value(value)
        if value is None:
            return value
        return datetime(value.year, value.month, value.day)



