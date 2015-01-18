<?php
require_once( $_SERVER['DOCUMENT_ROOT'] . '/../lib/init.php');

$host = 'http://[2001:8b0:856:1::31]';
$scale = 80;

if (!$user || !$user->isMember()) {
    header('HTTP/1.1 403 Forbidden');
    die();
}

if (!isset($_GET['id'])) {
    header('HTTP/1.1 400 Bad Request');
}

if (!isset($_GET['scale'])) {
    $scale = 100;
} else {
    $scale = intval($_GET['scale']);
}


header('Content-Type: image/jpeg');

$chan = intval($_GET['id']);

echo(file_get_contents("$host/cgi-bin/nph-zms?mode=single&scale=$scale&monitor=$chan&user=hackspace&pass=hackspace"));

?>
