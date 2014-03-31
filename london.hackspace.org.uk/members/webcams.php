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
<div style="float: left; padding-left:20px; padding-right:20px;">
<?
foreach ($cameras as $id => $name) {

?>
<div style="float:left; height:320px; width:362px; margin: 1px; border: 1px #ccc solid;">
  <a href="camera.php?id=<?=$id?>">
    <img src="camera.php?id=<?=$id?>&scale=50" style="display:block; margin: 0 auto;" alt="<?=$name?>">
    <p style="text-align: center"><?=$name?></p>
  </a>
</div>

<? } ?>
</div>
<div style="clear:both"></div>
<? require('../footer.php'); ?>
