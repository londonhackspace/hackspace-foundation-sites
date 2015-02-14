<? 
$page = 'members';
require('../header.php'); 
ensureLogin();

if (!$user->isMember()) {
    fURL::redirect('/members/new_subscription.php');
}

?>
<h2>Membership Status</h2>
    <p>You're currently a member of London Hackspace, thanks for your support!</p>
    <h3>Reference Number</h3>
    <p>Your standing order reference number is: <strong><?=$user->getMemberNumber()?></strong></p>
<h3>Your Recent Payments</h3>
<table>
    <thead>
    <tr>
        <th>Date</th>
        <th>Amount</th>
    </tr>
    </thead>
    <tbody>
    <? foreach($user->buildTransactions() as $transaction) {?>
    <tr>
        <td><?=$transaction->getTimestamp()?></td>
        <td>£<?=$transaction->getAmount()?></td>
    </tr>
    <? } ?>
    </tbody>
</table>
<? require('../footer.php'); ?>
</body>
</html>
