<?
require('./header.php');
$cards = fRecordSet::build('Card', array('uid=' => $_GET['cardid']));
if($cards->count() == 0) {
    //TODO: add card
    print "nonexistent card";
    exit;
}
$card = $cards->getRecord(0);
$user = new User($card->getUserId());
$user->load();

?>
<h2>Membership Management</h2>
<p>
Member Name: <?=$user->prepareFullName()?><br>
Member ID: <?=$user->getMemberNumber()?><br>
Card ID: <?=$card->prepareUid()?><br>
Subscribed: <?=$user->isMember()?"Yes":"No"?>
</p>


<a href="/kiosk/" class="btn btn-default">Go back</a>
<?require('./footer.php')?>
