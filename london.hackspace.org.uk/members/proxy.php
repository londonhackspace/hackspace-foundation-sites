<?
$page = 'proxy';
$title = 'Proxy Vote';
require( '../header.php' );

ensureMember();

$election = 'egmjan2016';

?>
<h2>January 2016 EGM: Online Vote</h2>

<p>This form allows you to apply for an online proxy vote for resolutions 1, 2, and 3 in the
<a href="https://wiki.london.hackspace.org.uk/view/Organisation/2016_EGM">London Hackspace
January 2016 EGM</a>.</p>

<p>You will receive a link by email to vote online for these resolutions on Wednesday January 20th 2016.</p>

<p>If you have already applied for a proxy vote, or you attended the meeting, you don't need to submit this form.</p>

<?

if (isset($_POST['proxy'])) {
    $db->execute("INSERT INTO proxy_votes (user_id, election) VALUES (%s, %s)", $user->getId(), $election);
}

$res = $db->translatedQuery("SELECT 1 FROM proxy_votes WHERE user_id = %s AND election = %s", $user->getId(), $election);

if ($res->countReturnedRows() == 0) {
?>

<? /*<form method="post">
<button name="proxy">Apply for a vote</button>
</form>

 */ ?>

Online vote applications are now closed.

<? } else { ?>
<p><strong>Thanks! You have applied for an online vote. You will receive an email when voting opens.</strong></p>
<? } ?>

<? require('../footer.php'); ?>
</body>
</html>
