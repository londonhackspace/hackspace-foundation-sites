<?php require_once( $_SERVER['DOCUMENT_ROOT'] . '/../lib/init.php');

header('Content-Type: image/png');
if (!isset($user)) {
    fURL::redirect('/login.php?forward=/members/photo.php');
}

$filter_name = filter_var($_GET['name'], FILTER_SANITIZE_STRING);
$default = $_SERVER['DOCUMENT_ROOT'] . '/images/generic_avatar.png';
$path = $_SERVER['DOCUMENT_ROOT'] . '/../var/photos/' . $filter_name;

($filter_name == null || !is_readable($path)) ? readfile($default) : readfile($path);
?>