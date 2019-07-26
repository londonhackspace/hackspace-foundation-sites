<?
$page = 'cards';
$title = 'Add card';
$desc = '';
require('../header.php');

if (!isset($user)) {
    fURL::redirect('/login.php?forward=/members/addcard.php');
}

if (isset($_POST['submit'])) {
    try {
        fRequest::validateCSRFToken($_POST['token']);

        $validator = new fValidation();
        $validator->addRequiredFields('uid');
        $validator->addRegexRule('uid', '#^[0-9a-fA-F]+$#', 'Not in hex format');

        $validator->validate();

        $uid = strtoupper($_POST['uid']);
        validateCardUIDUsable($uid);

        $card = new Card();
        $card->setUserId($user->getId());
        $card->setAddedDate(time());
        $card->setUid($uid);
        $card->store();
        fURL::redirect('/members/cards.php');
        // Now we could trigger acserver to re-read carddb
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

<?php require('../footer.php'); ?>
</body>
</html>
