#!/usr/bin/env bash

DB=postgres://hackspace:hackspace@db/hackspace
check='select count(*) from project_states'

until psql "$DB" -c '\q'; do
  >&2 echo "Postgres is unavailable - sleeping"
  sleep 0.5
done

if ! psql -tA $DB -c "$check" >/dev/null; then
  echo "Check query failed, initialising DB"

  psql $DB < etc/create-flourish-tables.sql

  python manage.py migrate sites
  python manage.py migrate
  python manage.py loaddata main/fixtures/*

  psql $DB < etc/restore-column-defaults.sql
  psql $DB < etc/restore-multicolumn-pks.sql
fi

python manage.py runserver 0.0.0.0:9001

