<?
$title = 'Membership Management';
require('./header.php');
$cards = fRecordSet::build('Card', array('uid=' => $_GET['cardid']));
if($cards->count() == 0) {
    fURL::redirect("/kiosk/addcard.php?cardid=" . $_GET['cardid']);
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

<a href="/kiosk/storage.php?cardid=<?=$_GET['cardid']?>" class="btn btn-success">Storage Requests</a>
<a href="/kiosk/box.php?cardid=<?=$_GET['cardid']?>" class="btn btn-success">Member Box</a>
<a href="/kiosk/" class="btn btn-default">Go back</a>
<?require('./footer.php')?>
</body>
</html>
