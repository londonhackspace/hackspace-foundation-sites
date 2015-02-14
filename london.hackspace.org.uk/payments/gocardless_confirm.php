<?php
require_once( $_SERVER['DOCUMENT_ROOT'] . '/../lib/init.php');

$confirm_params = array(
  'resource_id'    => $_GET['resource_id'],
  'resource_type'  => $_GET['resource_type'],
  'resource_uri'   => $_GET['resource_uri'],
  'signature'      => $_GET['signature']
);

if (isset($_GET['state'])) {
  $confirm_params['state'] = $_GET['state'];
}

print_r($confirm_params);

// TODO: catch GoCardless_SignatureException here
$subscription = GoCardless::confirm_resource($confirm_params);

print_r($subscription);

$sub = Subscription($confirm_params['state']);
$sub->setRemoteId($_GET['resource_id']);
$sub->setState('active');
$sub->store();

fURL::redirect('/members');
