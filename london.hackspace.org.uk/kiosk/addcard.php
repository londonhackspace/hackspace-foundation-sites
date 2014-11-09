<?
require('./header.php');

if (isset($_POST['submit'])) {
        $validator = new fValidation();
        $validator->addRequiredFields('password', 'email', 'uid');
        $validator->addEmailFields('email');
        $validator->addRegexRule('uid', '#^[0-9a-fA-F]+$#', 'Not in hex format');

        $validator->validate();

        $uid = strtoupper($_POST['uid']);
        if ($uid == '21222324') {
            /* New Visa cards return this, presumably for privacy */
            throw new fValidationException('Non-unique UID.');
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

<p><a href="/kiosk/" class="btn btn-default">Go back</a></p>

<?
        require('./footer.php');
        exit;
}

?>
<h2>Add a card to your account</h2>
<form method="post">
    <input type="hidden" name="token" value="<?=fRequest::generateCSRFToken()?>" />
    <fieldset>
        <table>
            <tr>
                <td><label for="email">Email</label></td>
                <td><input type="email" id="email" name="email"/></td>
            </tr>
            <tr>
                <td><label for="password">Password</label></td>
                <td><input type="password" id="password" name="password" /></td>
            </tr>
            <tr>
                <td colspan="2"><input type="submit" name="submit" class="btn btn-default" value="Add card" /></td>
            </tr>
        </table>
        <input type="hidden" name="uid" value="<?=htmlentities($_GET['cardid'])?>">
    </fieldset>
</form>

<a href="/kiosk/" class="btn btn-default">Go back</a>
    <?require('./footer.php')?>
