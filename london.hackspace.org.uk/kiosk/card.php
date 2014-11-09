<?
require('./header.php');
$cards = fRecordSet::build('Card', array('uid=' => $_GET['cardid']));
if($cards->count() == 0) {
    //TODO: add card
    exit;
}
$card = $cards[0];

?>
<h2>Membership Management</h2>
Card ID: <?=$_GET['cardid']?>


<a href="/kiosk/" class="btn btn-default">Go back</a>
<?require('./footer.php')?>
