<?
$page = 'storagelist';
$title = "Storage list";
require( '../header.php' );

if (!isset($user)) {
    fURL::redirect('/login.php?forward=/storage/list.php');
}

$projects = fRecordSet::build('Project',array('state!='=>'Removed'), array('location_id' => 'asc', 'name' => 'asc'));
?>

<h2>Storage Requests</h2>
<a href="/storage/edit.php" class="btn btn-primary"><span class="glyphicon glyphicon-add"></span> Start a storage request</a>
<br/><br/>

<? 
    $loc = ''; 
    foreach($projects as $project) {
        if($project->getLocationId() != $loc) {
            $loc = $project->getLocationId();
            echo "<h3>$loc</h3>";
        }
        ?>
        <div class="status small <?= strtolower($project->getState()); ?>"><?= $project->getState(); ?></div>
        <a href="/storage/<?=$project->getId()?>"><?=$project->getName()?></a><br/>
        <?
    } ?>

<? require('../footer.php'); ?>