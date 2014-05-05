<? 
$page = 'edit';
$title = "Edit your details";
$desc = '';
require('../header.php');

if (!isset($user)) {
    fURL::redirect('/login.php?forward=/members/edit.php');
}
?>
<h2>Edit Your Membership Account</h2>
<?php
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

        $user->setEmail(strtolower(trim($_POST['email'])));
        $user->setFullName(trim($_POST['fullname']));
        $user->setAddress(trim($_POST['address']));
        $user->setSubscriptionPeriod($_POST['length']);
        $user->setEmergencyName(trim($_POST['emergency_name']));
        $user->setEmergencyPhone(trim($_POST['emergency_phone']));
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
  echo "<div class=\"alert alert-success\"><p>Details saved.</p></div>";
}
?>
<p><a href="http://www.legislation.gov.uk/ukpga/2006/46/part/8/chapter/2/crossheading/general">UK law</a> requires us to
store the full name and address of all our members. If you don't provide these details, you won't receive membership privileges.</p>

<? /* <p>If you prefer to pay for a longer period of time, you can change your membership period here. You must pay at least £5/month.</p> */ ?>

<form class="form-horizontal" method="post" role="form">
    <input type="hidden" name="token" value="<?=fRequest::generateCSRFToken()?>" />
    <input type="hidden" id="length" name="length" value="<?=$user->getSubscriptionPeriod()?>" />
    <div class="form-group">
        <label for="fullname" class="col-sm-3 control-label">Full Name</label>
        <div class="col-sm-9">
            <input type="text" id="fullname" name="fullname" class="form-control" value="<?= htmlspecialchars($user->getFullName()) ?>" />
        </div>
    </div>
    <div class="form-group">
        <label for="email" class="col-sm-3 control-label">Email</label>
        <div class="col-sm-9">
            <input type="email" id="email" name="email" class="form-control" value="<?=$user->getEmail()?>" />
        </div>
    </div>
    <div class="form-group">
        <label for="address" class="col-sm-3 control-label">Address</label>
        <div class="col-sm-9">
            <textarea id="address" name="address" class="form-control" rows="5"><?=$user->getAddress()?></textarea>
        </div>
    </div>
<? /*
    <div class="form-group">
       <label for="length" class="col-sm-3 control-label">Subscription Length (months)</label>
       <div class="col-sm-9">
           <input id="length" name="length" value="<?=$user->getSubscriptionPeriod()?>" />
       </div>
    </div>
*/ ?>
    <div class="form-group">
        <label for="newpassword" class="col-sm-3 control-label">New Password (optional)</label>
        <div class="col-sm-9">
            <input type="password" id="newpassword" name="newpassword" class="form-control" />
        </div>
    </div>
    <div class="form-group">
        <label for="newpasswordconfirm" class="col-sm-3 control-label">Confirm New Password</label>
        <div class="col-sm-9">
            <input type="password" id="newpasswordconfirm" name="newpasswordconfirm" class="form-control" />
        </div>
    </div>
    <br/>
    <strong>Emergency Contact</strong>
    <p>In case of a medical emergency who should we contact on your behalf?</p>
    <div class="form-group">
        <label for="emergency_name" class="col-sm-3 control-label">Full Name</label>
        <div class="col-sm-9">
            <input type="text" id="emergency_name" name="emergency_name" class="form-control" value="<?= htmlspecialchars($user->getEmergencyName()) ?>"/>
        </div>
    </div>
    <div class="form-group">
        <label for="emergency_phone" class="col-sm-3 control-label">Phone number</label>
        <div class="col-sm-9">
            <input type="text" id="emergency_phone" name="emergency_phone" class="form-control" value="<?= htmlspecialchars($user->getEmergencyPhone()) ?>"/>
        </div>
    </div>

    <div class="form-group">
        <div class="col-sm-offset-3 col-sm-9">
          <input type="submit" name="submit" value="Save" class="btn btn-primary"/>
        </div>
    </div>
</form>
<? require('../footer.php'); ?>
</body>
</html>
