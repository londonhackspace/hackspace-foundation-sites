<?
$page = 'storagedetails_edit';
require( '../header.php' );

if (!isset($user))
	fURL::redirect("/login.php?forward=/storage/{$project->getId()}");

$project = new Project(filter_var($_GET['id'], FILTER_SANITIZE_STRING));
$projectslogs = fRecordSet::build('ProjectsLog',array('project_id=' => $project->getId()), array('timestamp' => 'asc'));
$states = fRecordSet::build('ProjectState');
$from = new DateTime($project->getFromDate());
$to = new DateTime($project->getToDate()); 
$projectUser = new User($project->getUserId());

if (isset($_POST['remove']) && ($user->getId() == $project->getUserId())) {
	try {
		fRequest::validateCSRFToken($_POST['token']);
		if($project->getState() == 'Approved' || $project->getState() == 'Passed Deadline') {
			$project->setState('Removed');
			$project->store();

			// log the update
			$logmsg = 'Status changed to '.$project->getState();
			$log = new ProjectsLog();
			$log->setProjectId($project->getId());
			$log->setTimestamp(time());
			$log->setDetails($logmsg);
			$log->setUserId($user->getId());
			$log->store();
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
		if($newStatus != $project->getState() && (
			   ($newStatus == 'Pending Approval') ||
			   ($newStatus == 'Removed' && ($project->getState() == 'Approved' || $project->getState() == 'Passed Deadline')) ||
			   ($newStatus == 'Approved' && ($project->getState() == 'Unapproved' || $project->getState() == 'Pending Approval')) ||
			   ($newStatus == 'Unapproved' && ($project->getState() == 'Approved' || $project->getState() == 'Pending Approval')) ||
			   ($newStatus == 'Archived' && ($project->getState() == 'Unapproved')) ||
			   ($newStatus == 'Passed Deadline' && ($project->getState() == 'Approved'))
		   )) {
			$project->setState(filter_var($_POST['state'], FILTER_SANITIZE_STRING));
			$project->store();

			// log the update
			$logmsg = 'Status changed to '.$project->getState();
			$log = new ProjectsLog();
			$log->setProjectId($project->getId());
			$log->setTimestamp(time());
			$log->setDetails($logmsg);
			$log->setUserId($user->getId());
			$log->store();

			// send to mailing list
			if($project->getState() != 'Archived') {
				$toEmail = 'london-hack-space-test@googlegroups.com';
				$subject = 'Storage Request #'.$project->getId().': '.$project->getName();
				$message =  $logmsg . " by " . htmlspecialchars($user->getFullName());

				$headers = 'From: no-reply@london.hackspace.org.uk' . "\r\n" .
					'Reply-To: no-reply@london.hackspace.org.uk' . "\r\n" .
					'Content-Type:text/html;charset=utf-8' . "\r\n" .
					'X-Mailer: PHP/' . phpversion();

				mail($toEmail, $subject, $message, $headers);

				// log the google groups post
				$log = new ProjectsLog();
				$log->setProjectId($project->getId());
				$log->setTimestamp(time()+1);
				$log->setDetails('Posted to the Mailing List');
				$log->store();
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

<h2>Storage Request</h2>
<h3>
	<?=$project->getName(); ?>
	<div class="status <?= strtolower($project->getState()); ?>"><?= $project->getState(); ?></div>
	<p><small>
		Stored for 
		<? 
		if($from->diff($to)->format('%d') == '0') { 
			echo $from->diff($to)->format('%m month(s)');
		} else if($from->diff($to)->format('%m') == '0') { 
			echo $from->diff($to)->format('%d day(s)');
		} else { 
			echo $from->diff($to)->format('%d day(s), %m month(s)'); 
		}
		echo ' | '.$from->format('jS M Y').' - '.$to->format('jS M Y');
		?>
	<br/>
	<a href="/members/member.php?id=<?=$project->getUserId()?>"><?=htmlspecialchars($projectUser->getFullName())?></a> | 
	<a target="_blank" href="https://groups.google.com/forum/#!topicsearchin/london-hack-space-test/subject$3AStorage$20AND$20subject$3ARequest$20AND$20subject$3A$23<?=$project->getId()?>">Mailing list topic</a></small>
</p>
</h3>
<p>
<? $location = new Location($project->getLocationId());
	echo $location->getName(); ?>, 
	<?=$project->getLocation(); ?>
</p>
<p>
	<?=nl2br(stripslashes($project->getDescription())); ?>
</p>
<br/>
<h4>Activity log</h4>
<ul>
<? foreach($projectslogs as $log) {
	$userURL = '';
	if($log->getUserId() != null) {
		$logUser = new User($log->getUserId());
		$userURL = ' by <a href="/members/member.php?id='.$log->getUserId().'">'.htmlspecialchars($logUser->getFullName()).'</a>';
	}
	echo '<li><span class="light-color">'.date('g:ia jS M',$log->getTimestamp()).'</span> | '.str_replace('Mailing List','<a target="_blank" href="https://groups.google.com/forum/#!topicsearchin/london-hack-space-test/subject$3AStorage$20AND$20subject$3ARequest$20AND$20subject$3A$23'.$project->getId().'">Mailing List</a>',$log->getDetails()).$userURL.'</li>';
} ?>
</ul><br/>
<? if($user->getId() == $project->getUserId() && ($project->getState() == 'Pending Approval' || $project->getState() == 'Unapproved')) { ?>
<a href="/storage/edit/<?=$project->getId()?>" class="btn btn-primary">Edit request</a>
<? } if($user->getId() == $project->getUserId() && ($project->getState() == 'Approved' || $project->getState() == 'Passed Deadline')) { ?>
<form class="form-inline" role="form" method="post">
<input type="submit" name="remove" class="btn btn-primary">Mark as removed from the space</a>
</form>
<? } ?>
<br/></br>
<? if($user->getId() != $project->getUserId() || $user->isAdmin()) { ?>
<form class="form-inline" role="form" method="post">
	<input type="hidden" name="token" value="<?=fRequest::generateCSRFToken()?>" />
	<select class="form-control" name="state">
		<option value="" disabled selected></option>
		<? foreach($states as $state) {
				$newStatus = $state->getName();
				if($newStatus != $project->getState() && (
					   ($newStatus == 'Pending Approval') ||
					   ($newStatus == 'Removed' && ($project->getState() == 'Approved' || $project->getState() == 'Passed Deadline')) ||
					   ($newStatus == 'Approved' && ($project->getState() == 'Unapproved' || $project->getState() == 'Pending Approval')) ||
					   ($newStatus == 'Unapproved' && ($project->getState() == 'Approved' || $project->getState() == 'Pending Approval')) ||
					   ($newStatus == 'Archived' && ($project->getState() == 'Unapproved')) ||
					   ($newStatus == 'Passed Deadline' && ($project->getState() == 'Approved'))
				   )) {

					echo '<option value="'.$state->getName().'" ';
					if($project->getState() == $state->getName()) { 
						echo 'selected'; 
					}
					echo '>'.$state->getName().'</option>';
				}
			} ?>
	</select>
	<input type="submit" name="submit" value="Update status" class="btn btn-primary"/>
</form>
<? }
 require('../footer.php'); ?>