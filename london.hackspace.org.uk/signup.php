<? 
$page = 'membership';
require('header.php'); 

if ($user) {
    fURL::redirect('/members');
}

if (isset($_POST['submit'])) {
    try {
        fRequest::validateCSRFToken($_POST['token']);

        $validator = new fValidation();
        $validator->addRequiredFields('fullname', 'password', 'email', 'address');
        $validator->addEmailFields('email');

        $validator->validate();

        if ($_POST['password'] != $_POST['passwordconfirm']) {
            throw new fValidationException('Passwords do not match');
        }

        $user = new User();
        $user->setEmail(strtolower(trim($_POST['email'])));
        $user->setFullName(trim($_POST['fullname']));
        $user->setAddress(trim($_POST['address']));
        $user->setPassword(fCryptography::hashPassword($_POST['password']));
        $user->setEmergencyName(trim($_POST['emergency_name']));
        $user->setEmergencyPhone(trim($_POST['emergency_phone']));
        $user->setGocardlessUser(false);
        $user->store();

        $email = new fEmail();
        $email->addRecipient($user->getEmail());
        $email->setFromEmail('contact@london.hackspace.org.uk', 'London Hackspace');
        $email->setSubject('London Hackspace joining information');
        $name = $user->getFullName();
        $email->setBody("Hi $name,

You (or someone who gave us your email address) have set up your London Hackspace membership account:

To actually be able to use the Hackspace, you now would need to become a paying member by setting up a standing order to pay us

Our bank details are:

Barclays Bank plc
Sort Code: 20-32-06
Account number: 53413292
Account Name: London Hackspace Ltd
Reference: ". sprintf("HS%05u", $user->getId())."

It is vitally important to include your unique reference number above, so that our automated systems can identify the money coming into our
bank account is from you, to activate your membership.

Your membership will be activated once our bank clears the funds, and our daily script spots it.

Reading material for new members: https://wiki.london.hackspace.org.uk/view/New_members_guide

If you didn't actually sign up for London Hackspace, and someone is pretending to be you, just ignore this email. We won't sent you many more emails.
If it is troubling you, you can reply to this email.

Cheers,

The London Hackspace membership automated script
");
        $smtp = new fSMTP('turing.hackspace.org.uk');
        $email->send($smtp);

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
<h2>Membership</h2>
<p>The London Hackspace is a members-owned non-profit association. Members have a hand in the running of the
organisation as well as 24/7 access to the space.</p>

<p>Membership is paid monthly by standing order. We require £15/month from members in order to keep the space running. <a href="/cost-of-hacking/">Running an
organisation like this in London isn't cheap</a>. If you can't afford £15/month but still wish to become a member, please email membership@london.hackspace.org.uk to discuss.

<h2>Join the London Hackspace</h2>
<p>By joining the London Hackspace you're becoming a member of London Hackspace Ltd., and you agree to be bound by 
<a href="/organisation/docs/articles.pdf">our constitution</a>. You also agree to follow the
<a href="http://wiki.london.hackspace.org.uk/view/Rules">rules of the space</a>.</p>

<p><a href="http://www.legislation.gov.uk/ukpga/2006/46/part/8/chapter/2/crossheading/general">UK law</a> requires that
you provide your real name and address in order to join. Your name will be visible to all members.</p>
<p>* = mandatory field</p>

<form class="form-horizontal" method="post" role="form">
    <input type="hidden" name="token" value="<?=fRequest::generateCSRFToken()?>" />
    <div class="form-group">
        <label for="fullname" class="col-sm-4 control-label">Full Name *</label>
        <div class="col-sm-8">
            <input type="text" required autofocus id="fullname" name="fullname" class="form-control" />
        </div>
    </div>
    <div class="form-group">
        <label for="email" class="col-sm-4 control-label">Email *</label>
        <div class="col-sm-8">
            <input type="email" required id="email" name="email" class="form-control" />
        </div>
    </div>
    <div class="form-group">
        <label for="password" class="col-sm-4 control-label">Password *</label>
        <div class="col-sm-8">
            <input type="password" required id="password" name="password" class="form-control" />
        </div>
    </div>
    <div class="form-group">
        <label for="password" class="col-sm-4 control-label">Confirm Password *</label>
        <div class="col-sm-8">
            <input type="password" required id="passwordconfirm" name="passwordconfirm" class="form-control" />
        </div>
    </div>
    <div class="form-group">
        <label for="address" class="col-sm-4 control-label">Address *</label>
        <div class="col-sm-8">
            <textarea required id="address" name="address" class="form-control" rows="5"></textarea>
        </div>
    </div>
    <strong>Emergency Contact</strong>
    <p>In case of a medical emergency who should we contact on your behalf?</p>
    <div class="form-group">
        <label for="emergency_name" class="col-sm-4 control-label">Full Name</label>
        <div class="col-sm-8">
            <input type="text" id="emergency_name" name="emergency_name" class="form-control" />
        </div>
    </div>
    <div class="form-group">
        <label for="emergency_phone" class="col-sm-4 control-label">Phone number</label>
        <div class="col-sm-8">
            <input type="text" id="emergency_phone" name="emergency_phone" class="form-control" />
        </div>
    </div>
    <div class="form-group">
        <div class="col-sm-offset-4 col-sm-8">
          <input type="submit" name="submit" value="Join" class="btn btn-primary"/>
        </div>
    </div>    
</form>
<? require('footer.php'); ?>
</body>
</html>
