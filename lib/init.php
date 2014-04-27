<?php
ob_start();
require_once('config.php');
require_once('user.php');
require_once('transaction.php');
require_once('card.php');
require_once('usersprofile.php');
require_once('learning.php');
require_once('alias.php');
require_once('interest.php');
require_once('calendar.php');

$db = new fDatabase('sqlite', dirname(__FILE__) . '/../var/database.db');

fORMDatabase::attach($db);

fSession::setLength('30 minutes', '10 weeks');
fSession::setPath(dirname(__FILE__) . '/../var/session');

if ($uid = fSession::get('user')) {
    $user = new User($uid);
} else {
    $user = null;
}

function ensureLogin() {
  global $user;
  if (!isset($user)) {
        fURL::redirect("/login.php?forward={$_SERVER['REQUEST_URI']}");
  }
}

function send404($message) {
  header('HTTP/1.1 404 File not found');
  echo $message;
  exit;
}
