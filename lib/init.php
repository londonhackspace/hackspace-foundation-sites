<?php
require_once('config.php');
require_once('user.php');


fORMDatabase::attach(
    new fDatabase('sqlite', '../var/database.db')
);
