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

        $user = new User(array('email' => strtolower($_POST['email'])));
        $token = $user->getResetPasswordToken();
        $email = new fEmail();
        $email->addRecipient($user->getEmail());
        $email->setFromEmail('contact@hackspace.org.uk', 'London Hackspace');
        $email->setSubject('London Hackspace Password Reset');
        $name = $user->getFullName();
        $email->setBody("Hi $name,

You (or someone pretending to be you) requested a password reset for your
London Hackspace account. To reset your password, go to this address:

https://{$_SERVER['SERVER_NAME']}/passwordreset.php?token=$token

If you don't want to reset your password, just ignore this email.

Cheers,

The London Hackspace email monkey
");
        $smtp = new fSMTP('turing.hackspace.org.uk');
        $email->send($smtp);
        echo "<p>An email has been sent to you with further instructions.</p>";
    } catch(fNotFoundException $e) {?>
        <p>No user exists with that email address. <a href="signup.php">Sign up</a>? 
                    Or <a href="passwordreset.php">try again</a>?</p>
<?  } catch (fValidationException $e) {
        echo "<p>" . $e->printMessage() . "</p>";
    } catch (fSQLException $e) {
        echo "<p>An unexpected error occurred, please try again later</p>";
        trigger_error($e);
    }

} elseif (isset($_GET['token'])) {
?>
<p>OK, now enter your new password:</p>

<form method="post" class="form-horizontal">
	<input type="hidden" name="resettoken" value="<?=htmlentities($_GET['token'])?>" />
	<input type="hidden" name="token" value="<?=fRequest::generateCSRFToken()?>" />
    <div class="form-group">
        <label for="password" class="col-sm-4 control-label">Password</label>
        <div class="col-sm-8">
            <input type="password" required autofocus id="password" name="password" class="form-control" />
        </div>
    </div>
    <div class="form-group">
        <label for="passwordconfirm" class="col-sm-4 control-label">Confirm Password</label>
        <div class="col-sm-8">
            <input type="password" required id="passwordconfirm" name="passwordconfirm" class="form-control" />
        </div>
    </div>
    <div class="form-group">
        <div class="col-sm-offset-4 col-sm-8">
          <input type="submit" name="reset" value="Submit" class="btn btn-primary"/>
        </div>
    </div>
</form>
<?
} else {
?>
<p>To reset your password, enter your email address:</p>

<form method="post" class="form-horizontal">
	<input type="hidden" name="token" value="<?=fRequest::generateCSRFToken()?>" />
    <div class="form-group">
        <label for="email" class="col-sm-2 control-label">Email</label>
        <div class="col-sm-10">
            <input type="email" required autofocus id="email" name="email" class="form-control" />
        </div>
    </div>
    <div class="form-group">
        <div class="col-sm-offset-2 col-sm-10">
          <input type="submit" name="sendtoken" value="Submit" class="btn btn-primary"/>
        </div>
    </div>
</form>

<p>If you are having difficulty please email <i>contact (at) london.hackspace.org.uk</i>.</p>

<? 
}
require('footer.php'); ?>
</body>
</html>
