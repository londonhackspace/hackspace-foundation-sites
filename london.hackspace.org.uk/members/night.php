<? 
$page = 'night';
$title = "Night access";
$desc = '';
require('../header.php');

if (!isset($user)) {
    fURL::redirect('/login.php?forward=/members/night.php');
}

?>
<h2>Night access</h2>

<p>Access to Cremer Business Centre between 19:00 and 07:00 will soon be restricted by keycode access, the key you need is listed below.</p>

<p>Please treat this as confidential and do not pass it on to non-members or visitors, if in doubt please direct them to this page.</p>

<p><a onclick="document.getElementById('access-code').style.display = 'block';">Show access code</a></p>

<p id="access-code" style="display:none;font-weight:bold;font-size:x-large;">
<?php echo file_get_contents( '../../var/code.txt' ); ?>
</p>
<? require('../footer.php'); ?>
