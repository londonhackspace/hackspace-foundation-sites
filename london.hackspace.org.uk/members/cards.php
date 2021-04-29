<?
$page = 'cards';
$title = 'Card access';
$desc = '';
require '../header.php';

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
        foreach ($user->buildCards() as $card) {
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

<h2>Card access</h2>

<h3>Your Authorised Cards</h3>

<p>As a member, you may authorise your RFID card for 24/7 access to the space and equipment. You can also set a nickname or audio file to be announced to the space instead of your full name.</p>

<p>Any 13.56 MHz device will work, including Oyster cards. The system will only read the unique identifier from it.</p>

<p>The Wiki has more information on how to <a href="http://wiki.london.hackspace.org.uk/view/Door_control_system">add your RFID to the door access system</a>.</p>

<form method="POST">
    <input type="hidden" name="token" value="<?php echo fRequest::generateCSRFToken()?>" />
    <input type="hidden" name="update_card" value="" />
    <table class="card-management">
        <thead>
        <tr>
            <th>Date added</th>
            <th style="text-align: center">Card ID</th>
            <th>Permission</th>
            <th>Delete</th>
        </tr>
        </thead>
        <tbody>
        <?foreach ($user->buildCards() as $card): ?>
        <tr class="<?php echo $card->getActive() ? 'allowed' : 'blocked'?>">
            <td><?php echo $card->getAddedDate()?></td>
            <td><?php echo $card->getUid()?></td>
            <td style="text-align: center">
                <?if ($card->getActive()): ?>
                <input class="btn btn-default" type="submit" name="disable_<?php echo $card->getUid()?>" value="Block" title="This card is currently allowed. Click to block it." />
                <?else: ?>
                <input class="btn btn-default" type="submit" name="enable_<?php echo $card->getUid()?>" value="Allow" title="This card is currently blocked. Click to allow it." />
                <?endif;?>
            </td>
            <td style="text-align: center">
                <input class="btn btn-default" type="submit" name="delete_<?php echo $card->getUid()?>" value="Delete" title="Click to delete this card." <?if ($card->getActive()) {?>disabled<?}?> />
            </td>
        </tr>
        <?endforeach?>
        </tbody>
    </table>
</form>
<br/>

<?if (isset($_GET['saved'])) {
    echo "<div class=\"alert alert-success\"><p>Details saved.</p></div>";
}?>

<p><a href="addcard.php">add a card by RFID unique id</a>.</p>

<form class="form-horizontal" method="post" role="form">
    <input type="hidden" name="token" value="<?php echo fRequest::generateCSRFToken()?>" />
    <input type="hidden" name="update_details" value="" />
    <div class="form-group">
        <label for="nickname" class="col-sm-3 control-label">Nickname</label>
        <div class="col-sm-9">
            <input type="text" required id="nickname" name="nickname" class="form-control" value="<?php echo $user->getNickname() ?>" />
        </div>
    </div>
    <div class="form-group">
        <label for="gladosfile" class="col-sm-3 control-label">Glados file</label>
        <div class="col-sm-9">
            <input type="text" id="gladosfile" name="gladosfile" class="form-control" value="<?php echo $user->getGladosfile() ?>" />
        </div>
    </div>
    <div class="form-group">
        <div class="col-sm-offset-3 col-sm-9">
          <input type="submit" name="submit" value="Save" class="btn btn-primary"/>
        </div>
    </div>
</form>

<?php require '../footer.php';?>
</body>
</html>
