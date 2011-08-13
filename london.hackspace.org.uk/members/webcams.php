<? 
$page = 'webcams';
$title = "Webcams";
$desc = '';
require('../header.php');

if (!isset($user)) {
    fURL::redirect('/login.php?forward=/members/webcams.php');
}

if (!$user->isMember) {
    header("HTTP/1.1 403 Not Authorized");
    die();
}

if (!isset($_GET['camera'])) {
    $camera = 1;
} else {
    $camera = intval($_GET['camera']);
}

?>
<div>
<b>Switch Camera:</b>
<a href="/members/webcams.php?camera=1">Main</a> |
<a href="/members/webcams.php?camera=2">Quiet/Social</a> |
<a href="/members/webcams.php?camera=4">Workshop</a> |
<a href="/members/webcams.php?camera=3">Door</a>
</div>
<div>
<img src="camera.php?id=<?=$camera?>">
</div>
<? require('../footer.php'); ?>
