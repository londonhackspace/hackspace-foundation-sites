<?php require_once( $_SERVER['DOCUMENT_ROOT'] . '/../lib/init.php');
ensureLogin();

if (!isset($_GET['name'])){
 send404('Photo not found');
}

header('Content-Type: image/png');
$size = '';
if(isset($_GET['size']))
	$size = filter_var($_GET['size'], FILTER_SANITIZE_STRING);
($size == 'sml') ? $size_name = '_sml' : $size_name = '';

$filter_name = preg_replace("/[^0-9a-zA-Z_]/","",filter_var($_GET['name'], FILTER_SANITIZE_STRING));
$default = $_SERVER['DOCUMENT_ROOT'] . '/images/generic_avatar.png';
$path = $_SERVER['DOCUMENT_ROOT'] . '/../var/photos/' . $filter_name . $size_name . '.png';

($filter_name == null || !is_readable($path)) ? readfile($default) : readfile($path);
?>
