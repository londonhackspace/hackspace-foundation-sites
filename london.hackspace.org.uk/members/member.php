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
    <tr><th>Name</th><td><?=$this_user->getFullName()?></td></tr>
    <tr><th>ID</th><td><?=$this_user->getMemberNumber()?></td></tr>
    <?if ($user->isAdmin()) { ?>
    <tr><th>Member?</th><td><?=($this_user->isMember())?"Yes":"No"?></td></tr>
    <tr><th>Admin?</th><td><?=($this_user->isAdmin())?"Yes":"No"?></td></tr>
    <tr><th>Email</th><td><?=$this_user->getEmail()?></td></tr>
    <tr><th>Address</th><td><?=nl2br($this_user->getAddress())?></td></tr>
    <?}?>
  </table>

  <?if ($user->isAdmin()) { ?>
    <h3>Recent Payments</h3>
    <table>
        <thead>
        <tr>
            <th>Date</th>
            <th>Amount</th>
            <th>FIT ID</th>
        </tr>
        </thead>
        <tbody>
        <? foreach($this_user->buildTransactions() as $transaction) {?>
        <tr>
            <td><?=$transaction->getTimestamp()?></td>
            <td>£<?=$transaction->getAmount()?></td>
            <td><?=$transaction->getFitId()?></td>
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
