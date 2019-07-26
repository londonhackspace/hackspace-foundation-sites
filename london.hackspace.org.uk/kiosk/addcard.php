<?
$title = 'Add a card';
require('./header.php');

if (isset($_POST['submit'])) {
    try{
        $validator = new fValidation();
        $validator->addRequiredFields('password', 'email', 'uid');
        $validator->addEmailFields('email');
        $validator->addRegexRule('uid', '#^[0-9a-fA-F]+$#', 'Not in hex format');

        $validator->validate();

        $uid = sanitiseCardUID($_POST['uid']);
        validateCardUIDUsable($uid);

        $users = fRecordSet::build('User', array('email=' => strtolower($_POST['email'])));
        if ($users->count() == 0) {
            throw new fValidationException('Invalid username or password.');
        }

        $rec = $users->getRecords();
        $user = $rec[0];

        if (!fCryptography::checkPasswordHash($_POST['password'], $user->getPassword())) {
            throw new fValidationException('Invalid username or password.');
        }

        $card = new Card();
        $card->setUserId($user->getId());
        $card->setAddedDate(time());
        $card->setUid($uid);
        $card->store();

        fSession::destroy(); ?>

<h2>Card successfully added</h2>
Please allow at least 10 minutes before attempting to use that card on door entry or acnode systems.
<?
        require('./footer.php');
        // Now we could trigger acserver to re-read carddb
        exit;
    } catch (fValidationException $e) {
        echo "<p>" . $e->printMessage() . "</p>";
    }
}

?>
<p>Please log in with your London Hackspace membership account:</p>
<form method="post">
    <input type="hidden" name="token" value="<?=fRequest::generateCSRFToken()?>" />
    <div class="form-group">
        <label for="email">Email</label>
        <input class="form-control" type="email" id="email" name="email" autocomplete="off">
    </div>
    <div class="form-group">
        <label for="password">Password</label>
        <input class="form-control" type="password" id="password" name="password" autocomplete="off">
    </div>
    <div class="form-group">
        <input type="submit" name="submit" class="btn btn-default" value="Add card">
    </div>
    <?php /* This is a landing page so cardid isn't validated at this point */ ?>
    <input type="hidden" name="uid" value="<?=htmlentities($_GET['cardid'])?>">
</form>

    <?require('./footer.php')?>
<script type="text/javascript">
    $(function() {
        $('#email').focus();
});
</script>
</body>
</html>
