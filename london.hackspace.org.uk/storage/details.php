<?
$page = 'storagedetails_edit';
require( '../header.php' );

if (!isset($user))
	fURL::redirect("/storage/{$project->getId()}");

$project = new Project(filter_var($_GET['id'], FILTER_SANITIZE_STRING));
$states = fRecordSet::build('ProjectState');
$from = new DateTime($project->getFromDate());
$to = new DateTime($project->getToDate()); 

if (isset($_POST['submit'])) {
	try {
		fRequest::validateCSRFToken($_POST['token']);

		if(!isset($_POST['state']) || $_POST['state'] == '')
			throw new fValidationException('Status field is required.');

		$project->setState(filter_var($_POST['state'], FILTER_SANITIZE_STRING));
		$project->store();
		fURL::redirect("/storage/{$project->getId()}");
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
		echo ' &nbsp; '.$from->format('jS M Y').' - '.$to->format('jS M Y');
		?>
	</small><br/>
	<small><a target="_blank" href="https://groups.google.com/forum/#!topic/london-hack-space/">Mailing list topic</a></small>
</p>
</h3>
<p>
<? $location = new Location($project->getLocationId());
	echo $location->getName(); ?>, 
	<?=$project->getLocation(); ?>
</p>
<p>
	<?=$project->getDescription(); ?>
</p>
<br/>
<?//if($user->getId() == $project->getUserId() && $project->getState() != 'Approved') { ?>
<a href="/storage/edit/<?=$project->getId()?>" class="btn btn-primary">Edit request</a>
<? //} else { ?>
<form class="form-inline" role="form" method="post">
	<input type="hidden" name="token" value="<?=fRequest::generateCSRFToken()?>" />
	<select class="form-control" name="state">
		<? foreach($states as $state) {
				echo '<option value="'.$state->getName().'" ';
				if($project->getState() == $state->getName()) { 
					echo 'selected'; 
				}
				echo '>'.$state->getName().'</option>';
			} ?>
	</select>
	<input type="submit" name="submit" value="Update status" class="btn btn-primary"/>
</form>
<? //}
 require('../footer.php'); ?>