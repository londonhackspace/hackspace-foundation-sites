<?
$page = 'cards';
$title = 'Card access';
$desc = '';
require('../header.php');

if (!isset($user)) {
    fURL::redirect('/login.php?forward=/members/cards.php');
}

if (isset($_POST['update_nick'])) {
    try {
        fRequest::validateCSRFToken($_POST['token']);
        $user->setNickname($_POST['nickname']);
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

<h2>Card access</h2>
<?
if($user->isMember()) {
?>

<h3>Your Authorised Cards</h3>

<p>As a member, you may authorise your RFID card for 24/7 access to the space and equipment. You can also set a nickname to be announced to the space instead of your full name.</p>

<p>Any 13.56 MHz device will work, including Oyster cards. The system will only read the unique identifier from it.</p>

<form method="POST">
    <input type="hidden" name="token" value="<?=fRequest::generateCSRFToken()?>" />
    <input type="hidden" name="update_card" value="" />
    <table>
        <tr>
            <th>Date added</th>
            <th style="text-align: center">Card ID</th>
            <th>Enabled</th>
        </tr>
        <? foreach($user->buildCards() as $card): ?>
        <tr>
            <td><?=$card->getAddedDate()?></td>
            <td><?=$card->getUid()?></td>
            <td style="text-align: center">
                <? if ($card->getActive()): ?>
                <input type="submit" name="disable_<?=$card->getUid()?>" value="Yes" />
                <? else: ?>
                <input type="submit" name="enable_<?=$card->getUid()?>" value="No" />
                <? endif; ?>
            </td>
        </tr>
        <? endforeach ?>
    </table>
</form>

<form method="POST">
    <input type="hidden" name="token" value="<?=fRequest::generateCSRFToken()?>" />
    <input type="hidden" name="update_nick" value="" />
    <table>
        <tr>
            <td><label for="nickname">Nickname:</label></td>
            <td><input type="text" id="nickname" name="nickname" value="<?php echo $user->getNickname() ?>" /></td>
        </tr>
        <tr>
            <td colspan="2"><input type="submit" name="submit" value="Save" /></td>
        </tr>
    </table>
</form>

<? if (isset($_GET['saved'])) {
  echo "<p>Details saved.</p>";
} ?>
<p><a href="index.php">Return to membership home</a></p>


<? } else { ?>
   <p>You must be a member to use this page.</p>
<?php }

require('../footer.php'); ?>