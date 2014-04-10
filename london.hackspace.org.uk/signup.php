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
<h2>Membership</h2>
<p>The London Hackspace is a members-owned non-profit association. Members have a hand in the running of the
organisation as well as 24/7 access to the space.</p>

<p>Membership is paid monthly by standing order. We ask that you pay what you think the space is worth to you. <a href="/cost-of-hacking/">Running an
organisation like this in London isn't cheap</a>, so please be as generous as you can. The minimum subscription is £5/month. The
space requires our members to contribute an average subscription of £20/month to survive.</p>

<h2>Join the London Hackspace</h2>
<p>To become a member of the London Hackspace, we need a few details from you.</p>

<p>By joining the London Hackspace you're becoming a member of London Hackspace Ltd., and you agree to be bound by 
<a href="/organisation/docs/articles.pdf">our constitution</a>. You also agree to follow the
<a href="http://wiki.london.hackspace.org.uk/view/Rules">rules of the space</a>.</p>

<p><a href="http://www.legislation.gov.uk/ukpga/2006/46/part/8/chapter/2/crossheading/general">UK law</a> requires that
you provide your real name and address in order to join. Your name will be visible to all members.</p>

<form class="form-horizontal" method="post" role="form">
    <input type="hidden" name="token" value="<?=fRequest::generateCSRFToken()?>" />
    <div class="form-group">
        <label for="email" class="col-sm-4 control-label">Email</label>
        <div class="col-sm-8">
            <input type="email" required autofocus id="email" name="email" class="form-control" />
        </div>
    </div>
    <div class="form-group">
        <label for="password" class="col-sm-4 control-label">Password</label>
        <div class="col-sm-8">
            <input type="password" required id="password" name="password" class="form-control" />
        </div>
    </div>
    <div class="form-group">
        <label for="password" class="col-sm-4 control-label">Confirm Password</label>
        <div class="col-sm-8">
            <input type="password" required id="passwordconfirm" name="passwordconfirm" class="form-control" />
        </div>
    </div>
    <div class="form-group">
        <label for="fullname" class="col-sm-4 control-label">Full Name</label>
        <div class="col-sm-8">
            <input type="text" required id="fullname" name="fullname" class="form-control" />
        </div>
    </div>
    <div class="form-group">
        <label for="address" class="col-sm-4 control-label">Address</label>
        <div class="col-sm-8">
            <textarea required id="address" name="address" class="form-control" rows="5"></textarea>
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
