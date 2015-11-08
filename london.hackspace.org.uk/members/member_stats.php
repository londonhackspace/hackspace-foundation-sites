<?php
require_once( $_SERVER['DOCUMENT_ROOT'] . '/../lib/init.php');
$subscribers = $db->translatedQuery( 'SELECT COUNT(id) AS num FROM users WHERE subscribed=true' )->fetchRow();
$pending = $db->translatedQuery( 'SELECT COUNT(id) AS num FROM users WHERE subscribed=false' )->fetchRow();

print "subscribed:{$subscribers['num']} pending:{$pending['num']}";
