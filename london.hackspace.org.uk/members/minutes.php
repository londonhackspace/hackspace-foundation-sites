<? 
$page = 'minutes';
$title = "Meeting Minutes";
$desc = '';
require('../header.php');

if (!isset($user)) {
    fURL::redirect('/login.php?forward=/members/minutes.php');
}

if($user->isMember()) {

?>
<h2>Meeting Minutes</h2>

<p>Minutes from meetings.</p>
<p><b>Please do not share with non-members.</b></p>



<p><button onclick="toggleElement("080919-minutes")">08/09/19 Minutes</button></p>

<div id="080919-minutes">
<?=nl2br(file_get_contents('../../var/080919-minutes.txt')); ?>
</div>

<? } else { ?>
   <p>You must be a member to use this page.</p>
<? }

require('../footer.php'); ?>

<script type="text/javascript">
function toggleElement(name) {
  var x = document.getElementById(name);
  if (x.style.display === "none") {
    x.style.display = "block";
  } else {
    x.style.display = "none";
  }
}
</script>
</body>
</html>
