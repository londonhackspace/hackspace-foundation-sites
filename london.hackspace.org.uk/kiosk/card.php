<?
$title = 'Membership Management';
require('./header.php');
$cardid = strtoupper($_GET['cardid']);
$cards = fRecordSet::build('Card', array('uid=' => $cardid));
if($cards->count() == 0) {
    fURL::redirect("/kiosk/addcard.php?cardid=" . $cardid);
}
$card = $cards->getRecord(0);
$user = new User($card->getUserId());
$user->load();

?>
<p>
Member Name: <?=$user->prepareFullName()?><br>
Member ID: <?=$user->getMemberNumber()?><br>
Card ID: <?=$card->prepareUid()?><br>
Subscribed: <?=$user->isMember() ? "Yes":"No"?>
</p>

<h2>Stickers!:</h2>

<p><a href="/kiosk/storage.php?cardid=<?=$cardid?>" class="btn btn-success">Storage Requests</a></p>
<p><a href="/kiosk/box.php?cardid=<?=$cardid?>" class="btn btn-success">Member Box</a></p>
<p><a href="/kiosk/fixme.php?cardid=<?=$cardid?>" class="btn btn-success">Fix Me</a></p>
<p><a href="/kiosk/hackme.php?cardid=<?=$cardid?>" class="btn btn-success">Hack Me</a></p>
<p><a href="/kiosk/nod.php?cardid=<?=$cardid?>" class="btn btn-success">Notice of Disposal</a></p>
<p><a href="/kiosk/" class="btn btn-default">Go back</a></p>


<?require('./footer.php')?>
</body>
</html>
