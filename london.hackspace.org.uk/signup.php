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
        $user->setEmail(strtolower($_POST['email']));
        $user->setFullName($_POST['fullname']);
        $user->setAddress($_POST['address']);
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

<form method="post">
    <input type="hidden" name="token" value="<?=fRequest::generateCSRFToken()?>" />
    <fieldset>
        <table id="signuptable">
            <tr>
                <td><label for="email">Email</label></td>
                <td><input type="email" required autofocus id="email" name="email" /></td>
            </tr>
            <tr>
                <td><label for="password">Password</label></td>
                <td><input type="password" required id="password" name="password" /></td>
            </tr>
            <tr>
                <td><label for="passwordconfirm">Confirm Password</label></td>
                <td><input type="password" required id="passwordconfirm" name="passwordconfirm" /></td>
            </tr>
            <tr>
                <td><label for="fullname">Full Name</label></td>
                <td><input type="text" required id="fullname" name="fullname" /></td>
            </tr>
            <tr>
                <td><label for="address">Address</label></td>
                <td><textarea id="address" required name="address" cols="30" rows="5"></textarea></td>
            </tr>
            <tr>
                <td colspan="2"><input type="submit" class="submit" name="submit" value="Submit" /></td>
            </tr>
        </table>
    </fieldset>
</form>
<? require('footer.php'); ?>
