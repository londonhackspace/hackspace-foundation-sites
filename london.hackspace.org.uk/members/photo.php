<?php require_once( $_SERVER['DOCUMENT_ROOT'] . '/../lib/init.php');
ensureLogin();

if (!isset($_GET['name'])){
 send404('Photo not found');
}

header('Pragma: private');
header('Cache-Control: private, max-age=86400');
header('Expires: '. gmdate('r', time() + 86400));
header('Content-Type: image/png');

$size = '';
if(isset($_GET['size']))
	$size = filter_var($_GET['size'], FILTER_SANITIZE_STRING);

($size == 'sml') ? $size_name = '_sml' : $size_name = '';
($size == 'med') ? $size_name = '_med' : $size_name = '';

$filter_name = preg_replace("/[^0-9a-zA-Z_]/","",filter_var($_GET['name'], FILTER_SANITIZE_STRING));
$default = $_SERVER['DOCUMENT_ROOT'] . '/images/generic_avatar.png';
$path = $_SERVER['DOCUMENT_ROOT'] . '/../var/photos/' . $filter_name . $size_name . '.png';

$getfile = ($filter_name == null || !is_readable($path)) ? $default : $path;

if (isset($_SERVER['HTTP_IF_MODIFIED_SINCE']) && (strtotime($_SERVER['HTTP_IF_MODIFIED_SINCE']) == filemtime($getfile))) {
	header('Last-Modified: '.gmdate(DATE_RFC1123,filemtime($getfile)), true, 304);
	exit;
}
header('Last-Modified: '.gmdate(DATE_RFC1123,filemtime($getfile)));
readfile($getfile);
?>