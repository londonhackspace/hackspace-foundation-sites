<?
$page = 'storagedetails_print';
$title = "Storage list";
require( '../header-mini.php' );

$project = new Project(filter_var($_GET['id'], FILTER_SANITIZE_STRING));
if (!isset($user))
	fURL::redirect("/login.php?forward=/storage/print/{$project->getId()}");

$to = new DateTime($project->getToDate()); 
$projectUser = new User($project->getUserId());
?>
<style type="text/css">
	.print {
		text-align: center;
		max-width: 500px;
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
</style>
<div class="print">
	<h1>DO NOT HACK</h1>
	<h1><?=$project->getName()?></h1>
	<h2><?=htmlspecialchars($projectUser->getFullName())?></h2>
	<div id="qrcode"></div>
	<h2>Removed by <?=$to->format('jS M Y')?></h2>
</div>
<script type="text/javascript" src="/javascript/qrcode.min.js"></script>
<script type="text/javascript">
new QRCode(document.getElementById("qrcode"), "https://london.hackspace.org.uk/storage/<?=$project->getId()?>");
</script>
</div>
</body>
</html>