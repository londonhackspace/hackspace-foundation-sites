# -*- coding: utf-8 -*-
from __future__ import unicode_literals

from django.db import migrations, models


class Migration(migrations.Migration):

    dependencies = [
    ]

    operations = [
        migrations.CreateModel(
            name='PasswordReset',
            fields=[
                ('key', models.TextField(primary_key=True, serialize=False)),
                ('expires', models.DateTimeField()),
            ],
            options={
                'db_table': 'password_resets',
            },
        ),
        migrations.CreateModel(
            name='Permission',
            fields=[
                ('id', models.AutoField(auto_created=True, primary_key=True, serialize=False, verbose_name='ID')),
                ('perm_name', models.CharField(max_length=255)),
            ],
            options={
                'db_table': 'perms',
            },
        ),
        migrations.CreateModel(
            name='User',
            fields=[
                ('id', models.AutoField(auto_created=True, primary_key=True, serialize=False, verbose_name='ID')),
                ('email', models.CharField(max_length=255, unique=True)),
                ('password', models.CharField(max_length=255)),
                ('full_name', models.CharField(max_length=255)),
                ('subscribed', models.BooleanField()),
                ('bankhash', models.TextField(blank=True, null=True)),
                ('creationdate', models.TextField(blank=True, null=True)),
                ('address', models.TextField(blank=True, null=True)),
                ('hackney', models.BooleanField()),
                ('subscription_period', models.IntegerField()),
                ('nickname', models.CharField(blank=True, max_length=255, null=True, unique=True)),
                ('irc_nick', models.CharField(blank=True, max_length=255, null=True, unique=True)),
                ('gladosfile', models.CharField(blank=True, max_length=255, null=True)),
                ('terminated', models.BooleanField()),
                ('admin', models.BooleanField()),
                ('has_profile', models.BooleanField()),
                ('disabled_profile', models.BooleanField()),
                ('doorbot_timestamp', models.DateTimeField(blank=True, null=True)),
                ('emergency_name', models.CharField(blank=True, max_length=255, null=True)),
                ('emergency_phone', models.CharField(blank=True, max_length=40, null=True)),
                ('ldapuser', models.CharField(blank=True, max_length=32, null=True, unique=True)),
                ('ldapnthash', models.CharField(blank=True, max_length=32, null=True)),
                ('ldapsshahash', models.CharField(blank=True, max_length=38, null=True)),
                ('ldapshell', models.CharField(blank=True, max_length=32, null=True)),
                ('ldapemail', models.CharField(blank=True, max_length=255, null=True)),
            ],
            options={
                'db_table': 'users',
            },
        ),
        migrations.CreateModel(
            name='UserPermission',
            fields=[
                ('id', models.AutoField(auto_created=True, primary_key=True, serialize=False, verbose_name='ID')),
                ('perm', models.ForeignKey(to='lhsauth.Permission')),
            ],
            options={
                'db_table': 'userperms',
            },
        ),
        migrations.AddField(
            model_name='userpermission',
            name='user',
            field=models.ForeignKey(to='lhsauth.User'),
        ),
        migrations.AddField(
            model_name='passwordreset',
            name='user',
            field=models.ForeignKey(to='lhsauth.User'),
        ),
    ]
