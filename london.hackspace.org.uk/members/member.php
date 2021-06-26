<? 
$page = 'memberdetail';
require( '../header.php' );

if (!isset($user)) {
    fURL::redirect('/login.php?forward=/members/members.php');
}

try {
  $this_user = new User($_GET['id']);
} catch(fNotFoundException $e) {
  header('HTTP/1.1 404 Not Found');
  echo "User not found";
  require('../footer.php');
  echo "</body></html>";
  exit;
}

?>
<?
if(($user->isMember() && $this_user->isMember()) || $user->isAdmin() ) {
?>
<h2>Member Info</h2>
  <table>
    <tr><th>Name</th><td><?=htmlspecialchars($this_user->getFullName())?></td></tr>
    <tr><th>ID</th><td><?=$this_user->getMemberNumber()?></td></tr>
    <?if($this_user->getHasProfile()) { ?>
    <tr><th>Profile</th><td><a href="/members/profile.php?id=<?=$this_user->getId()?>">Visit member's profile</a></td></tr>
    <?}?>
    <?if ($user->isAdmin()) { ?>
    <tr><th>Member?</th><td><?=($this_user->isMember())?"Yes":"No"?></td></tr>
    <tr><th>Admin?</th><td><?=($this_user->isAdmin())?"Yes":"No"?></td></tr>
    <tr><th>Email</th><td><?=$this_user->getEmail()?></td></tr>
    <tr><th>Address</th><td><?=nl2br($this_user->getAddress())?></td></tr>
    <tr><th>Emergency Contact</th><td><?=htmlspecialchars($this_user->getEmergencyName())?><br/><?=htmlspecialchars($this_user->getEmergencyPhone())?></td></tr>
    <?}?>
  </table>

  <?if ($user->isAdmin()) { ?>
    <h3>Access Cards</h3>
    <table class="table">
    <thead>
    <tr>
        <th>Date added</th>
        <th>Card ID</th>
        <th style="text-align: center">Permission</th>
    </tr>
    </thead>
    <tbody>
    <? foreach($this_user->buildCards() as $card): ?>
        <tr class="<?=$card->getActive() ? 'allowed' : 'blocked' ?>">
            <td><?=$card->getAddedDate()?></td>
            <td><?=implode(str_split($card->getUid(),2),':')?></td>
            <? if ($card->getActive()): ?>
              <td class="rfidcard  rfidcard-active">Active</td>
            <? else: ?>
              <td class="rfidcard rfidcard-blocked">Blocked</td>
            <? endif; ?>
        </tr>
    <? endforeach ?>
    </tbody>
    </table>
  <?}?>

  <?if ($user->isAdmin()) { ?>
    <h3>Recent Payments</h3>
    <table class="table">
    <thead>
    <tr>
        <th>Date</th>
        <th>Amount</th>
        <th>Type</th>
    </tr>
    </thead>
    <tbody>
    <? foreach($this_user->buildPayments() as $payment) {?>
    <tr <?php if($payment->getPaymentState() == 3) { ?>style="background-color: lightgrey"<?php } ?>>
        
        <td><?=$payment->getTimestamp()?></td>
        <td>£<?=$payment->getAmount()?></td>
        <td><?php if($payment->getPaymentType() == 1) { ?>Bank<?php } else { ?> GoCardless <?php } if($payment->getPaymentState() == 3) { ?> (Failed) <?php } 
              else if($payment->getPaymentState() == 1) { ?> (Pending) <?php } ?>
    </tr>
    <? } ?>
    </tbody>
</table>
  <?}?>
<? } else { ?>
   <p>You don't have access to this page.</p>
<?php } 
require('../footer.php'); ?>
</body>
</html>
