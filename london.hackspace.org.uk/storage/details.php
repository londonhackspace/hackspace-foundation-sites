<?
$page = 'storagedetails_edit';
$title = "Storage details";
require( '../header.php' );

ensureMember();

$project = new Project(filter_var($_GET['id'], FILTER_SANITIZE_NUMBER_INT));
$projectslogs = fRecordSet::build('ProjectsLog',array('project_id=' => $project->getId()), array('id' => 'asc'));
$states = fRecordSet::build('ProjectState',array(), array('id' => 'asc'));
$projectUser = new User($project->getUserId());

// has the project owner set the status to removed or extended?
if (isset($_POST['remove']) || isset($_POST['extend']) && ($user->getId() == $project->getUserId())) {
	try {
		fRequest::validateCSRFToken($_POST['token']);
		if(isset($_POST['remove']) && $project->canTransitionStates($project->getState(),'Removed'))
			$project->setState('Removed');
		else if(isset($_POST['extend']) && $project->canTransitionStates($project->getState(),'Extended'))
			$project->setState('Extended');

		$project->store();
		$project->submitLog('Status changed to ' . $project->getState(),$user->getId());
		fURL::redirect("/storage/{$project->getId()}");
	} catch (fValidationException $e) {
		echo $e->printMessage();
	} catch (fSQLException $e) {
		echo '<div class="alert alert-danger">An unexpected error occurred, please try again later</div>';
	}
}

// has another member updated the status?
if (isset($_POST['submit']) && ($user->getId() != $project->getUserId() || $user->isAdmin())) {
	try {
		fRequest::validateCSRFToken($_POST['token']);

		if(!isset($_POST['state']) || $_POST['state'] == '')
			throw new fValidationException('Status field is required.');

		$newStatus = filter_var($_POST['state'], FILTER_SANITIZE_STRING, FILTER_FLAG_NO_ENCODE_QUOTES);
		$reason = filter_var($_POST['reason'], FILTER_SANITIZE_STRING, FILTER_FLAG_NO_ENCODE_QUOTES);

		if($newStatus != $project->getState() && $project->canTransitionStates($project->getState(),$newStatus)) {
			$project->setState($newStatus);
			$project->store();
			if($reason != '') {
				$reason = ' with the reason \''.$reason."'";
			}

			// log the update
			$project->submitLog('Status changed to ' . $project->getState() . $reason, $user->getId());

			if($project->getState() != 'Archived') {
				// send to mailing list
				$project->submitMailingList('Status changed to ' . $project->getState() . $reason . " by " . htmlspecialchars($user->getFullName()));

				// inform the owner
				$project->submitEmailToOwner(
					"Dear " . htmlspecialchars($projectUser->getFullName()) . ",<br/><br/>".
					"This is an automatic email to let you know your project " . htmlspecialchars($project->getName()) . " has been updated with status {$project->getState()}{$reason}.<br/><br/>".
					"If you have any questions or concerns regarding this change you can discuss this with members on the <a href=\"{$project->getMailingListURL()}\">Mailing List</a>.<br/><br/>".
					"Best,<br/>Monkeys in the machine"
				);
			}

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
		<? if(!$project->hasExtension()) { ?>
		<input type="submit" name="extend" class="btn btn-default" value="Extend the deadline"/>
		<? } ?>
	</form>
	</small>
<? } ?>
<h2>Storage Request</h2>
<h3><?=htmlspecialchars($project->getName()); ?>
	<div class="status <?= strtolower($project->getState()); ?>"><?= $project->getState(); ?> <?if($project->getState() == 'Extended') { ?>(<?=$project->getExtensionDuration()?> days)<? } ?></div>
<p><small>
	By <a href="/members/member.php?id=<?=$project->getUserId()?>"><?=htmlspecialchars($projectUser->getFullName())?></a> (incase of emergency contact <? if($project->getContact()) { ?><a href="mailto:<?=$project->getContact()?>"><span class="glyphicon glyphicon-envelope" style="margin-left:5px;margin-right:4px;" aria-hidden="true"></span><?=$project->getContact()?></a><? } else { ?><a href="mailto:<?=$projectUser->getEmail()?>"><span class="glyphicon glyphicon-envelope" style="margin-left:5px;margin-right:4px;" aria-hidden="true"></span><?=$projectUser->getEmail()?></a><? } ?>)<br/>
	<?=$project->outputDates(); ?><br/>
	<?=$project->outputDuration(); ?>
	<?=$project->outputLocation(); ?>
</small></p>
</h3>
<? if($project->recentPost() && $user->getId() == $project->getUserId()) { ?>
	<div class="alert alert-success storage-request-notice">
		<p>Now you've made a storage request don't forget:</p>
		<div><a target="_blank" href="<?=$project->getMailingListURL()?>" class="btn btn-success">Read the mailing list topic</a> this is where other members <? if(!$project->isShortTerm()) { ?> can choose to expediate your request (if its urgent) or unapprove it.<? } else { ?> can raise any concerns they have with your request.<? } ?></div>
		<div><a target="_blank" href="/storage/print/<?=$project->getId()?>" class="btn btn-success">Print DO NOT HACK label</a> after it's been <strong>approved</strong> and attach it to your project. This is to let other members know your project is accounted for. You can print it at the kiosk on the 1st floor near the entrance.</div>
		<? if($project->getState() == 'Pending Approval') { ?><p>Your request will be automatically approved after <?=$project->automaticApprovalDuration();?> days if you don't make any changes and no one replies on the mailing list.</p><? } ?>
	</div>
<? } else { ?>
	<a target="_blank" href="<?=$project->getMailingListURL()?>" class="btn btn-default">Read the mailing list topic</a>
	<a target="_blank" href="/storage/print/<?=$project->getId()?>" class="btn btn-default">Print DO NOT HACK label</a><br/>
<? } ?>
<br/>
	<?if($project->hasExtension()) { ?><strong>Extended for <?=$project->getExtensionDuration()?> days</strong><br/><br/><? } ?>
<p><?=nl2br(htmlspecialchars($project->getDescription())); ?></p>
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
	echo '<li><span class="light-color">'.date('g:ia jS M',$log->getTimestamp()).'</span> | '.str_replace('Mailing List','<a target="_blank" href="'.$project->getMailingListURL().'">Mailing List</a>',htmlspecialchars($log->getDetails())).$userURL.'</li>';
} ?>
</ul>
<? if($user->getId() != $project->getUserId() || $user->isAdmin()) { ?>
<hr/>
<form class="form-inline" role="form" method="post">
	<strong>Update Status</strong><br/>
	<p><small>Status changes are notified to the mailing list (except for archived).</small></p>
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
	<input type="text" name="reason" class="form-control" placeholder="Is there a reason for this change?" style="width: 280px;" />
	<input type="submit" name="submit" value="Update status" class="btn btn-primary"/>
</form>
<? }
 require('../footer.php'); ?>
