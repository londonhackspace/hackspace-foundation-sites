from __future__ import unicode_literals

from django.db import models
from django.contrib.auth.base_user import AbstractBaseUser, BaseUserManager


class Alias(models.Model):
    id = models.CharField(primary_key=True, max_length=255)
    type = models.IntegerField()

    class Meta:
        db_table = 'aliases'


class Card(models.Model):
    uid = models.CharField(primary_key=True, max_length=255)
    user = models.ForeignKey('User')
    added_date = models.DateTimeField()
    active = models.BooleanField()

    class Meta:
        db_table = 'cards'


class Interest(models.Model):
    interest_id = models.AutoField(primary_key=True)
    category = models.ForeignKey('InterestCategory', db_column='category')
    suggested = models.BooleanField()
    name = models.CharField(max_length=255)
    url = models.CharField(max_length=255, blank=True, null=True)

    class Meta:
        db_table = 'interests'


class InterestCategory(models.Model):
    id = models.CharField(primary_key=True, max_length=255)

    class Meta:
        db_table = 'interests_categories'


class Learning(models.Model):
    learning_id = models.AutoField(primary_key=True)
    name = models.CharField(max_length=255)
    description = models.CharField(max_length=255)
    url = models.CharField(max_length=255, blank=True, null=True)

    class Meta:
        db_table = 'learnings'


class Location(models.Model):
    name = models.CharField(max_length=255)

    class Meta:
        db_table = 'locations'


class PasswordReset(models.Model):
    key = models.TextField(primary_key=True)
    user = models.ForeignKey('User')
    expires = models.DateTimeField()

    class Meta:
        db_table = 'password_resets'


class Permission(models.Model):
    perm_name = models.CharField(max_length=255)

    class Meta:
        db_table = 'perms'


class ProjectStates(models.Model):
    name = models.CharField(max_length=255)

    class Meta:
        db_table = 'project_states'


class Project(models.Model):
    user = models.ForeignKey('User')
    name = models.CharField(max_length=255)
    description = models.CharField(max_length=500)
    state = models.ForeignKey(ProjectStates)
    location = models.ForeignKey(Location)
    location_name = models.CharField(db_column='location', max_length=255, blank=True, null=True)
    updated_date = models.DateTimeField()
    from_date = models.DateTimeField()
    to_date = models.DateTimeField()
    contact = models.CharField(max_length=255, blank=True, null=True)

    class Meta:
        db_table = 'projects'


class ProjectLog(models.Model):
    timestamp = models.IntegerField()
    project = models.ForeignKey(Project)
    user = models.ForeignKey('User', blank=True, null=True)
    details = models.CharField(max_length=255)

    class Meta:
        db_table = 'projects_logs'


class Subscription(models.Model):
    user = models.ForeignKey('User')
    transaction = models.ForeignKey('Transaction')
    start_date = models.DateTimeField()
    end_date = models.DateTimeField()

    class Meta:
        db_table = 'subscriptions'


class Transaction(models.Model):
    fit_id = models.TextField(unique=True)
    timestamp = models.DateTimeField()
    user = models.ForeignKey('User')
    amount = models.DecimalField(max_digits=6, decimal_places=2)

    class Meta:
        db_table = 'transactions'


class UserPermission(models.Model):
    perm = models.ForeignKey(Permission)
    user = models.ForeignKey('User')

    class Meta:
        db_table = 'userperms'


class UserManager(BaseUserManager):
    def create_user(self, email, full_name, hackney, password):
        user = self.model(
            email=self.normalize_email(email),
            full_name=full_name,
            hackney=hackney,
        )

        user.set_password(password)
        user.save(using=self._db)
        return user


class FlourishPasswordField(models.CharField):
    def flourish_to_django(self, value):
        if value.startswith('fCryptography::password_hash'):
            _, salt, hash = value.split('#', 2)
            value = '%s$%s$%s' % ('flourish_sha1', salt, hash)
        return value

    def django_to_flourish(self, value):
        algo, salt, hash = value.split('$', 2)
        if algo == 'flourish_sha1':
            value = 'fCryptography::password_hash#%s#%s' % (salt, hash)
        return value

    def to_python(self, value):
        if value is None:
            return None

        value = self.flourish_to_django(value)
        return super(FlourishPasswordField, self).to_python(value)

    def from_db_value(self, value, expression, connection, context):
        return self.flourish_to_django(value)

    def get_prep_value(self, value):
        value = super(FlourishPasswordField, self).get_prep_value(value)
        if value is None:
            return value
        return self.django_to_flourish(value)


class User(AbstractBaseUser):
    USERNAME_FIELD = 'email'
    EMAIL_FIELD = 'email'
    REQUIRED_FIELDS = ['full_name', 'hackney']

    objects = UserManager()

    email = models.CharField(unique=True, max_length=255)
    password = FlourishPasswordField(max_length=255)  # shadows AbstractBaseUser
    last_login = models.DateTimeField(blank=True, null=True)  # shadows AbstractBaseUser
    full_name = models.CharField(max_length=255)
    subscribed = models.BooleanField(default=False)
    bankhash = models.TextField(blank=True, null=True)
    creationdate = models.TextField(blank=True, null=True)
    address = models.TextField(blank=True, null=True)
    hackney = models.BooleanField(default=False)
    subscription_period = models.IntegerField(default=0)
    nickname = models.CharField(unique=True, max_length=255, blank=True, null=True)
    irc_nick = models.CharField(unique=True, max_length=255, blank=True, null=True)
    gladosfile = models.CharField(max_length=255, blank=True, null=True)
    terminated = models.BooleanField(default=False)
    admin = models.BooleanField(default=False)
    has_profile = models.BooleanField(default=False)
    disabled_profile = models.BooleanField(default=False)
    doorbot_timestamp = models.DateTimeField(blank=True, null=True)
    emergency_name = models.CharField(max_length=255, blank=True, null=True)
    emergency_phone = models.CharField(max_length=40, blank=True, null=True)
    ldapuser = models.CharField(unique=True, max_length=32, blank=True, null=True)
    ldapnthash = models.CharField(max_length=32, blank=True, null=True)
    ldapsshahash = models.CharField(max_length=38, blank=True, null=True)
    ldapshell = models.CharField(max_length=32, blank=True, null=True)
    ldapemail = models.CharField(max_length=255, blank=True, null=True)

    class Meta:
        db_table = 'users'

    def __str__(self):
        return self.email

    @property
    def is_active(self):
        return not self.terminated

    @property
    def is_staff(self):
        return self.admin

    def get_full_name(self):
        return self.full_name

    def get_short_name(self):
        return self.full_name

    def has_perm(self, perm, obj=None):
        return self.admin

    def has_module_perms(self, app_label):
        return self.admin



class UserAlias(models.Model):
    user = models.ForeignKey(User)
    alias = models.ForeignKey(Alias)
    username = models.CharField(max_length=255)

    class Meta:
        db_table = 'users_aliases'
        unique_together = (('user', 'alias'),)


class UserInterest(models.Model):
    user = models.ForeignKey(User)
    interest = models.ForeignKey(Interest)

    class Meta:
        db_table = 'users_interests'
        unique_together = (('user', 'interest'),)


class UserLearning(models.Model):
    user = models.ForeignKey(User)
    learning = models.ForeignKey(Learning)

    class Meta:
        db_table = 'users_learnings'
        unique_together = (('user', 'learning'),)


class UserProfile(models.Model):
    user = models.OneToOneField(User, primary_key=True)
    allow_email = models.BooleanField()
    allow_doorbot = models.BooleanField()
    photo = models.CharField(max_length=255, blank=True, null=True)
    website = models.CharField(max_length=255, blank=True, null=True)
    description = models.CharField(max_length=500, blank=True, null=True)

    class Meta:
        db_table = 'users_profiles'
