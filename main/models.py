from __future__ import unicode_literals

from django.db import models
from django.conf import settings
from django.core.mail import send_mail, EmailMessage
from django.utils.functional import cached_property

from .fields import DateTimeDateField
from lhsauth.models import User

from datetime import date, datetime, timedelta
from time import time as system_time


class Alias(models.Model):
    id = models.CharField(primary_key=True, max_length=255)
    type = models.IntegerField()

    class Meta:
        db_table = 'aliases'

    def __str__(self):
        return self.id


class Card(models.Model):
    uid = models.CharField(primary_key=True, max_length=255)
    user = models.ForeignKey(User, on_delete=models.CASCADE)
    added_date = models.DateTimeField()  # Actually a DateTime
    active = models.BooleanField()

    class Meta:
        db_table = 'cards'

    def __str__(self):
        return self.uid


class Interest(models.Model):
    interest_id = models.AutoField(primary_key=True)
    category = models.ForeignKey('InterestCategory', db_column='category', on_delete=models.CASCADE)
    suggested = models.BooleanField()
    name = models.CharField(max_length=255)
    url = models.CharField(max_length=255, blank=True, null=True)

    class Meta:
        db_table = 'interests'

    def __str__(self):
        return self.name


class InterestCategory(models.Model):
    id = models.CharField(primary_key=True, max_length=255)

    class Meta:
        db_table = 'interests_categories'

    def __str__(self):
        return self.id


class Learning(models.Model):
    learning_id = models.AutoField(primary_key=True)
    name = models.CharField(max_length=255)
    description = models.CharField(max_length=255)
    url = models.CharField(max_length=255, blank=True, null=True)

    class Meta:
        db_table = 'learnings'

    def __str__(self):
        return self.name


class Location(models.Model):
    name = models.CharField(max_length=255)

    class Meta:
        db_table = 'locations'

    def __str__(self):
        return self.name


ProjectState = None
class ProjectStateMeta(models.base.ModelBase):
    do_not_call_in_templates = True

    # Make ProjectState act like an enum, as we'll probably replace it with one
    def __getattr__(self, name):
        try:
            return super(ProjectStateMeta, self).__getattr__(name)
        except AttributeError:
            if name == 'db_states':
                # This means we can use a cached ProjectState.PassedDeadline,
                # instead of ProjectState.objects.get(name='Passed Deadline')
                self.db_states = {s.name.replace(' ', ''): s for s in ProjectState.objects.all()}
                return self.db_states

            try:
                return self.db_states[name]
            except KeyError:
                raise AttributeError(name)


class ProjectState(models.Model, metaclass=ProjectStateMeta):
    name = models.CharField(max_length=255)

    class Meta:
        db_table = 'project_states'

    def __str__(self):
        return self.name


class Project(models.Model):
    user = models.ForeignKey(User, on_delete=models.CASCADE)
    name = models.CharField(max_length=255)
    description = models.CharField(max_length=500)
    state = models.ForeignKey(ProjectState, on_delete=models.CASCADE)
    location = models.ForeignKey(Location, on_delete=models.CASCADE)
    location_name = models.CharField(db_column='location', max_length=255, blank=True, null=True)
    updated_date = DateTimeDateField()
    from_date = DateTimeDateField()
    to_date = DateTimeDateField()
    contact = models.CharField(max_length=255, blank=True, null=True)

    class Meta:
        db_table = 'projects'

    def __str__(self):
        return self.name

    @property
    def approve_after(self):
        if self.location.name == 'Yard':
            return timedelta(days=7)
        return timedelta(days=2)

    @property
    def created_date(self):
        if not self.projectlog_set:
            return None

        first_log = self.projectlog_set.order_by('timestamp')[0]
        return first_log.log_date

    @property
    def short_term(self):
        # FIXME: add a created_at column, as this is wrong
        if self.created_date is None:
            return False

        short_notice = self.from_date <= self.created_date + timedelta(days=1)
        short_duration = self.to_date <= self.from_date + timedelta(days=3)
        return short_notice and short_duration

    @property
    def extension(self):
        if self.short_term:
            return timedelta(days=2)
        return timedelta(days=14)

    @property
    def extended(self):
        if self.state == ProjectState.Extended:
            return True

        # FIXME: add an extended column, as this is wrong
        extended_log = self.projectlog_set.filter(details='Status changed to Extended')
        return bool(extended_log)

    @property
    def deadline_date(self):
        if self.extended:
            return self.to_date + self.extension
        return self.to_date

    @property
    def has_activity(self):
        # FIXME: this should be a column too
        if self.short_term:
            return self.projectlog_set.count() > 4
        return self.projectlog_set.count() > 2

    def add_log(self, msg, user=None):
        self.projectlog_set.create(details=msg, user=user, timestamp=system_time())

    def email_owner(self, body_html):
        msg = EmailMessage(
            subject='London Hackspace Storage Request #%s: %s' % (self.id, self.name),
            from_email=settings.NOREPLY_EMAIL,
            to=[self.user.email],
            headers={
                'Reply-To': settings.CONTACT_EMAIL,
            },
            body=body_html,
        )
        msg.content_subtype = "html"
        msg.send(fail_silently=False)

    def email_mailing_list(self, body_html):
        # TODO: pretty sure we only need self.id here
        message_id = '<storage-%s-%s-%s@%s>' % (
            self.id,
            self.user.id,
            self.from_date.strftime('%Y%m%d%H%M%S'),
            settings.DOMAIN_NAME,
        )
        msg = EmailMessage(
            subject='Storage Request #%s: %s by %s' % (self.id, self.name, self.user.full_name),
            from_email=settings.NOREPLY_EMAIL,
            to=[settings.PROJECT_MAILING_LIST + '@googlegroups.com'],
            headers={
                'Reply-To': settings.NOREPLY_EMAIL,
                'References': message_id,
            },
            body=body_html,
        )
        msg.content_subtype = "html"
        msg.send(fail_silently=False)


class ProjectLog(models.Model):
    timestamp = models.IntegerField()  # Should be a DateTime
    project = models.ForeignKey(Project, on_delete=models.CASCADE)
    user = models.ForeignKey(User, blank=True, null=True, on_delete=models.SET_NULL)
    details = models.CharField(max_length=255)

    class Meta:
        db_table = 'projects_logs'

    def __str__(self):
        return self.log_dt.strftime('%Y-%m-%d %H:%M:%S')

    @property
    def log_dt(self):
        return datetime.fromtimestamp(self.timestamp)

    @property
    def log_date(self):
        dt = self.log_dt
        return date(dt.year, dt.month, dt.day)


class Subscription(models.Model):
    user = models.ForeignKey(User, on_delete=models.CASCADE)
    transaction = models.ForeignKey('Transaction', on_delete=models.CASCADE)
    start_date = DateTimeDateField()
    end_date = DateTimeDateField()

    class Meta:
        db_table = 'subscriptions'


class Transaction(models.Model):
    fit_id = models.TextField(unique=True)
    timestamp = DateTimeDateField()
    user = models.ForeignKey(User, on_delete=models.PROTECT)
    amount = models.DecimalField(max_digits=6, decimal_places=2)

    class Meta:
        db_table = 'transactions'

    def __str__(self):
        return self.fit_id


class UserAlias(models.Model):
    user = models.ForeignKey(User, on_delete=models.CASCADE)
    alias = models.ForeignKey(Alias, on_delete=models.CASCADE)
    username = models.CharField(max_length=255)

    class Meta:
        db_table = 'users_aliases'
        unique_together = (('user', 'alias'),)

    def __str__(self):
        return self.alias.id


class UserInterest(models.Model):
    user = models.ForeignKey(User, on_delete=models.CASCADE)
    interest = models.ForeignKey(Interest, on_delete=models.CASCADE)

    class Meta:
        db_table = 'users_interests'
        unique_together = (('user', 'interest'),)

    def __str__(self):
        return self.interest


class UserLearning(models.Model):
    user = models.ForeignKey(User, on_delete=models.CASCADE)
    learning = models.ForeignKey(Learning, on_delete=models.CASCADE)

    class Meta:
        db_table = 'users_learnings'
        unique_together = (('user', 'learning'),)

    def __str__(self):
        return self.learning


class UserProfile(models.Model):
    user = models.OneToOneField(User, primary_key=True, on_delete=models.CASCADE)
    allow_email = models.BooleanField()
    allow_doorbot = models.BooleanField()
    photo = models.CharField(max_length=255, blank=True, null=True)
    website = models.CharField(max_length=255, blank=True, null=True)
    description = models.CharField(max_length=500, blank=True, null=True)

    class Meta:
        db_table = 'users_profiles'
