from django.core.management.base import BaseCommand

from lhsdiscord.discord_utils import *

async def handleCommand(client, parameter):
    members = client.getDiscordMembers()
    for member in members:
        if member.needs_updating():
            await client.setMemberRegistration(member.discord_name, member.actual_status())
class Command(BaseCommand):
    help = 'Gets the current registrations of members on the Discord Server'

    def handle(self, *args, **options):
        print(f"Getting Discord Members' registrations")
        client.run(handleCommand, [])
 