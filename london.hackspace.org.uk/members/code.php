<? 
$page = 'code';
$title = "Code access";
$desc = '';
require('../header.php');

if (!isset($user)) {
    fURL::redirect('/login.php?forward=/members/code.php');
}

if($user->isMember()) {

?>
<h2>Night access</h2>

<p>Access to Cremer Business Centre between 19:00 and 07:00 is restricted by keycode access, the key you need is listed below.</p>

<p>Please treat this as confidential and do not pass it on to non-members or visitors, if in doubt please direct them to this page.</p>

<p><a onclick="document.getElementById('night-code').style.display = 'block';" href='#night-code'>Show access code</a></p>

<p id="night-code" style="display:none;font-weight:bold;font-size:x-large;">
<?php echo file_get_contents( '../../var/night-code.txt' ); ?>
</p>

<h2>Gate padlock</h2>

<p>The new space's gate has a padlock with a code on. After dialing in the code you need to press the button on the bottom of the padlock. The gate should be locked behind you.</p>

<p>Please treat this as confidential and do not pass it on to non-members or visitors, if in doubt please direct them to this page.</p>

<p><a onclick="document.getElementById('gate-code').style.display = 'block';" href='#gate-code'>Show padlock code</a></p>

<p id="gate-code" style="display:none;font-weight:bold;font-size:x-large;">
<?php echo file_get_contents( '../../var/gate-code.txt' ); ?>
</p>

<? } else { ?>
   <p>You must be a member to use this page.</p>
<? }

require('../footer.php'); ?>
