<?php
require_once( $_SERVER['DOCUMENT_ROOT'] . '/../lib/init.php');
ensureLogin();

if (!fRequest::isPost()) {
    fURL::redirect('/');
}

fRequest::validateCSRFToken(fRequest::get('request_token'));

$amount = fRequest::get('paymentAmount');

if ($amount < 5 or $amount > 100) {
    // TODO: error handling
    fURL::redirect('/');
}

$subscription = new Subscription();
$subscription->setCreated(new fDate());
$subscription->setProvider('GoCardless');
$subscription->setState('inactive');
$subscription->setAmount($amount);
$subscription->store();

$subscription_details = array(
  'amount'           => $amount,
  'interval_length'  => 1,
  'interval_unit'    => 'month',
  'name'             => 'London Hackspace Membership',
  'description'      => 'Monthly membership fee',
  'redirect_uri'     => fURL::getDomain() . '/payments/gocardless_confirm.php',
  'cancel_uri'       => fURL::getDomain() . '/payments/gocardless_cancel.php',
  'state'            => $subscription->getId()
);

$url = GoCardless::new_subscription_url($subscription_details);
fURL::redirect($url);
