<? 
$page = 'login';
require('header.php'); 

if ($user) {
    fURL::redirect('/members');
}

if (isset($_POST['submit'])) {
    try {
        fRequest::validateCSRFToken($_POST['token']);

        $validator = new fValidation();
        $validator->addRequiredFields('password', 'email');
        $validator->addEmailFields('email');

        $validator->validate();

        $users = fRecordSet::build('User', array('email=' => strtolower($_POST['email'])));
        if ($users->count() == 0) {
            throw new fValidationException('Invalid username or password.');
        }

        $rec = $users->getRecords();
        $user = $rec[0];

        if (!fCryptography::checkPasswordHash($_POST['password'], $user->getPassword())) {
            throw new fValidationException('Invalid username or password.');
        }

        fSession::set('user', $user->getId());

        if (fRequest::get('persistent_login', 'boolean')) {
            fSession::enablePersistence();
        }

        if (isset($_POST['forward'])) {
            fURL::redirect('http://' . $_SERVER['SERVER_NAME'] . $_POST['forward']);
        } else {
            fURL::redirect('/members');
        }
        exit;
    } catch (fValidationException $e) {
        echo "<p>" . $e->printMessage() . "</p>";
    } catch (fSQLException $e) {
        echo "<p>An unexpected error occurred, please try again later</p>";
        trigger_error($e);
    }
}

?>
<h2>Log In</h2>
<form method="post">
    <input type="hidden" name="token" value="<?=fRequest::generateCSRFToken()?>" />
    <fieldset>
        <? if (isset($_GET['forward'])) { ?>
            <input type="hidden" name="forward" value="<?=htmlentities($_GET['forward'])?>" />
        <? }?>
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
                <td>&nbsp;</td>
                <td><input type="checkbox" value="true" name="persistent_login" /> Remember me.</td>
            </tr>
            <tr>
                <td colspan="2"><input type="submit" name="submit" value="Log In" /></td>
            </tr>
        </table>
    </fieldset>
</form>

<p>Forgotten your password? <a href="passwordreset.php">Reset it here</a>.</p>
<? require('footer.php'); ?>
