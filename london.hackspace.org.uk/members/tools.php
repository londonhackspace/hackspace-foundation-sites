<?
$page = 'tools';
$title = 'Tool access';
$desc = '';
require('../header.php');

global $ACSERVER_ADDRESS;
global $ACSERVER_KEY;

//print_r($ACSERVER_ADDRESS);
//print_r($ACSERVER_KEY);

if (!isset($user)) {
    fURL::redirect('/login.php?forward=/members/signup.php');
}


if (isset($_POST['update_details'])) {
    try {
        fRequest::validateCSRFToken($_POST['token']);
        $user->setNickname($_POST['nickname']);
        $user->setGladosfile($_POST['gladosfile']);
        $user->store();
        fURL::redirect('?saved');
        exit;
    } catch (fValidationException $e) {
        echo "<p>" . $e->printMessage() . "</p>";
    } catch (fSQLException $e) {
        echo "<p>An unexpected error occurred, please try again later</p>";
        trigger_error($e);
    }

} elseif (isset($_POST['update_card'])) {
    try {
        fRequest::validateCSRFToken($_POST['token']);
        foreach($user->buildCards() as $card) {
            if (isset($_POST['enable_' . $card->getUid()])) {
                $card->setActive(1);
                $card->store();
            } else if (isset($_POST['disable_' . $card->getUid()])) {
                $card->setActive(0);
                $card->store();
            } else if (isset($_POST['delete_' . $card->getUid()]) && !$card->getActive()) {
                $card->delete();
                $card->store();
            }
        }
        fURL::redirect();
        exit;
    } catch (fValidationException $e) {
        echo "<p>" . $e->printMessage() . "</p>";
    } catch (fSQLException $e) {
        echo "<p>An unexpected error occurred, please try again later</p>";
        trigger_error($e);
    }
}
?>

<h2>Tool access</h2>


<p>The Wiki has more information on how to <a href="http://wiki.london.hackspace.org.uk/view/Door_control_system">add your RFID to the door access system</a>.</p>

<form method="POST">
    <input type="hidden" name="token" value="<?=fRequest::generateCSRFToken()?>" />
    <input type="hidden" name="update_card" value="" />
    
</form>
<br/>

<? if (isset($_GET['saved'])) {
  echo "<div class=\"alert alert-success\"><p>Details saved.</p></div>";
} ?>

<?php require('../footer.php'); ?>
</body>
</html>
