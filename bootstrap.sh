#!/usr/bin/env bash
export DEBIAN_FRONTEND=noninteractive

# Install required packages
apt-get update
apt-get install -y php5 php5-curl php-apc git postgresql-9.4 php5-pgsql

su postgres -c 'createuser hackspace-web'
su postgres -c 'createdb -O hackspace-web hackspace'

cat > /etc/postgresql/9.4/main/pg_hba.conf <<EOF
local   hackspace       hackspace-web                           trust
local   all             postgres                                peer
local   all             all                                     peer
host    all             all             127.0.0.1/32            md5
host    all             all             ::1/128                 md5
EOF

service postgresql reload

psql -U hackspace-web hackspace < /var/www/hackspace-foundation-sites/etc/schema.sql

# Configure php
sed -i~ "s/short_open_tag = Off/short_open_tag = On/g" /etc/php5/apache2/php.ini
sed -i~ "s/display_errors = Off/display_errors = On/g" /etc/php5/apache2/php.ini
sed -i~ "s/display_startup_errors = Off/display_startup_errors = On/g" /etc/php5/apache2/php.ini

# Configure apache
a2enmod rewrite
a2enmod expires
cp /var/www/hackspace-foundation-sites/apache-config-drop-in /etc/apache2/sites-available/000-default.conf
service apache2 restart

