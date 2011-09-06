<?
$page = 'cards';
$title = 'Add card';
$desc = '';
require('../header.php');

if (!isset($user)) {
    fURL::redirect('/login.php?forward=/members/cards.php');
}

if (isset($_POST['submit'])) {
    try {
        fRequest::validateCSRFToken($_POST['token']);
        $card = new Card();
        $card->setUserId($user->getId());
        $card->setAddedDate(time());
        $card->setUid($_POST['uid']);
        $card->store();
        fURL::redirect('/members/cards.php');
        exit;
    } catch (fValidationException $e) {
        echo "<p>" . $e->printMessage() . "</p>";
    } catch (fSQLException $e) {
        echo "<p>An unexpected error occurred, please try again later</p>";
        trigger_error($e);
    }
}
?>

<h2>Add card</h2>
<?
if($user->isMember()) {
?>
<form method="POST">
    <input type="hidden" name="token" value="<?=fRequest::generateCSRFToken()?>" />
    <table>
        <tr>
            <td><label for="uid">Card ID: </label></td>
            <td><input type="text" name="uid" value="" /></td>
        </tr>
        <tr>
            <td colspan="2"><input type="submit" name="submit" value="Add" /></td>
        </tr>
    </table>
</form>
<? } else { ?>
<p>You must be a member to use this page.</p>
<?php }

require('../footer.php'); ?>