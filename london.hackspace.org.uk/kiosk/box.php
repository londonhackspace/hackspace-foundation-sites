<?
$title = 'Member Box Sticker';
require('./header.php');

ensureKioskUser();

if (isset($_POST['print']) && $user->isMember()) {
    $data = array(
        'owner_id' => $user->getId(),
        'owner_name' => $user->getFullName(),
    );
    $data_string = json_encode($data);
    $ch = curl_init('http://kiosk.london.hackspace.org.uk:12345/print/box');
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

<p>On this page you can print a label for your box.</p>

<p>It will have:</p>

<form method="post">
<table class="table">
<tbody>
    <tr><th>Your membership ID:</th><td><?=$user->getId()?></td></tr>
    <tr><th>Your name:</th><td><?=htmlspecialchars($user->getFullName())?></td></tr>
    <tr><td colspan="2">... and a QR code with a link to your profile page.</td></tr>
    <tr><td><button name="print" value="<?=$user->getId()?>" class="btn btn-primary">Print Sticker</button></td></tr>
</tbody>
</table>
</form>
<? } ?>

<?require('./footer.php')?>
</body>
</html>
 
