<?php
require_once( $_SERVER['DOCUMENT_ROOT'] . '/../lib/init.php');

if (!$user || !$user->isMember()) {
    header('HTTP/1.1 403 Forbidden');
    die();
}

if (!isset($_GET['id'])) {
    header('HTTP/1.1 400 Bad Request');
}

apache_setenv('PHP_AUTH', '1');

header('Content-Type: multipart/x-mixed-replace; boundary=--BoundaryString');
virtual('/cameras/' . intval($_GET['id']));

?>
