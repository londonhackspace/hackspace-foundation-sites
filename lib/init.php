<?php
ob_start();
$root = dirname(__FILE__);

require_once("$root/../etc/config.php");
require_once("$root/config.php");
require_once("$root/user.php");
require_once("$root/transaction.php");
require_once("$root/card.php");
require_once("$root/usersprofile.php");
require_once("$root/learning.php");
require_once("$root/alias.php");
require_once("$root/interest.php");
require_once("$root/calendar.php");
require_once("$root/project.php");

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

function ensureMember() {
    global $user;
    ensureLogin();
    if (!$user->isMember()) {
        echo "<p>Only subscribed members may access this area.</p>";
        exit;
   }
}

function send404($message) {
  header('HTTP/1.1 404 File not found');
  echo $message;
  exit;
}
