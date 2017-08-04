#!/bin/bash

dir=$(dirname $(readlink -f "$0"))
cd "$dir"
env/bin/python manage.py "$@"

