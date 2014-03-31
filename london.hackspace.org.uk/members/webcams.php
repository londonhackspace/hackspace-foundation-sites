<?
$page = 'webcams';
$title = "Webcams";
$noindex = True;
$desc = '';
require('../header.php');

$cameras = array(
  19 => "Main Room",
  18 => "Classroom",
  17 => "Workshop",
  14 => "Front Door",
  13 => "Back Lobby",
  12 => "Back Door",
  10 => "Back Yard",
  9 => "Back Gate"
);

if (!isset($user) || !$user->isMember()) {
    fURL::redirect('/login.php?forward=/members/webcams.php');
}
?>
<div id="webcam-grid">
<?
foreach ($cameras as $id => $name) {

?>
<div class="webcam-image thumbnail with-caption">
  <a href="camera.php?id=<?=$id?>">
    <img src="camera.php?id=<?=$id?>&scale=50" alt="<?=$name?>">
    <p><?=$name?></p>
  </a>
</div>

<? } ?>
</div>
<? require('../footer.php'); ?>
