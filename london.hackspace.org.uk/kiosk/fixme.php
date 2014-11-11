<?
$title = 'Fix Me Sticker';
require('./header.php');
$cards = fRecordSet::build('Card', array('uid=' => $_GET['cardid']));
if($cards->count() == 0) {
    fURL::redirect("/kiosk/addcard.php?cardid=" . $_GET['cardid']);
}
$card = $cards->getRecord(0);
$user = new User($card->getUserId());
$user->load();

# echo json_encode($_POST);

if (isset($_POST['print']) && $user->isMember() && isset($_POST['name']) && isset($_POST['more_info'])) {
    fRequest::validateCSRFToken($_POST['token']);
    $data = array(
        'name' => $_POST['name'],
        'reporter_id' => $user->getId(),
        'reporter_name' => $user->getFull_Name(),
        'reporter_email' => $user->getEmail(),
        'more_info' => $_POST['more_info']
    );
    $data_string = json_encode($data);
#    echo($data_string);
    $ch = curl_init('http://kiosk.london.hackspace.org.uk:12345/print/fixme');
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
    curl_setopt($ch, CURLOPT_POSTFIELDS, $data_string);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, array(
                'Content-Type: application/json',
                'Content-Length: ' . strlen($data_string)));
#    $result = curl_exec($ch);
    curl_close($ch);
    echo("<p>Your sticker is being printed now.</p>");
}

?>

<? if($user->isMember()) { ?>

<p>On this page you can print a Fix Me label, use it to mark something that needs fixing.</p>

<p>It will have:</p>

<form method="post" class="form-horizontal" role="form">
<input type="hidden" name="token" value="<?=fRequest::generateCSRFToken()?>" />
<div class="form-group">
    <label class="col-sm-3 control-label">Your name:</label><div class="col-sm-9"><p class="help-block"><?=$user->getFull_Name()?></p></div>
</div>
<div class="form-group">
    <label class="col-sm-3 control-label">Your email:</label><div class="col-sm-9"><p class="help-block"><?=$user->getEmail()?></p></div>
</div>
<div class="form-group">
    <label for="name" class="col-sm-3 control-label">Name</label>
    <div class="col-sm-9">
        <input type="text" class="form-control" id="name" name="name" placeholder="Name of the broken thing" value="">
    </div>
</div>
<div class="form-group">
    <label for="description" class="col-sm-3 control-label">More Info</label>
    <div class="col-sm-9">
        <textarea id="more_info" name="more_info" class="form-control" placeholder="... and more info about what is broken" rows="3"></textarea>
    </div>
</div>
<input type=hidden name="print" value="<?=$user->getId()?>">
<div class="form-group">
    <div class="col-sm-offset-3 col-sm-9">
        <input type="submit" id="print" name="print" value="Print" class="btn btn-primary"/>
    </div>
</div>
</form>
<? } ?>

<a href="/kiosk/card.php?cardid=<?=$_GET['cardid']?>" class="btn btn-default">Go back</a>
<?require('./footer.php')?>
</body>
</html>
 