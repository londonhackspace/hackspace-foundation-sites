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
<table class="table">
<tr><th>Member Name</th><td><?=$user->prepareFullName()?></td></tr>
<tr><th>Member ID</th><td> <?=$user->getMemberNumber()?></td></tr>
<tr><th>Card ID</th><td> <?=$card->prepareUid()?></td></tr>
<tr><th>Subscribed</th><td> <?=$user->isMember() ? "Yes":"No"?></td></tr>
</table>

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
