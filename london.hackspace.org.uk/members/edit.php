<? 
$page = 'edit';
$title = "Edit your details";
$desc = '';
require('../header.php');

if (!isset($user)) {
    fURL::redirect('/login.php?forward=/members/edit.php');
}

if (isset($_POST['submit'])) {
    try {
        fRequest::validateCSRFToken($_POST['token']);

        $validator = new fValidation();
        $validator->addRequiredFields('fullname', 'email', 'address', 'length');
        $validator->addEmailFields('email');

        $validator->validate();

        if ($_POST['newpassword'] != '') {
            if ($_POST['newpassword'] != $_POST['newpasswordconfirm']) {
                throw new fValidationException('Passwords do not match');
            }
            $user->setPassword(fCryptography::hashPassword($_POST['newpassword']));
        }

        $user->setEmail(strtolower($_POST['email']));
        $user->setFullName($_POST['fullname']);
        $user->setAddress($_POST['address']);
        $user->setSubscriptionPeriod($_POST['length']);
        $user->store();
        fURL::redirect('?saved');
        exit;
    } catch (fValidationException $e) {
        echo "<p>" . $e->printMessage() . "</p>";
    } catch (fSQLException $e) {
        echo "<p>An unexpected error occurred, please try again later</p>";
        trigger_error($e);
    }
}

if (isset($_GET['saved'])) {
    echo "<p>Details saved.</p>";
}
?>
<h2>Edit Your Membership Details</h2>
<p><a href="http://www.legislation.gov.uk/ukpga/2006/46/part/8/chapter/2/crossheading/general">UK law</a> requires us to
store the full name and address of all our members. If you don't provide these details, you won't receive membership privileges.</p>

<p>If you prefer to pay for a longer period of time, you can change your membership period here. You must pay at least £5/month.</p>

<form method="post">
    <input type="hidden" name="token" value="<?=fRequest::generateCSRFToken()?>" />
    <fieldset>
        <table id="edittable">
            <tr>
                <td><label for="email">Email</label></td>
                <td><input type="text" id="email" name="email" value="<?=$user->getEmail()?>" /></td>
            </tr>
            <tr>
                <td><label for="fullname">Full Name</label></td>
                <td><input type="text" id="fullname" name="fullname" value="<?= htmlspecialchars($user->getFullName()) ?>" /></td>
            </tr>
            <tr>
                <td><label for="address">Address</label></td>
                <td><textarea id="address" name="address" cols="30" rows="5"><?=$user->getAddress()?></textarea></td>
            </tr>
            <tr>
                <td><label for="length">Subscription Length (months)</label></td>
                <td><input id="length" name="length" value="<?=$user->getSubscriptionPeriod()?>" /></td>
            </tr>
            <tr>
                <td><label for="newpassword">New Password (optional)</label></td>
                <td><input type="password" id="newpassword" name="newpassword" /></td>
            </tr>
            <tr>
                <td><label for="newpasswordconfirm">Confirm New Password</label></td>
                <td><input type="password" id="newpasswordconfirm" name="newpasswordconfirm" /></td>
            </tr>
            <tr>
                <td colspan="2"><input type="submit" name="submit" value="Submit" /></td>
            </tr>
        </table>
    </fieldset>
</form>
<? require('../footer.php'); ?>
