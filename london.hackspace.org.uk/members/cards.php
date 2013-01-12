<?
$page = 'cards';
$title = 'Card access';
$desc = '';
require('../header.php');

if (!isset($user)) {
    fURL::redirect('/login.php?forward=/members/cards.php');
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

<h3>Your Authorised Cards</h3>

<p>As a member, you may authorise your RFID card for 24/7 access to the space and equipment. You can also set a nickname or audio file to be announced to the space instead of your full name.</p>

<p>Any 13.56 MHz device will work, including Oyster cards. The system will only read the unique identifier from it.</p>

<p>The Wiki has more information on how to <a href="http://wiki.london.hackspace.org.uk/view/Door_control_system">add your RFID to the door access system</a>.</p>

<form method="POST">
    <input type="hidden" name="token" value="<?=fRequest::generateCSRFToken()?>" />
    <input type="hidden" name="update_card" value="" />
    <table>
        <thead>
        <tr>
            <th>Date added</th>
            <th style="text-align: center">Card ID</th>
            <th>Active</th>
        </tr>
        </thead>
        <tbody>
        <? foreach($user->buildCards() as $card): ?>
        <tr>
            <td><?=$card->getAddedDate()?></td>
            <td><?=$card->getUid()?></td>
            <td style="text-align: center">
                <? if ($card->getActive()): ?>
                <input type="submit" name="disable_<?=$card->getUid()?>" value="Disable" title="This card is currently enabled. Click to disable it." />
                <? else: ?>
                <input type="submit" name="enable_<?=$card->getUid()?>" value="Enable" title="This card is currently disabled. Click to enable it." />
                <? endif; ?>
            </td>
        </tr>
        <? endforeach ?>
        </tbody>
    </table>
</form>

<form method="POST">
    <input type="hidden" name="token" value="<?=fRequest::generateCSRFToken()?>" />
    <input type="hidden" name="update_details" value="" />
    <table>
        <tr>
            <td><label for="nickname">Nickname:</label></td>
            <td><input type="text" id="nickname" name="nickname" size="10" value="<?php echo $user->getNickname() ?>" /></td>
        </tr>
        <tr>
            <td><label for="gladosfile">Glados file:</label></td>
            <td><input type="text" id="gladosfile" name="gladosfile" size="20" value="<?php echo $user->getGladosfile() ?>" /></td>
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


<?php require('../footer.php'); ?>
