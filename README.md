This is the source code for the [London Hackspace web
site](http://london.hackspace.org.uk)

(It used to also support http://hackspace.org.uk, but this is now just MediaWiki)

## Setting up Apache
Make sure you have mod_rewrite enabled.
You'll need to include the /apache-config file in your apache host config. Example:

    <VirtualHost *:80>
    ...
    Include /path/to/root/dir/apache-config
    </VirtualHost>

## Creating the DB
You can create the database from the schema located in ./etc:

    sqlite3 ./var/database.db < ./etc/schema.sql

Note that SQL Lite needs write access to the directory containing the .db file to do its journaling:

    chmod -R 777 ./var

## Configuring MediaWiki users panel
Create a file in ./var/mediawiki.php with the $type, $server, $username,
$password, $database, and $path variables set (where $type is a string
describing the database type, and $path is a complete path including trailing
slash to the MediaWiki instance).

## Enabling Calendar management
1. Enable the Calendar API at https://console.developers.google.com/.../apiui/api
2. Create a service account for OAuth at https://console.developers.google.com/.../apiui/credential
3. Put the OAuth certificate in ./var, and correct the service_account_name and key_file_location in lib/calendar.php
4. Go to the Hackspace calendar on Google and share it with the same service_account_name address

API documentation https://developers.google.com/api-client-library/php/
