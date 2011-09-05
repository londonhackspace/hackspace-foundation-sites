<?php
ob_start();
require_once('config.php');
require_once('user.php');
require_once('transaction.php');

$db = new fDatabase('sqlite', dirname(__FILE__) . '/../var/database.db');

fORMDatabase::attach($db);

fSession::setLength('1 day');
fSession::setPath(dirname(__FILE__) . '/../var/session');

if ($uid = fSession::get('user')) {
    $user = new User($uid);
} else {
    $user = null;
}
