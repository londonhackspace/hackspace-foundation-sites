<? 
$page = 'webcams';
$title = "Webcams";
$noindex = True;
$desc = '';
require('../header.php');

$live = False;

if (isset($user) && $user->isMember()) {
    $live = True;
}

if (!isset($_GET['camera'])) {
    $camera = 1;
} else {
    $camera = intval($_GET['camera']);
}

?>
<? if (!$live) { ?>
    <p><strong>You're not a member, so you're seeing a delayed snapshot of our webcams.</strong></p>
<? } ?>
<div>
<b>Switch Camera:</b>
<a href="/members/webcams.php?camera=1">Main</a> |
<a href="/members/webcams.php?camera=2">Quiet/Social</a> |
<a href="/members/webcams.php?camera=4">Workshop</a> |
<a href="/members/webcams.php?camera=3">Door</a>
</div>
<div style="padding-top:10px">
<? if ($live) { ?>
    <img src="camera.php?id=<?=$camera?>">
<? } else { ?>
    <img src="/cam_snapshots/<?=$camera?>.jpg">
<? } ?>
</div>
<? require('../footer.php'); ?>
