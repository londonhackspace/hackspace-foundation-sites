<?php
ob_start();
require_once('config.php');
require_once('user.php');


fORMDatabase::attach(
    new fDatabase('sqlite', '../var/database.db')
);

fSession::setLength('1 day');

if ($uid = fSession::get('user')) {
    $user = new User($uid);
}
