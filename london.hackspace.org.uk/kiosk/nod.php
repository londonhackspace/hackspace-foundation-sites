<?
$title = 'Notice of Disposal';
require('./header.php');
$cards = fRecordSet::build('Card', array('uid=' => $_GET['cardid']));
if($cards->count() == 0) {
    fURL::redirect("/kiosk/addcard.php?cardid=" . $_GET['cardid']);
}
$card = $cards->getRecord(0);
$user = new User($card->getUserId());
$user->load();

# echo json_encode($_POST);

if (isset($_POST['print']) && $user->isMember()) {
    fRequest::validateCSRFToken($_POST['token']);
    $data = array(
        'reporter_id' => $user->getId()
    );
    $data_string = json_encode($data);
#    echo($data_string);
    $ch = curl_init('http://kiosk.london.hackspace.org.uk:12345/print/nod');
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
    curl_setopt($ch, CURLOPT_POSTFIELDS, $data_string);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, array(
                'Content-Type: application/json',
                'Content-Length: ' . strlen($data_string)));
    $result = curl_exec($ch);
    curl_close($ch);
    echo("<p>Your sticker is being printed now.</p>");
}

?>

<? if($user->isMember()) { ?>

<p>On this page you can print a Notice of Disposal sticker, use them with care.</p>

<form method="post" class="form-horizontal" role="form">
<input type="hidden" name="token" value="<?=fRequest::generateCSRFToken()?>" />
<p><input type="submit" id="print" name="print" value="Print" class="btn btn-primary"/></p>
</form>
<? } ?>
<?require('./footer.php')?>
</body>
</html>
 
