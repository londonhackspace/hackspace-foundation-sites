from __future__ import unicode_literals

from django.db import models
from django.contrib.auth.base_user import AbstractBaseUser, BaseUserManager


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

    def __str__(self):
        return self.perm_name


class UserPermission(models.Model):
    perm = models.ForeignKey(Permission)
    user = models.ForeignKey('User')

    class Meta:
        db_table = 'userperms'

    def __str__(self):
        return self.perm.perm_name


class UserManager(BaseUserManager):
    def create_user(self, email, full_name, hackney, password, **extra):
        user = self.model(
            email=self.normalize_email(email),
            full_name=full_name,
            hackney=hackney,
            **extra
        )

        user.set_password(password)
        user.save(using=self._db)
        return user

    def create_superuser(self, email, full_name, hackney, password, **extra):
        extra.setdefault('subscribed', True)
        extra.setdefault('admin', True)

        if extra.get('subscribed') is not True:
            raise ValueError('Superuser must have subscribed=True.')
        if extra.get('admin') is not True:
            raise ValueError('Superuser must have admin=True.')

        return self.create_user(email, full_name, hackney, password, **extra)



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


