from django.core.management.base import BaseCommand

from lhsdiscord.discord_utils import *

async def handleCommand(client, parameters):
    status = DiscordStatus.fromStr(parameters[1])
    if status == DiscordStatus.INVALID:
        print(f"Error: Status {parameters[1]} is not valid")
    else:
        await client.setMemberRegistration(parameters[0], status)

class Command(BaseCommand):
    help = 'Gives a Discord Member the specified status'

    def add_arguments(self, parser):
        # Positional arguments
        parser.add_argument("name", nargs=1, type=str)
        parser.add_argument("status", nargs=1, type=str)

    def handle(self, *args, **options):
        print("Giving members specified status")
        client.run(handleCommand, [options["name"][0], options["status"][0]])