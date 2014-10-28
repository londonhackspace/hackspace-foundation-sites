<?
$page = 'storagedetails_edit';
$title = "Storage details";
require( '../header.php' );

$project = new Project(filter_var($_GET['id'], FILTER_SANITIZE_STRING));
if (!isset($user))
	fURL::redirect("/login.php?forward=/storage/{$project->getId()}");

$projectslogs = fRecordSet::build('ProjectsLog',array('project_id=' => $project->getId()), array('id' => 'asc'));
$states = fRecordSet::build('ProjectState',array(), array('sort' => 'asc'));
$projectUser = new User($project->getUserId());

if (isset($_POST['remove']) && ($user->getId() == $project->getUserId())) {
	try {
		fRequest::validateCSRFToken($_POST['token']);
		if($project->canTransitionStates($project->getState(),'Removed')) {
			$project->setState('Removed');
			$project->store();

			// log the update
			$project->submitLog($logmsg,$user->getId());
		}
		fURL::redirect("/storage/list.php");
	} catch (fValidationException $e) {
		echo $e->printMessage();
	} catch (fSQLException $e) {
		echo '<div class="alert alert-danger">An unexpected error occurred, please try again later</div>';
	}
}

if (isset($_POST['submit']) && ($user->getId() != $project->getUserId() || $user->isAdmin())) {
	try {
		fRequest::validateCSRFToken($_POST['token']);

		if(!isset($_POST['state']) || $_POST['state'] == '')
			throw new fValidationException('Status field is required.');

		$newStatus = filter_var($_POST['state'], FILTER_SANITIZE_STRING);
		if($newStatus != $project->getState() && $project->canTransitionStates($project->getState(),$newStatus)) {
			$project->setState($newStatus);
			$project->store();

			// log the update
			$project->submitLog('Status changed to ' . $project->getState() , $user->getId());

			// send to mailing list
			if($project->getState() != 'Archived')
				$project->submitMailingList('Status changed to ' . $project->getState() . " by " . htmlspecialchars($user->getFullName()));
		}

		fURL::redirect("/storage/list.php");
	} catch (fValidationException $e) {
		echo $e->printMessage();
	} catch (fSQLException $e) {
		echo '<div class="alert alert-danger">An unexpected error occurred, please try again later</div>';
	}
}
?>

<? if($user->getId() == $project->getUserId() && ($project->getState() == 'Pending Approval' || $project->getState() == 'Unapproved')) { ?>
	<small class="edit_bttn">
	<a href="/storage/edit/<?=$project->getId()?>" class="btn btn-default">Edit request</a>
	</small>
<? } if($user->getId() == $project->getUserId() && ($project->getState() == 'Approved' || $project->getState() == 'Passed Deadline' || $project->getState() == 'Extended')) { ?>
	<small class="edit_bttn">
	<form class="form-inline" role="form" method="post" style="display: inline;">
		<input type="hidden" name="token" value="<?=fRequest::generateCSRFToken()?>" />
		<input type="submit" name="remove" class="btn btn-default" value="Mark as removed from the space"/>
	</form>
	</small>
<? } ?>
<h2>Storage Request</h2>
<h3><?=$project->getName(); ?>
	<div class="status <?= strtolower($project->getState()); ?>"><?= $project->getState(); ?></div>
<p><small>
	<?=$project->outputDates(); ?>
	by <a href="/members/member.php?id=<?=$project->getUserId()?>"><?=htmlspecialchars($projectUser->getFullName())?></a><br/>
	<?=$project->outputDuration(); ?>
	<?=$project->outputLocation(); ?>
</small></p>
</h3>
<? if($project->recentPost() && $user->getId() == $project->getUserId()) { ?>
	<div class="alert alert-success storage-request-notice">
		<p>Now you've made a storage request don't forget:</p>
		<div><? if($project->getLocationId() != 'Yard') { ?><a target="_blank" href="/storage/print/<?=$project->getId()?>" class="btn btn-success">Print DO NOT HACK label</a> and attach it to your project. This is to let other members know your project is accounted for. <? } ?></div>
		<div><a target="_blank" href="https://groups.google.com/forum/#!topicsearchin/london-hack-space-test/subject$3AStorage$20AND$20subject$3ARequest$20AND$20subject$3A$23<?=$project->getId()?>" class="btn btn-success">Read the mailing list topic</a> this is where other members <? if(!$project->isShortTerm()) { ?> can choose to expediate your request (if its urgent) or unapprove it.<? } else { ?> can raise any concerns they have with your request.<? } ?></div>
		<? if($project->getState() == 'Pending Approval') { ?><p>Your request will be automatically approved after <?=$project->automaticApprovalDuration();?> days if you don't make any changes and no one replies on the mailing list.</p><? } ?>
	</div>
<? } else { ?>
	<a target="_blank" href="/storage/print/<?=$project->getId()?>" class="btn btn-success">Print DO NOT HACK label</a>
	<a target="_blank" href="https://groups.google.com/forum/#!topicsearchin/london-hack-space-test/subject$3AStorage$20AND$20subject$3ARequest$20AND$20subject$3A$23<?=$project->getId()?>" class="btn btn-success">Read the mailing list topic</a><br/>
<? } ?>
<br/>
<p><?=nl2br(stripslashes($project->getDescription())); ?></p>
<br/>
<hr/>
<strong>Activity log</strong><br/><br/>
<ul>
<? foreach($projectslogs as $log) {
	$userURL = '';
	if($log->getUserId() != null) {
		$logUser = new User($log->getUserId());
		$userURL = ' by <a href="/members/member.php?id='.$log->getUserId().'">'.htmlspecialchars($logUser->getFullName()).'</a>';
	}
	echo '<li><span class="light-color">'.date('g:ia jS M',$log->getTimestamp()).'</span> | '.str_replace('Mailing List','<a target="_blank" href="https://groups.google.com/forum/#!topicsearchin/london-hack-space-test/subject$3AStorage$20AND$20subject$3ARequest$20AND$20subject$3A$23'.$project->getId().'">Mailing List</a>',$log->getDetails()).$userURL.'</li>';
} ?>
</ul>
<? if($user->getId() != $project->getUserId() || $user->isAdmin()) { ?>
<hr/>
<form class="form-inline" role="form" method="post">
	<strong>Update Status</strong><br/>
	<p><small>Status changes are notified to the mailing list.</small></p>
	<input type="hidden" name="token" value="<?=fRequest::generateCSRFToken()?>" />
	<select class="form-control" name="state">
		<option value="" disabled selected></option>
		<? foreach($states as $state) {
			$newStatus = $state->getName();
			if($newStatus != $project->getState() && $project->canTransitionStates($project->getState(),$newStatus)) {
				echo '<option value="'.$state->getName().'" ';
				if($project->getState() == $state->getName())
					echo 'selected'; 
				echo '>'.$state->getName().'</option>';
			}
		} ?>
	</select>
	<input type="submit" name="submit" value="Update status" class="btn btn-primary"/>
</form>
<? }
 require('../footer.php'); ?>