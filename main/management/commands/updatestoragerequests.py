from django.conf import settings
from django.core.management.base import BaseCommand, CommandError
from django.db import transaction
from django.template.loader import render_to_string
from django.utils import timezone

from main.models import Project, ProjectState

from datetime import timedelta
import re
import subprocess
import traceback


class Command(BaseCommand):
    help = 'Updates the state of projects and emails owners if requested'

    def add_arguments(self, parser):
        parser.add_argument('--send-updates', dest='send_updates', action='store_true')
        parser.add_argument('--update-db', dest='update_db', action='store_true')

    def handle(self, *args, **options):
        outstanding_projects = Project.objects.filter(state__name__in=[
            ProjectState.PendingApproval,
            ProjectState.Approved,
            ProjectState.Extended,
            ProjectState.PassedDeadline,
        ])

        for project in outstanding_projects:
            try:
                with transaction.atomic():
                    updated = self.update_state(project, send_updates=options['send_updates'])
                    if not options['update_db']:
                        transaction.set_rollback(True)

            except Exception as e:
                for l in traceback.format_exc().splitlines():
                    self.stderr.write(self.style.ERROR(l))

                self.stderr.write(self.style.ERROR('Error updating %s' % project))

        self.stdout.write(self.style.SUCCESS('Processing complete'))


    def calculate_state(self, project, subscribed, today):
        if project.state == ProjectState.PendingApproval:
            if today > project.from_date + timedelta(days=21):
                return ProjectState.Archived

        if not subscribed:
            if project.state == ProjectState.PendingApproval:
                return ProjectState.Unapproved

            elif project.state in [ProjectState.Approved, ProjectState.Extended]:
                return ProjectState.PassedDeadline

        elif project.state == ProjectState.PendingApproval:
            if today > project.created_date + project.approve_after and not project.has_activity:
                return ProjectState.Approved

        elif project.state in [ProjectState.Approved, ProjectState.Extended]:
            if today > project.deadline_date:
                return ProjectState.PassedDeadline

        return project.state


    def calculate_action(self, project, subscribed, new_state, today):
        mailing_list = True
        email = True

        if new_state == project.state:
            if new_state not in [ProjectState.Approved, ProjectState.Extended, ProjectState.PassedDeadline]:
                log_msg = None

            elif today == project.to_date - timedelta(days=3):
                log_msg = 'Three days before removal, reminder email sent to owner'
                mailing_list = False

            elif today == project.to_date + timedelta(days=1):
                log_msg = 'Day after removal, reminder email sent to owner'
                mailing_list = False

            elif today > project.to_date and today.weekday() == project.to_date.weekday():
                log_msg = 'Week anniversary of Passed Deadline, reminder email sent to owner'
                mailing_list = True

            else:
                log_msg = None

        elif not subscribed:
            log_msg = "Owner isn't a subscribed member, status automatically changed to %s" % new_state

        elif new_state == ProjectState.Approved:
            log_msg = '%s days passed and no comments on the Mailing List, status automatically changed to %s' % (
                      project.approve_after.days, new_state)

        elif new_state == ProjectState.PassedDeadline:
            log_msg = 'Status automatically changed to %s' % new_state

        elif new_state == ProjectState.Archived:
            log_msg = 'Status automatically changed to %s' % new_state
            mailing_list = False
            email = False

        return log_msg, mailing_list, email


    def update_state(self, project, send_updates=False):
        subscribed = project.user.subscribed
        today = timezone.now().date()
        new_state = self.calculate_state(project, subscribed, today)

        if project.state == ProjectState.PendingApproval and new_state == ProjectState.Approved:
            # Do a final activity check. This is relatively fragile and slow.
            # Ideally we'd get email updates and check them in has_activity.
            self.stdout.write('Checking mailing list posts for %r' % project)

            output = subprocess.check_output([
                'phantomjs',
                'bin/storage-requests-phantomjs-ml-scrape.js',
                str(project.id),
            ])

            match = re.match('^Posts found ([0-9]+)\n', output)
            if not match:
                self.stdout.write(self.style.NOTICE('Unexpected output, not autoapproving: %r' % output))
                new_state = ProjectState.PendingApproval

            else:
                posts = int(match.group(1))
                if posts == 0:
                    self.stdout.write(self.style.NOTICE('No posts found, not autoapproving'))
                    new_state = ProjectState.PendingApproval

                elif posts > 1:
                    self.stdout.write('Found %s posts, not autoapproving' % posts)
                    new_state = ProjectState.PendingApproval


        log_msg, mailing_list, email = self.calculate_action(project, subscribed, new_state, today)

        if log_msg is None:
            self.stdout.write('No changes or reminders for %r' % project)
            return

        self.stdout.write('Setting state to %s for %r' % (new_state, project))
        project.state = new_state
        self.stdout.write('Adding log message %r' % log_msg)
        project.add_log(log_msg)
        project.save()

        if send_updates:
            if email:
                body_html = render_to_string('main/email/project_update.html', {
                    'project': project,
                    'new_state': new_state,
                    'subscribed': subscribed,
                    'ProjectState': ProjectState,
                    'CONTACT_EMAIL': settings.CONTACT_EMAIL,
                })
                project.email_owner(body_html)
                self.stdout.write('Emailed the owner')

            if mailing_list:
                body_html = render_to_string('main/email/project_log.html', {
                    'project': project,
                    'log_msg': log_msg,
                })
                project.email_mailing_list(body_html)
                project.add_log('Posted to the Mailing List');
                self.stdout.write('Emailed the list')

        self.stdout.write(self.style.SUCCESS('Successfully updated'))

