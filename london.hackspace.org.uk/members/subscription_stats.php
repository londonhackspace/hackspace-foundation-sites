<?php
require_once( $_SERVER['DOCUMENT_ROOT'] . '/../lib/init.php');
$average = $db->translatedQuery( "SELECT AVG(amount) AS num FROM lhspayments_payment
                                  WHERE timestamp >= now() - INTERVAL '1 month'" )->fetchRow();

print "averageSubscription:{$average['num']}";
