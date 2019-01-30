<?
$page = 'webcams';
$title = "Webcams";
$noindex = True;
$desc = '';
require('../header.php');

$cameras = array(
  5 => "Kitchen",
  4 => "Classroom",
  3 => "Open Space",
  15 => "Metal Workshop",
  17 => "Wood Workshop",
  14 => "Middle Workshop"
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
</body>
</html>
