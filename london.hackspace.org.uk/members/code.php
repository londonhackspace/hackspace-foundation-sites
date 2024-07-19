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
<h2>Gate to side alley</h2>

<p>The gate to the alleyway at the left (West) of the building has a padlock which is often locked. Please lock it if you are the last out of the building.</p>
<p>Please treat this as confidential and do not pass it on to non-members or visitors, if in doubt please direct them to this page.</p>

<p>The code was last changed on <?php echo date('j F Y', filemtime( '../../var/gate-code.txt' )) ?>.</p>

<p><a onclick="document.getElementById('gate-code').style.display = 'block';" href='#gate-code'>Show padlock code</a></p>

<p id="gate-code" style="display:none;font-weight:bold;font-size:x-large;">
<?=nl2br(file_get_contents('../../var/gate-code.txt')); ?>
</p>

<? } else { ?>
   <p>You must be a member to use this page.</p>
<? }

require('../footer.php'); ?>
</body>
</html>
