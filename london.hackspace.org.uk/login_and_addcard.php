<?
$page = 'addcard';
require('header.php');

if ($user) {
    fSession::destroy();
    fURL::redirect();
}

if (isset($_POST['submit'])) {
    try {
        fRequest::validateCSRFToken($_POST['token']);

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

        //fSession::set('user', $user->getId());

        $card = new Card();
        $card->setUserId($user->getId());
        $card->setAddedDate(time());
        $card->setUid($uid);
        $card->store();

        fSession::destroy();
        fURL::redirect('?added');

        exit;
    } catch (fValidationException $e) {
        echo "<p>" . $e->printMessage() . "</p>";
    } catch (fSQLException $e) {
        echo "<p>An unexpected error occurred, please try again later</p>";
        trigger_error($e);
    }
}

if (isset($_GET['added']) && !isset($_POST['submit'])) {

?>
<p>Your card was added. For security, you have been logged out.</p>
<?

} else {

?>
<h2>Log In</h2>
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
                <td><label for="uid">Card to add</label></td>
                <td>
                <? if (isset($_POST['uid'])) { ?>
                    <?=htmlentities($_POST['uid'])?>
                    <input type="hidden" name="uid" value="<?=htmlentities($_POST['uid'])?>" />
                <? } else { ?>
                    <input type="text" name="uid"/>
                <? } ?>
                </td>
            </tr>
            <tr>
                <td colspan="2"><input type="submit" name="submit" value="Add card" /></td>
            </tr>
        </table>
    </fieldset>
</form>

<p>Forgotten your password? <a href="passwordreset.php">Reset it here</a>.</p>
<?
}

require('footer.php'); ?>
</body>
</html>
