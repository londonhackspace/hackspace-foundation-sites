<?
$title = 'Membership Kiosk';
require('./header.php');
?>
<div class="well">You can use this kiosk to register a new RFID card to access the space. Simply
                  swipe the card you want to add on the reader below, and follow the instructions.
</div>

<p>Coming soon: the ability to print Do Not Hack stickers and members box labels!</p>

<p><small>If you have any problems with this kiosk, contact Russ or Jasper.</small></p>

<form action="card.php" style="position:absolute; left:-9999px;">
<input type="text" name="cardid" id="cardid" accesskey="i"/>
</form>
<? require('./footer.php'); ?>
</body>
</html>
