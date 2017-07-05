<?
$title = 'Meeting Attendance';
$meeting = 'egm2016';
$suppress_card_input = true;
require('./header.php');

ensureKioskUser();

if($user->getId() != 1) {
    fURL::redirect("/kiosk/");
}

?>
<form method="POST" style="position:absolute; left:-9999px;">
    <input type="text" name="attendance_card" id="cardid" accesskey="i"/>
</form>

<?
if (isset($_POST['attendance_card'])) {
    $cards = fRecordSet::build('Card', array('uid=' => $_POST['attendance_card']));
    if ($cards->count() == 0) {
?>
    <div class="alert alert-danger" role="alert">Unknown Card</div>
<?
    } else {
        $card = $cards->getRecord(0);
        $attending_user = new User($card->getUserId());
        $attending_user->load();
        if (!$attending_user->isMember()) {
?>
    <div class="alert alert-danger" role="alert">You are not a London Hackspace member</div>
<?
        } else {
            $res = $db->translatedQuery("SELECT 1 FROM meeting_attendees WHERE user_id = %s AND meeting = %s",
                                        $attending_user->getId(), $meeting);
            if ($res->countReturnedRows() == 0) {
                $db->execute("INSERT INTO meeting_attendees (user_id, meeting) VALUES (%s, %s)",
                    $attending_user->getId(), $meeting);
                ?>
        <div class="alert alert-success" role="alert">Welcome, <?=htmlspecialchars($attending_user->getFullName()) ?>.
          Your attendance has been registered.</div>
    <?
            } else {
?>
        <div class="alert alert-info" role="alert">Attendance already registered</div>


<?
            }
        }
    }
}

?>

<p><strong>Swipe your card below to register your attendance.</strong></p>

<?require('./footer.php')?>
</body>
</html>
