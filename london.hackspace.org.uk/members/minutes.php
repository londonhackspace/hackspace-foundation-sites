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



<p><a onclick="document.getElementById('080919-minutes').style.display = 'block';" href='#080919-minutes'>08/09/19 Minutes</a></p>

<p id="080919-minutes" style="display:none;font-weight:bold;font-size:x-large;">
<?=nl2br(file_get_contents('../../var/080919-minutes.txt')); ?>
</p>

<? } else { ?>
   <p>You must be a member to use this page.</p>
<? }

require('../footer.php'); ?>
</body>
</html>
