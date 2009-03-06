<? 
$page = 'signup';
require('header.php'); 

if ($user) {
    fURL::redirect('/members');
}

if (isset($_POST['submit'])) {
    try {
        $validator = new fValidation();
        $validator->addRequiredFields('fullname', 'password', 'email');
        $validator->addEmailFields('email');

        $validator->validate();

        if ($_POST['password'] != $_POST['passwordconfirm']) {
            throw new fValidationException('Passwords do not match');
        }

        $user = new User();
        $user->setEmail($_POST['email']);
        $user->setFullName($_POST['fullname']);
        $user->setPassword(fCryptography::hashPassword($_POST['password']));
        $user->store();

        fSession::set('user', $user->getId());

        fURL::redirect('/members');
        exit;
    } catch (fValidationException $e) {
        echo "<p>" . $e->printMessage() . "</p>";
    } catch (fSQLException $e) {
        echo "<p>An unexpected error occurred, please try again later</p>";
        trigger_error($e);
    }
}

?>
<h2>Sign up to the Hackspace Foundation</h2>
<p>To become a member of the Hackspace Foundation, we need a few details from you.</p>
<form method="post">
<fieldset>
<table id="signuptable">
<tr><td><label for="email">Email</label></td><td><input type="text" id="email" name="email"/></td></tr>
<tr><td><label for="password">Password</label></td><td><input type="password" id="password" name="password" /></td></tr>
<tr><td><label for="passwordconfirm">Confirm Password</label></td><td><input type="password" id+"passwordconfirm" name="passwordconfirm" /></td></tr>
<tr><td><label for="fullname">Full Name</label></td><td><input type="text" id="fullname" name="fullname"/></td></tr>
<tr><td colspan="2"><input type="submit" name="submit" value="Submit" /></td></tr>
</table>
</fieldset>
</form>
<? require('footer.php'); ?>
