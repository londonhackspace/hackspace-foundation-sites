from django.db import connection
from django.conf import settings
import discord
from asgiref.sync import sync_to_async
from enum import Enum

class DiscordStatus(Enum):
    NONE = 0
    REGISTERED = 1
    SUBSCRIBED = 2
    INVALID = 3

    @staticmethod
    def toStr(value):
        if value == DiscordStatus.NONE:
            return "None"
        elif value == DiscordStatus.REGISTERED:
            return "Registered"
        elif value == DiscordStatus.SUBSCRIBED:
            return "Subscribed"
        elif value == DiscordStatus.INVALID:
            return "Invalid"
        else:
            return "Error"

    @staticmethod
    def fromStr(value):
        if value.lower() == "none":
            return DiscordStatus.NONE
        elif value.lower() == "registered":
            return DiscordStatus.REGISTERED
        elif value.lower() == "subscribed":
            return DiscordStatus.SUBSCRIBED
        else:
            return DiscordStatus.INVALID
       


class DiscordMember:
    def __init__(self, client, sql_row):
        self.id = sql_row[0] if sql_row[0] is not None else 0
        self.full_name = sql_row[1] if sql_row[1] is not None else ''
        self.discord_name = sql_row[2]
        self.subscribed = sql_row[3] if sql_row[3] is not None else False
        discord_registered = sql_row[4]
        discord_subscribed = sql_row[5]
        self.client = client
        self.current_status = self.deriveStatus(client, discord_registered, discord_subscribed)
        
    @staticmethod
    def deriveStatus(client, registered, subscribed):
        if not registered and not subscribed:
            return DiscordStatus.NONE
        elif not client.has_registered_role() :
            if registered:
                return DiscordStatus.INVALID
            else:
                return DiscordStatus.SUBSCRIBED if subscribed else DiscordStatus.NONE
        elif not client.has_subscribed_role():
            if subscribed:
                return DiscordStatus.INVALID
            else:
                return DiscordStatus.REGISTERED if registered else DiscordStatus.NONE
        elif client.toggle_roles and (subscribed == registered):
            return DiscordStatus.INVALID
        elif subscribed:
            if not client.toggle_roles and not registered:
                return DiscordStatus.INVALID
            else:
                return DiscordStatus.SUBSCRIBED
        else:
            return DiscordStatus.REGISTERED


    def actual_status(self):
        if self.id == 0:
            return DiscordStatus.NONE
        elif self.subscribed:
            return DiscordStatus.SUBSCRIBED if self.client.has_subscribed_role() else DiscordStatus.REGISTERED
        else:
            return DiscordStatus.REGISTERED if self.client.has_registered_role() else DiscordStatus.NONE

    def needs_updating(self):
        return self.current_status != self.actual_status()

    def status_change(self):
        if self.needs_updating():
            return DiscordStatus.toStr(self.actual_status())
        else:
            return ' '


@sync_to_async
def GetDiscordMembers(client):
    # build a temporary database table of discord users
    with connection.cursor() as cursor:
        cursor.execute(
            """
            CREATE TEMP TABLE temp_discord_users(
            discord_name VARCHAR PRIMARY KEY,
            discord_registered BOOLEAN,
            discord_subscribed BOOLEAN
            );
            """)
        for member in client.guild.members:
            if not member.bot:
                member_is_registered = (member.get_role(client.registered_role.id) != None) if client.registered_role is not None else False
                member_is_subscribed = (member.get_role(client.subscribed_role.id) != None) if client.subscribed_role is not None else False
                cursor.execute(
                    """INSERT INTO temp_discord_users
                    (discord_name, discord_registered, discord_subscribed)
                    VALUES (%s, %s, %s)
                    """, (member._user.__str__(), member_is_registered, member_is_subscribed))
        
        cursor.execute("""
            WITH lhs_discord AS 
                ( SELECT 
                users_aliases.user_id, 
                users.full_name,
                users_aliases.username as discord_name,
                users.subscribed
                FROM users_aliases
                INNER JOIN users
                ON users_aliases.user_id = users.id
                WHERE users_aliases.alias_id = 'Discord'
                )
            SELECT 
            lhs_discord.user_id, 
            lhs_discord.full_name,
            temp_discord_users.discord_name,
            lhs_discord.subscribed,
            temp_discord_users.discord_registered,
            temp_discord_users.discord_subscribed
            FROM lhs_discord
            RIGHT JOIN temp_discord_users
            ON lhs_discord.discord_name = temp_discord_users.discord_name 
            WHERE lhs_discord.user_id IS NOT NULL OR temp_discord_users.discord_registered OR temp_discord_users.discord_subscribed    
            """)

        
        members = []
        while True:
            row = cursor.fetchone()
            if row == None:
                break
            members.append(DiscordMember(client, row))
        
        cursor.execute("DROP TABLE IF EXISTS temp_discord_users;")
            
        return members

class DiscordMemberClient(discord.Client):
    def __init__(self):
        super().__init__(intents=discord.Intents(members=True, guilds=True))
        self.handle_command = None
        self.command_parameters = []
        self.member_role = None
        self.registered_role = None
        self.subscribed_role = None
        self.toggle_roles = False
        self.ignore_roles = []

    def has_subscribed_role(self):
        return self.subscribed_role is not None

    def has_registered_role(self):
        return self.registered_role is not None

    async def getDiscordMembers(self):
        return await GetDiscordMembers(self)

    def run(self, command_handler, command_parameters):
        self.handle_command = command_handler
        self.command_parameters = command_parameters
        if 'bot_token' in settings.DISCORD:
            super().run(settings.DISCORD['bot_token'])
        else:
            print("Error: bot_token not defined in DISCORD settings")

    def onReady(self):
        if not 'guild_id' in settings.DISCORD:
            print("Error: guild_id not defined in DISCORD settings")
            return False;
        guild = client.get_guild(settings.DISCORD['guild_id'])
        if guild == None:
            print("Error: The guild ID is incorrect")
            return False
        client.guild = guild
        if 'bot_role' in settings.DISCORD:
            self.bot_role = discord.utils.get(self.guild.roles, name=settings.DISCORD['bot_role'])
            if self.bot_role == None:
                print("Error: bot_role in settings does not match any on server")
                return False
        else:
            print ('Error: No bot_role in settings')
        if 'subscribed_role' in settings.DISCORD:
            self.subscribed_role = discord.utils.get(self.guild.roles, name=settings.DISCORD['subscribed_role'])
            if self.subscribed_role == None:
                print("Error: subscribed_role in settings does not match any on server")
                return False
            if self.subscribed_role > self.bot_role:
                print("Error: bot_role must be higher in the hierarchy than subscribed_role")
                return False
        if 'registered_role' in settings.DISCORD:
            self.registered_role = discord.utils.get(self.guild.roles, name=settings.DISCORD['registered_role'])
            if self.registered_role == None:
                print("Error: registered_role in settings does not match any on server")
                return False
            if self.registered_role > self.bot_role:
                print("Error: bot_role must be higher in the hierarchy than registered_role")
                return False
            if self.subscribed_role is not None and self.subscribed_role < self.registered_role:
                print("Error: subscribed_role must be higher in the hierarchy than registered_role")
                return False
        if self.subscribed_role is None and self.registered_role is None:
            print("Error: you must provide at least one of registered_role or member_role")
            return False
        if 'toggle_roles' in settings.DISCORD:
            self.toggle_roles = settings.DISCORD['toggle_roles']
        if 'ignore_roles' in settings.DISCORD:
            for role_name in settings.DISCORD['ignore_roles']:
                role = discord.utils.get(self.guild.roles, name=role_name)
                if role is None:
                    print(f"Error: role {role_name} does not exist on server")
                    return False
                if role == self.bot_role:
                    print(f"Error: cannot ignore bot_role")
                    return False
                if role == self.subscribed_role:
                    print(f"Error: cannot ignore subscribed_role")
                    return False
                if role == self.registered_role:
                    print(f"Error: cannot ignore registered_role")
                    return False
                self.ignore_roles.append(role)
        return True

    async def setMemberRegistration(self, name, new_status):
        member = self.guild.get_member_named(name)
        if member == None:
            print(f"Error: The server had no member named {name}")
        else:
            member_is_registered = (member.get_role(self.registered_role.id) != None) if self.registered_role is not None else False
            member_is_subscribed = (member.get_role(self.subscribed_role.id) != None) if self.subscribed_role is not None else False
            current_status = DiscordMember.deriveStatus(self, member_is_registered, member_is_subscribed)
            if (current_status == new_status):
                print(f"Info: Member {name} already has status {DiscordStatus.toStr(new_status)}, no change required")
            elif new_status == DiscordStatus.REGISTERED and self.registered_role is None:
                print("Error: Registered Status is not supported by current configuration")
            elif new_status == DiscordStatus.SUBSCRIBED and self.subscribed_role is None:
                print("Error: Subscribed Status is not supported by current configuration")
            else:
                remove_roles = []
                add_roles = []
                if new_status != DiscordStatus.SUBSCRIBED and member_is_subscribed:
                    roles = member.roles
                    if (self.subscribed_role is not None):
                        while roles[0] < self.subscribed_role:
                            del roles[0]
                    while len(roles) > 0 and roles[-1] >= self.bot_role:
                        roles.pop()
                    remove_roles = roles
                if new_status == DiscordStatus.NONE and (member_is_registered or member_is_subscribed):
                    roles = member.roles
                    if (self.registered_role is not None):
                        while roles[0] < self.registered_role:
                            del roles[0]
                    while len(roles) > 0 and roles[-1] > self.subscribed_role:
                        roles.pop()
                    remove_roles += roles
                elif new_status == DiscordStatus.REGISTERED and not member_is_registered:
                    add_roles.append(self.registered_role)
                else: # Subscribed
                    if not member_is_subscribed:
                        add_roles.append(self.subscribed_role)
                    if self.toggle_roles and member_is_registered:
                        remove_roles.append(self.registered_role)
                    elif not self.toggle_roles and not member_is_registered:
                        add_roles.append(self.registered_role)
                print (f"Setting {name} status to {DiscordStatus.toStr(new_status)}")
                index = 0
                while index < len(remove_roles):
                    if remove_roles[index] in self.ignore_roles:
                        del remove_roles[index]
                    else:
                        index += 1
                await member.remove_roles(*remove_roles)
                await member.add_roles(*add_roles)

        


client = DiscordMemberClient()

@client.event
async def on_ready():
    print("We have logged in as {0.user}".format(client))
    if client.onReady():
        if client.handle_command is not None:
            handle_command = client.handle_command
            await handle_command(client, client.command_parameters)
        else:
            print('Error: No command handler specified')
    await client.close()

    
