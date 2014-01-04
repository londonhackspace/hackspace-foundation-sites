This is the source code for the [London Hackspace web
site](http://london.hackspace.org.uk)

(It used to also support http://hackspace.org.uk, but this is now just MediaWiki)

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
