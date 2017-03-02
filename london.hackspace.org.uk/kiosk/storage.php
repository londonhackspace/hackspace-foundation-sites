<?
$title = 'Project Storage';
require('./header.php');

ensureKioskUser();

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
        'more_info' => html_entity_decode($project->getDescription(), ENT_QUOTES),
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

<p>You can print Do Not Hack stickers for your current storage projects.</p>

<h2>Your current projects</h2>
<form method="post">
<table class="table table-striped table-bordered">
<thead>
    <tr><th>Project</th><th>State</th><th>Print</th>
</thead>
<tbody>
<? foreach($projects as $project) { ?>
    <tr>
    <th><?=$project->prepareName() ?></th>
    <td><?=$project->getState()?></td>
    <td><? if ($project->getStateId() == 2 or $project->getStateId() == 4) {?>
            <button name="print" value="<?=$project->getId()?>" class="btn btn-primary">
                 <span class="glyphicon glyphicon-print"></span>
                Print
            </button>
        <? } ?>
    </td>
    </tr>
<? } ?>
</tbody>
</table>
</form>

<p>You can only print stickers for projects which have been approved.</p>

<? } else { ?>

<p>You don't have any current storage requests. To create one, please log into the Hackspace web site.</p>

<? } ?>

<?require('./footer.php')?>
</body>
</html>
