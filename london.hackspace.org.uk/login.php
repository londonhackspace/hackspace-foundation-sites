<? 
$page = 'login';
require('header.php'); 

if ($user) {
    fURL::redirect('/members');
}
?>
<h2>Login</h2>
<?php
if (isset($_POST['submit'])) {
    try {
        fRequest::validateCSRFToken($_POST['token']);

        $validator = new fValidation();
        $validator->addRequiredFields('password', 'email');
        $validator->addEmailFields('email');

        $validator->validate();

        $users = fRecordSet::build('User', array('email=' => strtolower($_POST['email'])));
        if ($users->count() == 0) {
            throw new fValidationException('Invalid username or password.');
        }

        $rec = $users->getRecords();
        $user = $rec[0];

        if (!fCryptography::checkPasswordHash($_POST['password'], $user->getPassword())) {
            throw new fValidationException('Invalid username or password.');
        }

        fSession::set('user', $user->getId());

        if (fRequest::get('persistent_login', 'boolean')) {
            fSession::enablePersistence();
        }

        if (isset($_POST['forward'])) {
            fURL::redirect('http://' . $_SERVER['SERVER_NAME'] . $_POST['forward']);
        } else {
            fURL::redirect('/members');
        }
        exit;
    } catch (fValidationException $e) {
        echo "<p>" . $e->printMessage() . "</p>";
    } catch (fSQLException $e) {
        echo "<p>An unexpected error occurred, please try again later</p>";
        trigger_error($e);
    }
}

?>
<form class="form-horizontal" method="post" role="form">
    <input type="hidden" name="token" value="<?=fRequest::generateCSRFToken()?>" />
    <? if (isset($_GET['forward'])) { ?>
        <input type="hidden" name="forward" value="<?=htmlentities($_GET['forward'])?>" />
    <? }?>
    <div class="form-group">
        <label for="email" class="col-sm-2 control-label">Email</label>
        <div class="col-sm-10">
            <input type="email" required autofocus id="email" name="email" class="form-control" />
        </div>
    </div>
    <div class="form-group">
        <label for="password" class="col-sm-2 control-label">Password</label>
        <div class="col-sm-10">
            <input type="password" required id="password" name="password" class="form-control" />
        </div>
    </div>
    <div class="form-group">
        <div class="col-sm-offset-2 col-sm-10">
          <div class="checkbox">
            <label>
                <input type="checkbox" value="true" name="persistent_login" /> Remember me
            </label>
          </div>
        </div>
    </div>
    <div class="form-group">
        <div class="col-sm-offset-2 col-sm-10">
          <input type="submit" name="submit" value="Log In" class="btn btn-primary"/>
        </div>
    </div>
</form>

<p>Forgotten your password? <a href="passwordreset.php">Reset it here</a>.</p>

<? require('footer.php'); ?>
</body>
</html>
