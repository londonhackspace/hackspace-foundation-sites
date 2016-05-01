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

        $uid = strtoupper($_POST['uid']);
        validateCardUID($uid);

        // Random IDs are 4 bytes long and start with 0x08
        // http://www.nxp.com/documents/application_note/AN10927.pdf
        if(strlen($uid) === 8 && substr($uid,0,2) === "08") {
            throw new fValidationException('ID is randomly generated and will change every time the card is used!');
        }

        if(strlen($uid) === 8 && substr($uid,0,2) === "88") {
            throw new fValidationException('Card UID\'s can\'t start with 88');
        }

        if(strlen($uid) > 8 && substr($uid,6,8) === "88") {
            throw new fValidationException('Can\'t have cards with long uid\'s and UID3 == 88');
        }

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

<?
        require('./footer.php');
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
        <input class="form-control" type="email" id="email" name="email">
    </div>
    <div class="form-group">
        <label for="password">Password</label>
        <input class="form-control" type="password" id="password" name="password">
    </div>
    <div class="form-group">
        <input type="submit" name="submit" class="btn btn-default" value="Add card">
    </div>
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
