#!/usr/bin/env bash
export DEBIAN_FRONTEND=noninteractive

# Install required packages
apt-get update
apt-get install -y php5 sqlite php5-sqlite php5-curl php-apc sqlite3 ruby-sqlite3 ruby-erubis rubygems ruby-hpricot ruby-mail git


# Create config file
cd /var/www/hackspace-foundation-sites
cp etc/config.php.example etc/config.php

# Configure database
sqlite3 ./var/database.db < ./etc/schema.sql
cd var
echo "update users set subscribed=1; update users set admin=1;" | sqlite3 database.db
cd ..

# Configure php
sed -i~ "s/short_open_tag = Off/short_open_tag = On/g" /etc/php5/apache2/php.ini

# Configure apache
a2enmod rewrite
a2enmod expires
cp /var/www/hackspace-foundation-sites/apache-config-drop-in /etc/apache2/sites-available/000-default.conf
service apache2 restart

