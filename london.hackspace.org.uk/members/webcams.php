<? 
$page = 'webcams';
$title = "Webcams";
$noindex = True;
$desc = '';
require('../header.php');

$live = False;

if (isset($user) && $user->isMember() && !isset($_GET['nostream'])) {
    $live = True;
}

if (!isset($_GET['camera'])) {
    $camera = 1;
} else {
    $camera = intval($_GET['camera']);
}

?>
<? if (!$live && !isset($_GET['nostream'])) { ?>
    <p><strong>You're not a member, so you're seeing a delayed snapshot of our webcams.</strong></p>
<? } ?>
<div>
    <b>Switch Camera:</b>
    <a href="/members/webcams.php?camera=1">Main</a> |
    <a href="/members/webcams.php?camera=5">Classroom</a> |
<!--    <a href="/members/webcams.php?camera=6">Quiet Room</a> | -->
    <a href="/members/webcams.php?camera=4">Workshop</a> <!-- |
    <a href="/members/webcams.php?camera=3">Door</a> -->
</div>

<div style="padding-top:10px">
<? if ($live) { ?>
    <img src="camera.php?id=<?=$camera?>" alt="Hackspace webcam feed" />
    <? if ($camera == 4 or $camera == 2) {
        if ($camera == 4) { $file = 'camcontrol2.php'; } else { $file = 'camcontrol.php'; }
        ?>
        <small>
            <a href="http://hack.rs/<?=$file?>?cmd=left" target="camcontrol">Left</a> |
            <a href="http://hack.rs/<?=$file?>?cmd=right" target="camcontrol">Right</a> |
            <a href="http://hack.rs/<?=$file?>?cmd=up" target="camcontrol">Up</a> |
            <a href="http://hack.rs/<?=$file?>?cmd=down" target="camcontrol">Down</a><br>
            <a href="http://hack.rs/<?=$file?>?cmd=panscan" target="camcontrol">Pan Scan</a> |
            <a href="http://hack.rs/<?=$file?>?cmd=tiltscan" target="camcontrol">Tilt Scan</a>
        </small>

        <iframe name='camcontrol' frameBorder="0" width="0" height="0"></iframe>
    <? } ?>
<? } else { ?>
    <img src="/cam_snapshots/<?=$camera?>.jpg" alt="Hackspace webcam feed" />
<? } ?>
</div>
<? require('../footer.php'); ?>
