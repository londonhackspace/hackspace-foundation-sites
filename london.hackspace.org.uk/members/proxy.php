<?
$page = 'proxy';
$title = 'Proxy Vote';
require( '../header.php' );

ensureMember();

$election = 'egmjan2016';

?>
<h2>January 2016 EGM: Online Vote</h2>

<p>This form allows you to apply for an online vote for resolutions 1, 2, and 3 in the
<a href="https://wiki.london.hackspace.org.uk/view/Organisation/2016_EGM">London Hackspace
January 2016 EGM</a>.</p>

<?

if (isset($_POST['proxy'])) {
    $db->execute("INSERT INTO proxy_votes (user_id, election) VALUES (%s, %s)", $user->getId(), $election);
}

$res = $db->translatedQuery("SELECT 1 FROM proxy_votes WHERE user_id = %s AND election = %s", $user->getId(), $election);

if ($res->countReturnedRows() == 0) {
?>

<p>Applications for an online vote are now closed.</p>

<? } else { ?>
<p><strong>Thanks! You have applied for an online vote. If you have not already received an email telling you how to vote, you will receive one no later than 20:00 on Tuesday January 26th.</strong></p>

<p>If you haven't received an email by this time, please email russ@london.hackspace.org.uk</p>

<? } ?>

<? require('../footer.php'); ?>
</body>
</html>
