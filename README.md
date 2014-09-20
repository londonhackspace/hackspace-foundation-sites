This is the source code for the [London Hackspace web
site](http://london.hackspace.org.uk)

(It used to also support http://hackspace.org.uk, but this is now just MediaWiki)

## Packages needed

php5 sqlite php5-sqlite php-apc sqlite3 ruby-sqlite3 ruby-erubis rubygems ruby-hpricot ruby-mail

## Setting up Apache
Make sure you have mod_rewrite and expires enabled. (a2enmod rewrite, a2enmod expires)

You'll need to include the /apache-config file in your apache host config. Example:

    <VirtualHost *:80>
    ...
    Include /path/to/root/dir/apache-config
    </VirtualHost>

### Google api libs

    cd /var/www/hackspace-foundation-sites/lib/
    git clone https://github.com/google/google-api-php-client.git GoogleAPI

(Which revision? does it matter?)

### session dir

    cd /var/www/hackspace-foundation-sites/var
    chown www-data:www-data session

## Creating the DB
You can create the database from the schema located in ./etc:

    sqlite3 ./var/database.db < ./etc/schema.sql
    cd var
    chown www-data:adm database.db ; chmod 0660 database.db

Note that SQL Lite needs write access to the directory containing the .db file to do its journaling:

    chgrp www-data ./var
    chmod 0775 ./var

Now create an accout on the site ("Join"), and then:

    cd /var/www/hackspace-foundation-sites/var/
    sqlite3 database.db
    sqlite> update users set subscribed=1;
    sqlite> update users set admin=1;

to make yourself a member and an admin

## letting apache add ldap users (!)

use visudo to add this:

    www-data ALL=(www-data:ldapadmin) NOPASSWD:NOSETENV: /var/www/hackspace-foundation-sites/bin/ldap-add.sh, /var/www/hackspace-foundation-sites/bin/ldap-delete.sh

and then:

    groupadd ldapadmin
    chgrp ldapadmin /etc/smbldap-tools/smbldap_bind.conf
    chmod 0640 /etc/smbldap-tools/smbldap_bind.conf

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
