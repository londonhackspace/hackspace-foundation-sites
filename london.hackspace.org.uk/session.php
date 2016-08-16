<?php
/* A temporary bridge (in the style of Eitaibashi) to allow for migration to Python */

$root = $_SERVER['DOCUMENT_ROOT'] . '../';

// Load flourish
require_once($root . 'lib/config.php');

fSession::setLength('30 minutes', '10 weeks');
fSession::setPath($root . 'var/session');

$uid = fSession::get('user');
//die(var_dump($_SESSION));

if (isset($uid)) {
  $data = array(
      'user_id' => (int) $uid,
      'type' => fSession::get('fSession::type'),  // normal or persistent
      'expires' => fSession::get('fSession::expires'),
      'suppress_profile_notification' => fSession::get('suppress_profile_notification'),
  );
  die(json_encode($data));
} else {
  die('{}');
}
