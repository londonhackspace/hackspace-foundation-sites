<?
$page = 'proxy';
$title = 'Proxy Vote';
require( '../header.php' );

ensureMember();

$election = 'egmjan2016';

?>
<h2>Proxy Vote</h2>

<p>This form allows you to apply for a proxy vote for the <strong>delayed voting resolutions</strong>
(resolutions 1, 2, and 3, as well as any other resolutions where a poll is demanded) in the
<a href="https://wiki.london.hackspace.org.uk/view/Organisation/2016_EGM">London Hackspace
January 2016 EGM</a>.</p>

<p>You will receive a link by email to vote online for these resolutions on Wednesday January 20th 2016.</p>

<p>If you wish to submit a proxy vote for any other resolutions, you must apply <a href="https://wiki.london.hackspace.org.uk/view/Organisation/2016_EGM#Proxy_Votes">by email as detailed here</a>.
If you have already done this, or you are attending the meeting, you don't need to submit this form.</p>

<?

if (isset($_POST['proxy'])) {
    $db->execute("INSERT INTO proxy_votes (user_id, election) VALUES (%s, %s)", $user->getId(), $election);
}

$res = $db->translatedQuery("SELECT 1 FROM proxy_votes WHERE user_id = %s AND election = %s", $user->getId(), $election);

if ($res->countReturnedRows() == 0) {
?>

<form method="post">
<button name="proxy">Apply for proxy vote</button>
</form>

<? } else { ?>
<p><strong>Thanks! You have applied for a proxy vote.</strong></p>
<? } ?>

<? require('../footer.php'); ?>
</body>
</html>
