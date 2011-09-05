<?php
ob_start();
require_once('config.php');
require_once('user.php');
require_once('transaction.php');
require_once('card.php');

$db = new fDatabase('sqlite', dirname(__FILE__) . '/../var/database.db');

fORMDatabase::attach($db);

fSession::setLength('30 minute, 1 week');

if ($uid = fSession::get('user')) {
    $user = new User($uid);
} else {
    $user = null;
}
