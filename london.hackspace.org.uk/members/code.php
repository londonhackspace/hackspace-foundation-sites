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
<h2>Gate padlock</h2>

<p>The gate to the yard at the rear of the hackspace has a padlock with a code on. Please make sure you lock the gate behind you, and scramble the code on the padlock!.</p>

<p>Please treat this as confidential and do not pass it on to non-members or visitors, if in doubt please direct them to this page.</p>

<p><a onclick="document.getElementById('gate-code').style.display = 'block';" href='#gate-code'>Show padlock code</a></p>

<p id="gate-code" style="display:none;font-weight:bold;font-size:x-large;">
<?php echo file_get_contents( '../../var/gate-code.txt' ); ?>
</p>

<? } else { ?>
   <p>You must be a member to use this page.</p>
<? }

require('../footer.php'); ?>
