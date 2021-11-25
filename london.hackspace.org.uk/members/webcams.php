<?
$page = 'webcams';
$title = "Webcams";
$noindex = True;
$desc = '';
require('../header.php');

$cameras = array(
  20 => "3D Printing",
  18 => "Middle Workshop SE",
  15 => "Metal Workshop SW",
  17 => "Wood Workshop",
  14 => "Middle Workshop N",
  7 => "Wood Workshop NE",
  17 => "Wood Workshop SW",
  22 => "Wood Workshop E",
  21 => "Wood Workshop E2",
  24 => "Electronics",
  19 => "Cage",
  9 => "Metal Workshop NE",
  26 => "Biolab"
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
