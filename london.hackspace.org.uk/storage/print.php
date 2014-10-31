<?
$page = 'storagedetails_print';
$title = "Storage list";
require( '../header-mini.php' );

if (!isset($user))
	fURL::redirect("/login.php?forward=/storage/print/{$_GET['id']}");

$project = new Project(filter_var($_GET['id'], FILTER_SANITIZE_STRING));
$to = new DateTime($project->getToDate()); 
$projectUser = new User($project->getUserId());
?>
<style type="text/css">
	.print {
		max-width: 700px;
		margin-left: auto;
		margin-right: auto;
		font-family: sans-serif;
	}
	h1, h2, h3 {
		padding: 0;
		margin: 10px;
	}
	img {
		display: block;
		margin-left: auto;
		margin-right: auto;
	}
	#qrcode {
		float: left;
		margin-right: 30px;
	}
</style>
<div class="print">
	<div id="qrcode"></div>
	<h1>DO NOT HACK</h1>
	<h1><?=$project->getName()?></h1>
	<h3><?=htmlspecialchars($projectUser->getFullName())?></h3>
	<h3><?=$projectUser->getEmail()?></h3>
	<br/>
	<h2>Removed by <?=$to->format('jS M Y')?></h2>
	<strong><?=$project->getExtensionDuration()?> days maximum time extension</strong>
</div>
<script type="text/javascript" src="/javascript/qrcode.min.js"></script>
<script type="text/javascript">
new QRCode(document.getElementById("qrcode"), "https://london.hackspace.org.uk/storage/<?=$project->getId()?>");
</script>
</div>
</body>
</html>
