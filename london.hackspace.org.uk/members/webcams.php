<?
$page = 'webcams';
$title = "Webcams";
$noindex = True;
$desc = '';
require('../header.php');

$cameras = array(
  3 => "Main Room",
  6 => "Classroom",
  5 => "Workshop",
  1 => "Back Gate"
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
