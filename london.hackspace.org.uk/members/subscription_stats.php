<?php
require_once( $_SERVER['DOCUMENT_ROOT'] . '/../lib/init.php');
$average = $db->translatedQuery( "SELECT AVG(amount) AS num FROM transactions
                                  WHERE timestamp >= now() - INTERVAL '1 month'" )->fetchRow();

print "averageSubscription:{$average['num']}";
