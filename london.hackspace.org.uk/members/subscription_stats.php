<?php
require_once( $_SERVER['DOCUMENT_ROOT'] . '/../lib/init.php');
$average = $db->translatedQuery( 'SELECT AVG(amount) AS num FROM transactions WHERE timestamp >= date("now", "-30 day")' )->fetchRow();

print "averageSubscription:{$average['num']}";
