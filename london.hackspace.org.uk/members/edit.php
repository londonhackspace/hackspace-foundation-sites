<? 
$page = 'edit';
$title = "Edit your details";
$desc = '';
require('../header.php');

if (!isset($user)) {
    fURL::redirect('/login.php');
}

if (isset($_POST['submit'])) {
    try {
        $validator = new fValidation();
        $validator->addRequiredFields('fullname', 'email', 'address');
        $validator->addEmailFields('email');

        $validator->validate();

        if ($_POST['newpassword'] != '') {
            if ($_POST['newpassword'] != $_POST['newpasswordconfirm']) {
                throw new fValidationException('Passwords do not match');
            }
            $user->setPassword(fCryptography::hashPassword($_POST['newpassword']));
        }

        $user->setEmail($_POST['email']);
        $user->setFullName($_POST['fullname']);
        $user->setAddress($_POST['address']);
        $user->store();
        fURL::redirect($_SERVER['REQUEST_URI']);
        exit;
    } catch (fValidationException $e) {
        echo "<p>" . $e->printMessage() . "</p>";
    } catch (fSQLException $e) {
        echo "<p>An unexpected error occurred, please try again later</p>";
        trigger_error($e);
    }
}

?>
<h2>Edit Your Membership Details</h2>
<p><a href="http://www.legislation.gov.uk/ukpga/2006/46/part/8/chapter/2/crossheading/general">UK law</a> requires us to
store the full name and address of all our members. If you don't provide these details, you won't be able to get membership privileges.</p>
<form method="post">
<fieldset>
<table id="edittable">
<tr><td><label for="email">Email</label></td><td><input type="text" id="email" name="email" value="<?=$user->getEmail()?>"/></td></tr>
<tr><td><label for="fullname">Full Name</label></td><td><input type="text" id="fullname" name="fullname" value="<?=$user->getFullName()?>"/></td></tr>
<tr><td><label for="address">Address</label></td><td><textarea id="address" name="address" cols="30" rows="5"><?=$user->getAddress()?></textarea></td></tr>
<tr><td><label for="newpassword">New Password</label></td><td><input type="password" id="newpassword" name="newpassword" /></td></tr>
<tr><td><label for="newpasswordconfirm">Confirm New Password</label></td><td><input type="password" id="newpasswordconfirm" name="newpasswordconfirm" /></td></tr>
<tr><td colspan="2"><input type="submit" name="submit" value="Submit" /></td></tr>
</table>
</fieldset>
</form>
<? require('../footer.php'); ?>
