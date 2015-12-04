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

        // Strip colons from uid (easier to copy paste from some NFC
        // reader apps).
        fRequest::set('uid', str_replace(':','',fRequest::get('uid')) );

        $validator = new fValidation();
        $validator->addRequiredFields('password', 'email', 'uid');
        $validator->addEmailFields('email');
        $validator->addRegexRule('uid', '#^[0-9a-fA-F]+$#', 'Not in hex format');

        $validator->validate();

        $uid = strtoupper($_POST['uid']);
        if ($uid == '21222324') {
            /* New Visa cards return this, presumably for privacy */
            throw new fValidationException('Non-unique UID. This card cannot be added to the system.');
        }

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
                <td><input type="text" name="uid" value="<?=htmlentities($_POST['uid'])?>"/></td>
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
