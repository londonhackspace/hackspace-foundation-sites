<?
$title = 'Notice of Disposal';
require('./header.php');

ensureKioskUser();

# echo json_encode($_POST);

if (isset($_POST['print']) && $user->isMember()) {
    fRequest::validateCSRFToken($_POST['token']);
    $data = array(
        'id' => $user->getId(),
        'name' => $user->getFullName(),
        'email' => $user->getEmail()
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

<p>It will have:</p>

<form method="post" class="form-horizontal" role="form">
<input type="hidden" name="token" value="<?=fRequest::generateCSRFToken()?>" />
<div class="form-group">
    <label class="col-sm-3 control-label">Your name:</label><div class="col-sm-9"><p class="help-block"><?=htmlspecialchars($user->getFullName())?></p></div>
</div>
<div class="form-group">
    <label class="col-sm-3 control-label">Your email:</label><div class="col-sm-9"><p class="help-block"><?=htmlspecialchars($user->getEmail())?></p></div>
</div>
<div class="form-group">
    <label class="col-sm-3 control-label">The date the sticker was printed:</label>
    <div class="col-sm-9"><p class="help-block"><?=date('Y-m-d', strtotime("+2 weeks"))?></p></div>
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
 
