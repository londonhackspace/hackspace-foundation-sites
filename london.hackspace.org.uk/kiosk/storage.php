<?
$title = 'Project Storage';
require('./header.php');
$cards = fRecordSet::build('Card', array('uid=' => $_GET['cardid']));
if($cards->count() == 0) {
    fURL::redirect("/kiosk/addcard.php?cardid=" . $_GET['cardid']);
}
$card = $cards->getRecord(0);
$user = new User($card->getUserId());
$user->load();

if (isset($_POST['print'])) {
    $project = new Project($_POST['print']);
    $project->load();
    if ($project->getUserId() != $user->getId()) {
        print "Incorrect project ID";
        exit;
    }
    $data = array(
        'storage_id' => $project->getId(),
        'name' => $project->getName(),
        'ownername' => $user->getFullName(),
        'more_info' => $project->getDescription(),
        'completion_date' => $project->getToDate()->format('Y/m/d'),
        'max_extention' => "14"
    );
    $data_string = json_encode($data);
    $ch = curl_init('http://kiosk.london.hackspace.org.uk:12345/print/dnh');
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

$projects = fRecordSet::build('Project', array('state_id!='=>array('6','7'), 'user_id=' => $user->getId()));
?>

<? if($projects->count() > 0) { ?>

<p>On this page you can print Do Not Hack stickers for your current storage projects.</p>

<h3>Your current projects</h3>
<form method="post">
<table class="table">
<tbody>
<? foreach($projects as $project) { ?>
    <tr>
    <th><?=$project->prepareName() ?></th>
    <td><button name="print" value="<?=$project->getId()?>" class="btn btn-primary">Print Sticker</button></td>
    </tr>
<? } ?>
</tbody>
</table>
</form>
<? } else { ?>

<p>You don't have any current storage requests. To create one, please log into the Hackspace web site.</p>

<? } ?>

<a href="/kiosk/" class="btn btn-default">Go back</a>
<?require('./footer.php')?>
</body>
</html>
