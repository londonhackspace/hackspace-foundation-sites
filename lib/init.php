<?php
ob_start();
require_once('config.php');
require_once('user.php');
require_once('transaction.php');


fORMDatabase::attach(
    new fDatabase('sqlite', dirname(__FILE__) . '/../var/database.db')
);


fSession::setLength('1 day');

if ($uid = fSession::get('user')) {
    $user = new User($uid);
} else {
    $user = null;
}
