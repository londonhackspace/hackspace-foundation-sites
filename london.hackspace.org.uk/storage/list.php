<?php
$page = 'storagelist';
$title = "Storage list";
require( '../header.php' );

ensureMember();

$projects = fRecordSet::build('Project',array('state_id!='=>array('6','7')), array('location_id' => 'asc', 'name' => 'asc'));
?>

<h2>Storage Requests</h2>
<a href="/storage/edit.php" class="btn btn-primary"><span class="glyphicon glyphicon-add"></span> Start a storage request</a>
<br/><br/>

<?php 
    $loc = ''; 
    foreach($projects as $project) {
        if($project->getLocationId() != $loc) {
            $loc = $project->getLocationId();
            $location = new Location($project->getLocationId());
            echo "<h3>".$location->getName()."</h3>";
        }
        ?>
        <div class="status list small <?= strtolower($project->getState()); ?>"><?= $project->getState(); ?> <?php if($project->getState() == 'Extended') { ?>(<?=$project->getExtensionDuration()?> days)<?php } ?></div>
        <a href="/storage/<?=$project->getId()?>"><?=htmlspecialchars($project->getName())?></a><br/>
        <?php
    }

$projects = fRecordSet::build('Project',array('state_id='=>array('6','7')), array('state_id' => 'asc', 'name' => 'asc')); ?>

<br/><br/>
<h3>Old requests</h3>

<?php foreach($projects as $project) { ?>
        <div class="status list small <?= strtolower($project->getState()); ?>"><?= $project->getState(); ?></div>
        <a href="/storage/<?=$project->getId()?>"><?=htmlspecialchars($project->getName())?></a><br/>
<?php }

require('../footer.php'); ?>
