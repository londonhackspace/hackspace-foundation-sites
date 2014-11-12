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

<h2>Print Membership Stickers</h2>
<div class="btn-group">
<a href="/kiosk/storage.php?cardid=<?=$cardid?>" class="btn btn-default">Storage Request</a>
<a href="/kiosk/box.php?cardid=<?=$cardid?>" class="btn btn-default">Member Box</a>
</div>

<h2>Other Stickers</h2>

<div class="btn-group">
<a href="/kiosk/fixme.php?cardid=<?=$cardid?>" class="btn btn-default">Fix Me</a>
<a href="/kiosk/hackme.php?cardid=<?=$cardid?>" class="btn btn-default">Hack Me</a>
<a href="/kiosk/nod.php?cardid=<?=$cardid?>" class="btn btn-default">Notice of Disposal</a>
</div>


<?require('./footer.php')?>
</body>
</html>
