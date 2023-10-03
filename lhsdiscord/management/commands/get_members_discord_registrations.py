from django.core.management.base import BaseCommand

from lhsdiscord.discord_utils import *



def getMaxColumnWidths(row, current):
    if current == None:
        current = []
        for column in row:
            current.append(len(column))
    else:
        for index in range(len(row)):
            current[index] = max(current[index], len(row[index]))
    return current
    
def sizeTable(titles, rows):
    widths = getMaxColumnWidths(titles, None)
    for row in rows:
        widths = getMaxColumnWidths(row, widths)
    return widths

def printTable(titles, rows):
    widths = sizeTable(titles, rows)
    format_string = ''
    separator = ''
    for index in range(len(titles)):
        format_string += '| {:' + str(widths[index]) + '} '
        separator += '+' + ('-' * (widths[index] + 2))
    format_string += '|'
    separator += '+'
    title_string = format_string.format(*titles)
    print(separator)
    print (title_string)
    print(separator)
    for row in rows:
        print (format_string.format(*row))
    print(separator)
 
def printDiscordMembers(members):
    titles = ['Current', 'Change To', 'Discord Name', 'LHS ID', 'LHS Full Name', 'Subscribed']
    rows = []
    for member in members:
        rows.append([
            DiscordStatus.toStr(member.current_status),
            member.status_change(), 
            member.discord_name, 
            str(member.id), 
            member.full_name, 
            str(member.subscribed)
        ])
    printTable(titles, rows)




        

async def handleCommand(client, parameter):
    members = client.getDiscordMembers()
    printDiscordMembers(members)


class Command(BaseCommand):
    help = 'Gets the current registrations of members on the Discord Server'

    def handle(self, *args, **options):
        print(f"Getting Discord Members' registrations")
        client.run(handleCommand, [])
                