# Discord Integration

The integration of the Hackspace website with the Discord Server allows for giving a "registered" role to Discord Users who have added their Discord Alias to their hackspace member profile and/or a "subscribed" role to currently subscribed members who have done the same.

In order to communicate with Discord a bot needs to be added to the server with "Manage Roles" permissions (268435456)

The following information needs to be added to lhs/settings.py

~~~
DISCORD = {
    'bot_token': '<Bot Token from Developer Portal>',
    'guild_id': <ID of Hackspace Server>,
    'bot_role': '<Role assigned to bot, must be higher than subscribed_role>',

    # at least one of the following two settings must be included
    'subscribed_role': '<Role for subscribed members>',
    'registered_role': '<Role for unsubscribed members or all members depending on toggle_roles>',

    'toggle_roles': <True/False> # sets whether promoting a user to the subscribed role
                                 # also toggles the registered role off (optional, defaults to False)

    # optional
    ignore_roles: ['role to ignore', 'other role to ignore'...] # roles to be ignored when removing
                                                                # subscribed or registered roles
}
~~~

## Management Commands

Integration is implemented with django management commands executed with the command line

~~~
python manage.py <command> [parameters]
~~~
***

### get_members_discord_registrations
~~~
python manage.py get_members_discord_registrations
~~~

Displays a table of Discord users who have a matching alias in the hackspace member database and/or have a hackspace registration role assigned to them. Both the current status assigned to them on Discord and the correct status derived from their hackspace subscription state are displayed

***

### set_member_discord_registration
~~~
python manage.py set_member_discord_registration  <discord_name> <None|Registered|Subscribed>
~~~

This is really an internal function exposed for testing purposes. It sets a discord member's roles according to status provided. This is done independently of the member's hackspace status and will be overriden in the next refresh

As well as adding or removing the registered or subscribed roles themselves, associated roles are also removed according to the following rules.

When the subscribed_role is removed from a member, so are all roles they have in the hierarchy between subscribed_role and bot_role, excluding any in ignore_roles

When the registered_role is removed from a member, so are all roles they have in the hierarchy between registered_role and subscribed_role, excluding any in ignore_roles

***

### refresh_members_discord_registrations
~~~
python manage.py refresh_members_discord_registrations
~~~

This is the main function for maintaining synchronization between the hackspace members database and the Discord server member roles. It runs the same checks as in get_members_discord_registrations and for every user whose status on Discord does not match the status determined by their hackspace member status changes the Discord roles accordingly as in set)member_discord_registration

Running this in a cron job after the maintain_members command will keep the Discord server roles in sync (within 6 hours at least)

