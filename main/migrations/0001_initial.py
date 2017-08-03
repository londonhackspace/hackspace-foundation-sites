# -*- coding: utf-8 -*-
from __future__ import unicode_literals

from django.db import migrations, models


class Migration(migrations.Migration):

    dependencies = [
    ]

    operations = [
        migrations.CreateModel(
            name='Alias',
            fields=[
                ('id', models.CharField(primary_key=True, serialize=False, max_length=255)),
                ('type', models.IntegerField()),
            ],
            options={
                'db_table': 'aliases',
            },
        ),
        migrations.CreateModel(
            name='Card',
            fields=[
                ('uid', models.CharField(primary_key=True, serialize=False, max_length=255)),
                ('added_date', models.DateTimeField()),
                ('active', models.BooleanField()),
            ],
            options={
                'db_table': 'cards',
            },
        ),
        migrations.CreateModel(
            name='Interest',
            fields=[
                ('interest_id', models.AutoField(primary_key=True, serialize=False)),
                ('suggested', models.BooleanField()),
                ('name', models.CharField(max_length=255)),
                ('url', models.CharField(blank=True, max_length=255, null=True)),
            ],
            options={
                'db_table': 'interests',
            },
        ),
        migrations.CreateModel(
            name='InterestCategory',
            fields=[
                ('id', models.CharField(primary_key=True, serialize=False, max_length=255)),
            ],
            options={
                'db_table': 'interests_categories',
            },
        ),
        migrations.CreateModel(
            name='Learning',
            fields=[
                ('learning_id', models.AutoField(primary_key=True, serialize=False)),
                ('name', models.CharField(max_length=255)),
                ('description', models.CharField(max_length=255)),
                ('url', models.CharField(blank=True, max_length=255, null=True)),
            ],
            options={
                'db_table': 'learnings',
            },
        ),
        migrations.CreateModel(
            name='Location',
            fields=[
                ('id', models.AutoField(auto_created=True, primary_key=True, serialize=False, verbose_name='ID')),
                ('name', models.CharField(max_length=255)),
            ],
            options={
                'db_table': 'locations',
            },
        ),
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
            name='Project',
            fields=[
                ('id', models.AutoField(auto_created=True, primary_key=True, serialize=False, verbose_name='ID')),
                ('name', models.CharField(max_length=255)),
                ('description', models.CharField(max_length=500)),
                ('location_name', models.CharField(blank=True, max_length=255, null=True, db_column='location')),
                ('updated_date', models.DateTimeField()),
                ('from_date', models.DateTimeField()),
                ('to_date', models.DateTimeField()),
                ('contact', models.CharField(blank=True, max_length=255, null=True)),
                ('location', models.ForeignKey(to='main.Location')),
            ],
            options={
                'db_table': 'projects',
            },
        ),
        migrations.CreateModel(
            name='ProjectLog',
            fields=[
                ('id', models.AutoField(auto_created=True, primary_key=True, serialize=False, verbose_name='ID')),
                ('timestamp', models.IntegerField()),
                ('details', models.CharField(max_length=255)),
                ('project', models.ForeignKey(to='main.Project')),
            ],
            options={
                'db_table': 'projects_logs',
            },
        ),
        migrations.CreateModel(
            name='ProjectStates',
            fields=[
                ('id', models.AutoField(auto_created=True, primary_key=True, serialize=False, verbose_name='ID')),
                ('name', models.CharField(max_length=255)),
            ],
            options={
                'db_table': 'project_states',
            },
        ),
        migrations.CreateModel(
            name='Subscription',
            fields=[
                ('id', models.AutoField(auto_created=True, primary_key=True, serialize=False, verbose_name='ID')),
                ('start_date', models.DateTimeField()),
                ('end_date', models.DateTimeField()),
            ],
            options={
                'db_table': 'subscriptions',
            },
        ),
        migrations.CreateModel(
            name='Transaction',
            fields=[
                ('id', models.AutoField(auto_created=True, primary_key=True, serialize=False, verbose_name='ID')),
                ('fit_id', models.TextField(unique=True)),
                ('timestamp', models.DateTimeField()),
                ('amount', models.DecimalField(max_digits=6, decimal_places=2)),
            ],
            options={
                'db_table': 'transactions',
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
            name='UserAlias',
            fields=[
                ('id', models.AutoField(auto_created=True, primary_key=True, serialize=False, verbose_name='ID')),
                ('username', models.CharField(max_length=255)),
                ('alias', models.ForeignKey(to='main.Alias')),
            ],
            options={
                'db_table': 'users_aliases',
            },
        ),
        migrations.CreateModel(
            name='UserInterest',
            fields=[
                ('id', models.AutoField(auto_created=True, primary_key=True, serialize=False, verbose_name='ID')),
                ('interest', models.ForeignKey(to='main.Interest')),
            ],
            options={
                'db_table': 'users_interests',
            },
        ),
        migrations.CreateModel(
            name='UserLearning',
            fields=[
                ('id', models.AutoField(auto_created=True, primary_key=True, serialize=False, verbose_name='ID')),
                ('learning', models.ForeignKey(to='main.Learning')),
            ],
            options={
                'db_table': 'users_learnings',
            },
        ),
        migrations.CreateModel(
            name='UserPermission',
            fields=[
                ('id', models.AutoField(auto_created=True, primary_key=True, serialize=False, verbose_name='ID')),
                ('perm', models.ForeignKey(to='main.Permission')),
            ],
            options={
                'db_table': 'userperms',
            },
        ),
        migrations.CreateModel(
            name='UserProfile',
            fields=[
                ('user', models.OneToOneField(to='main.User', primary_key=True, serialize=False)),
                ('allow_email', models.BooleanField()),
                ('allow_doorbot', models.BooleanField()),
                ('photo', models.CharField(blank=True, max_length=255, null=True)),
                ('website', models.CharField(blank=True, max_length=255, null=True)),
                ('description', models.CharField(blank=True, max_length=500, null=True)),
            ],
            options={
                'db_table': 'users_profiles',
            },
        ),
        migrations.AddField(
            model_name='userpermission',
            name='user',
            field=models.ForeignKey(to='main.User'),
        ),
        migrations.AddField(
            model_name='userlearning',
            name='user',
            field=models.ForeignKey(to='main.User'),
        ),
        migrations.AddField(
            model_name='userinterest',
            name='user',
            field=models.ForeignKey(to='main.User'),
        ),
        migrations.AddField(
            model_name='useralias',
            name='user',
            field=models.ForeignKey(to='main.User'),
        ),
        migrations.AddField(
            model_name='transaction',
            name='user',
            field=models.ForeignKey(to='main.User'),
        ),
        migrations.AddField(
            model_name='subscription',
            name='transaction',
            field=models.ForeignKey(to='main.Transaction'),
        ),
        migrations.AddField(
            model_name='subscription',
            name='user',
            field=models.ForeignKey(to='main.User'),
        ),
        migrations.AddField(
            model_name='projectlog',
            name='user',
            field=models.ForeignKey(to='main.User', blank=True, null=True),
        ),
        migrations.AddField(
            model_name='project',
            name='state',
            field=models.ForeignKey(to='main.ProjectStates'),
        ),
        migrations.AddField(
            model_name='project',
            name='user',
            field=models.ForeignKey(to='main.User'),
        ),
        migrations.AddField(
            model_name='passwordreset',
            name='user',
            field=models.ForeignKey(to='main.User'),
        ),
        migrations.AddField(
            model_name='interest',
            name='category',
            field=models.ForeignKey(to='main.InterestCategory', db_column='category'),
        ),
        migrations.AddField(
            model_name='card',
            name='user',
            field=models.ForeignKey(to='main.User'),
        ),
        migrations.AlterUniqueTogether(
            name='userlearning',
            unique_together=set([('user', 'learning')]),
        ),
        migrations.AlterUniqueTogether(
            name='userinterest',
            unique_together=set([('user', 'interest')]),
        ),
        migrations.AlterUniqueTogether(
            name='useralias',
            unique_together=set([('user', 'alias')]),
        ),
    ]
