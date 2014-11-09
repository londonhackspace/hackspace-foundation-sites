<?
require('./header.php');
?>
<h2>London Hackspace Membership Kiosk</h2>
<p>This kiosk will soon allow you to add your RFID card, print Do Not Hack labels, and do other useful things. We're still working on it - if you're interested talk to Russ or Jasper.</p>

<form action="card.php">
<input type="text" name="cardid" id="cardid" accesskey="i"/>
</form>
<? require('./footer.php'); ?>
