<?
$title = 'Hack Me Sticker';
require('./header.php');

ensureKioskUser();

# echo json_encode($_POST);

if (isset($_POST['print']) && $user->isMember()) {
    try {
        fRequest::validateCSRFToken($_POST['token']);

        $validator = new fValidation();
        $validator->addRequiredFields('more_info');

        $validator->validate();


        $data = array(
            'donor_id' => $user->getId(),
            'donor_name' => $user->getFull_Name(),
            'donor_email' => $user->getEmail(),
            'dispose_date' => date('Y-m-d', strtotime("+2 weeks")),
            'more_info' => $_POST['more_info']
        );
        $data_string = json_encode($data);

        $ch = curl_init('http://kiosk.london.hackspace.org.uk:12345/print/hackme');
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data_string);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
                'Content-Type: application/json',
                'Content-Length: ' . strlen($data_string)));
        $result = curl_exec($ch);
        curl_close($ch);
        echo("<p>Your sticker is being printed now.</p>");
    } catch (fValidationException $e) {
        $e->printMessage();
    }
}

?>

<? if($user->isMember()) { ?>

<p>If you are donating something to the space that anyone can work on use this sticker to document it.</p>

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
    <label class="col-sm-3 control-label">Disposal date</label>
    <div class="col-sm-9"><p class="help-block"><?=date('Y-m-d', strtotime("+2 weeks"))?></p></div>
</div>
<div class="form-group">
    <label for="description" class="col-sm-3 control-label">More Info</label>
    <div class="col-sm-9">
        <textarea id="more_info" name="more_info" class="form-control" placeholder="... and some info about it, e.g. make/model to google for, what it does" rows="3"></textarea>
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

<?require('./footer.php')?>
</body>
</html>
 
