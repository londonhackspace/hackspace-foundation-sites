<? 
$page = 'membership';
require('../header.php'); 

if (!$user) {
    fURL::redirect('/');
}
?>
<h2>Members Area</h2>

<? if($user->isMember()) { ?>
    <p>You're currently a member of the Hackspace Foundation, thanks for your support!</p>
<h3>Your Recent Payments</h3>
<table>
    <tr><th>Date</th><th>Amount</th></tr>
<? foreach($user->buildTransactions() as $transaction) {?>
    <tr><td><?=$transaction->getTimestamp()?></td><td>£<?=$transaction->getAmount()?></td></tr>
<? } ?>
</table>
<? } else { ?>
    <p>You're not currently a member of the Hackspace Foundation.
        Membership is a recommended donation of £20 per month, with a 
        minimum of £5 per month. To become a member, you can pay by 
        standing order.</p>

<h3>Standing Order</h3>
    <p>Set up a monthly standing order with your bank (most banks let you do this online), 
       using the following details:</p>

<table>
    <tr><th>Bank</th><td>Barclays</td></tr>
    <tr><th>Payee</th><td>Hackspace Foundation</td></tr>
    <tr><th>Sort Code</th><td>20-32-06</td></tr>
    <tr><th>Account Number</th><td>53413292</td></tr>
    <tr><th>Reference</th><td><?=$user->getMemberNumber()?></td></tr>
</table>

    <p>Once your payment is set up, the site should reflect it once we reconcile 
        our statements &mdash; this should happen at least twice a month.</p>

<h3>Can't do Standing Order?</h3>
<p>If you aren't able to use a standing order to pay, please contact us at 
    <a href="mailto:russ@hackspace.org.uk">russ@hackspace.org.uk</a> and we'll do our best to
    sort something out for you.
</p>

<? } ?>

<? require('../footer.php'); ?>
