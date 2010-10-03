<? 
$page = 'login';
require('header.php'); 

if ($user) {
    fURL::redirect('/members');
}

if (isset($_POST['submit'])) {
    try {
        $validator = new fValidation();
        $validator->addRequiredFields('password', 'email');
        $validator->addEmailFields('email');

        $validator->validate();
            
        $users = fRecordSet::build('User', array('email=' => $_POST['email']));
        if ($users->count() == 0) {
            throw new fValidationException('No user found with that email.');
        }

        $rec = $users->getRecords();
        $user = $rec[0];
        
        if (!fCryptography::checkPasswordHash($_POST['password'], $user->getPassword())) {
            throw new fValidationException('Invalid Password.');
        }

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
<h2>Log In</h2>
<form method="post">
    <fieldset>
        <table>
            <tr>
                <td><label for="email">Email</label></td>
                <td><input type="text" id="email" name="email"/></td>
            </tr>
            <tr>
                <td><label for="password">Password</label></td>
                <td><input type="password" id="password" name="password" /></td>
            </tr>
            <tr>
                <td colspan="2"><input type="submit" name="submit" value="Log In" /></td>
            </tr>
        </table>
    </fieldset>
</form>
<? require('footer.php'); ?>
