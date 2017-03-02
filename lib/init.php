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

require_once("$root/gocardless-php/lib/GoCardless.php");

$db = new fDatabase('postgresql', $DB_NAME, $DB_USER, $DB_PASSWORD);

fORMDatabase::attach($db);

fSession::setLength('30 minutes', '10 weeks');
fSession::setPath(dirname(__FILE__) . '/../var/session');

if (isset($GOCARDLESS_CREDENTIALS)) {
    GoCardless::set_account_details($GOCARDLESS_CREDENTIALS);
}

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

function ensureKioskUser() {
    global $user;
    global $cardid;

    if (!isset($_GET['cardid'])) {
        fURL::redirect("/kiosk/");
    }
    $cardid = sanitiseCardUID($_GET['cardid']);
    $cards = fRecordSet::build('Card', array('uid=' => $cardid));
    if ($cards->count() == 0) {
        # $cardid has not been validated at this point
        fURL::redirect("/kiosk/addcard.php?cardid=" . $cardid);
    }
    $card = $cards->getRecord(0);
    if (!$card->getActive()) {
        fURL::redirect("/kiosk/inactive.php");
    }
    $user = new User($card->getUserId());
    $user->load();
}

function sanitiseCardUID($uid) {
    $uid = strtoupper($uid);
    $uid = preg_replace('/[^0-9A-Z]+/', '', $uid);
    return $uid;
}

// Throw an exception if the card UID is invalid
function validateCardUIDUsable($uid) {
    if ($uid == '21222324' || $uid == '01020304') {
        /* Some Visa cards issued around 2013 return 21222324, presumably for privacy.
         * Android phones always return 01020304. */
        throw new fValidationException('Non-unique UID. This card cannot be added to the system.');
    }

    // Random IDs are 4 bytes long and start with 0x08
    // http://www.nxp.com/documents/application_note/AN10927.pdf
    if(strlen($uid) === 8 && substr($uid,0,2) === "08") {
        throw new fValidationException('ID is randomly generated and will change every time the card is used!');
    }

    if(strlen($uid) === 8 && substr($uid,0,2) === "88") {
        throw new fValidationException('Card UID\'s can\'t start with 88');
    }

    if(strlen($uid) > 8 && substr($uid,6,8) === "88") {
        throw new fValidationException('Can\'t have cards with long uid\'s and UID3 == 88');
    }
}
