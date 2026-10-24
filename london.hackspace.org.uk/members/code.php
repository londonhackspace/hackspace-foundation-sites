<?php 
$page = 'code';
$title = "Code access";
$desc = '';
require('../header.php');

if (!isset($user)) {
    fURL::redirect('/login.php?forward=/members/code.php');
}

if($user->isMember()) {

?>
<style>
.gate_codes {
    display: flex;
    flex-flow: row wrap;
    font-weight:bold;

    background-color: LightBlue;
}

.gate_codes > div {
    margin-left: 1em;
    font-size:x-large;
}

h4 {
    font-weight:bold;
}

</style>
<?php  
    $code_file = file_get_contents('../../var/gate-code.json');
    $gate_codes = json_decode($code_file, true);
?>

<div class="gate_codes">
    <p>Please treat these codes as confidential and do not pass them on to non-members or visitors, If in doubt please direct them to this page.</p>
<div>
Main Padlock :
<?=$gate_codes['main']?>
</div>
<div>
Pedestrian Keypad : 
<?=$gate_codes['side']?>
</div>
</div>

<h2>Building Access and Lockup Policy</h2>

<p>The benefits of 24/7 access come with some responsibilities. For the security of both the Hackspace and our neighbours in EX2 it is essential that the building lockup policy is followed at all times.
</p>

<h3>Whenever you leave the building</h3>
<p>Ask yourself if you are the last person in, if you are not 100% sure there is someone else still in the building, please lock up.</p>

<h3>In the Evenings</h3>
<h4>From 6pm</h4>
The shutters and fire exit doors must be closed from 6pm onwards. 
If you are in the building at this time then please check this has been done, 
and do it yourself if it has not. 
<h4>From 8:30pm</h4>
A security patrol will close the main gate around 8:30pm. 
If you wish to bring a vehicle into the alleyway after this time, then you can unlock the gate,
but then you must lock it again after you have passed through. 
If you notice the gate has been left open, please close and lock it.
<h2>Lockup Procedure</h2>
<h4>The procedure is actually quite simple, you simply start at the front of the building and work back to the Hackspace. It is important to check all the entrances.</h4>
<h3>Front door and foyer</h3>
<h4>1. If the front shutter is up (i.e. you can see the street), then close it.</h4>
<p>
This requires a single press of the down button situated on the side of the control box to the right of the door. The down button is the one shaped like an arrow pointing down, you will be able to feel this with your fingertips.
</p>
<h4>2. Close the foyer door and ensure it is not on the latch.</h4>
<p>This is essential to prevent anyone who gains access to the upstairs unit from being able to get into the rest of the building</p>
<h3>Fire Exit Door</h3>
<h4>As you walk past the Fire Exit near the kitchen, look to see if it is closed, close it if not</h4>
<p>The fire exit is also the entrance for a number of tenants, it may have been left ajar or even blocked open on occasion</p>
<h3>Big Blue Shutter</h3>
<h4>If it is up, then close it. Turn the key in the box on the right hand side of the shutter to lower it.</h4>
<p>This is the shutter on the large double doors near the Hackspace internal door</p>
<h3>How do I get out?</h3>
<p>With everything locked up, you can either exits through the Hackspace itself, 
    or if you have a bicycle you may find it easier to use the fire exit near the kitchen.
    Please turn out the hackspace lights if you are the last to leave.
    <b>Make sure the door is closed properly behind you</b>
<p></p>
<h3>Gate</h3>
<h4>If there is nobody left in the building, or it is after 8:30pm, then ensure the gate is locked.</h4>
<p>This is done with the padlock and chain,     
    the padlock is opened with the code above.
<?php } else { ?>
   <p>You must be a member to use this page.</p>
<?php }

require('../footer.php'); ?>
</body>
</html>
