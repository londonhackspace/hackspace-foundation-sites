<? 
$page = 'membership';
require('header.php');

if ($user) {
    fURL::redirect('/members');
}

if (isset($_POST['reset'])) {
    try {
        fRequest::validateCSRFToken($_POST['token']);

        $validator = new fValidation();
        $validator->addRequiredFields('password');
        $validator->validate();

        if ($_POST['password'] != $_POST['passwordconfirm']) {
            throw new fValidationException('Passwords do not match');
        }

        $user = User::checkPasswordResetToken($_POST['resettoken']);
        if ($user == False) {
            throw new fValidationException('Invalid token, please make sure you followed the correct link');
        }

        $user->setPassword(fCryptography::hashPassword($_POST['password']));
        $user->store();
        fURL::redirect('/login.php');
        exit;
    } catch (fValidationException $e) {
        echo "<p>" . $e->printMessage() . "</p>";
    } catch (fSQLException $e) {
        echo "<p>An unexpected error occurred, please try again later</p>";
        trigger_error($e);
    }
} elseif (isset($_POST['sendtoken'])) {
    try {
        fRequest::validateCSRFToken($_POST['token']);
        $validator = new fValidation();
        $validator->addRequiredFields('email');
        $validator->validate();

        $user = new User(array('email' => $_POST['email']));
        $token = $user->getResetPasswordToken();
        $email = new fEmail();
        $email->addRecipient($user->getEmail());
        $email->setFromEmail('contact@hackspace.org.uk', 'London Hackspace');
        $email->setSubject('London Hackspace Password Reset');
        $name = $user->getFullName();
        $email->setBody("Hi $name,

You (or someone pretending to be you) requested a password reset for your
London Hackspace account. To reset your password, go to this address:

http://{$_SERVER['SERVER_NAME']}/passwordreset.php?token=$token

If you don't want to reset your password, just ignore this email.

Cheers,

The London Hackspace email monkey
");
        $email->send();
        echo "<p>An email has been sent to you with further instructions.</p>";
    } catch(fNotFoundException $e) {?>
        echo "<p>An email has been sent to you with further instructions.</p>";
<?  } catch (fValidationException $e) {
        echo "<p>" . $e->printMessage() . "</p>";
    } catch (fSQLException $e) {
        echo "<p>An unexpected error occurred, please try again later</p>";
        trigger_error($e);
    }

} elseif (isset($_GET['token'])) {
?>
<p>OK, now enter your new password:</p>

<form method="post">
    <fieldset>
        <input type="hidden" name="resettoken" value="<?=htmlentities($_GET['token'])?>" />
        <input type="hidden" name="token" value="<?=fRequest::generateCSRFToken()?>" />
        <table id="resettable">
            <tr>
                <td><label for="password">Password</label></td>
                <td><input type="password" id="password" name="password" /></td>
            </tr>
            <tr>
                <td><label for="passwordconfirm">Confirm Password</label></td>
                <td><input type="password" id="passwordconfirm" name="passwordconfirm" /></td>
            </tr>
            <tr>
                <td colspan="2"><input type="submit" name="reset" value="Submit" /></td>
            </tr>
        </table>
    </fieldset>
</form>
<?
} else {
?>
<p>To reset your password, enter your email address:</p>

<form method="post">
    <fieldset>
        <input type="hidden" name="token" value="<?=fRequest::generateCSRFToken()?>" />
        <table id="resettable">
            <tr>
                <td><label for="email">Email</label></td>
                <td><input type="text" id="email" name="email"/></td>
            </tr>
            <tr>
                <td colspan="2"><input type="submit" name="sendtoken" value="Submit" /></td>
            </tr>
        </table>
    </fieldset>
</form>
<? 
}
require('footer.php'); ?>
