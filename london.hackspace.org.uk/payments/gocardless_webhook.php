<?php
require_once( $_SERVER['DOCUMENT_ROOT'] . '/../lib/init.php');

$webhook = file_get_contents('php://input');
$webhook_array = json_decode($webhook, true);
$webhook_valid = GoCardless::validate_webhook($webhook_array['payload']);

if ($webhook_valid == TRUE) {
  header('HTTP/1.1 200 OK');
} else {
  header('HTTP/1.1 403 Invalid signature');
}
