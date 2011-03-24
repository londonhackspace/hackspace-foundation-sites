<? 
$page = 'members';
require('../header.php'); 

if (!$user) {
    fURL::redirect('/login.php?forward=/members');
}
?>
<h2>Members Area</h2>

<? if ($user->getAddress() == '') {?>
    <h4>More Details Required</h4>
    <p>UK Law requires that we store our members' real name and address. Since you haven't provided
        these details you will be unable to gain membership privileges until you do.</p>
    <p>Please <a href="/members/edit.php">provide your details</a> to continue.</p>
<?} else if ($user->isMember()) { ?>
    <p>You're currently a member of the Hackspace Foundation, thanks for your support!</p>
<h3>Your Recent Payments</h3>
<table>
    <tr><th>Date</th><th>Amount</th></tr>
<? foreach($user->buildTransactions() as $transaction) {?>
    <tr><td><?=$transaction->getTimestamp()?></td><td>£<?=$transaction->getAmount()?></td></tr>
<? } ?>
</table>

<h3>Standing Order</h3>
    <p>Thank you for setting up a standing order with your bank. If you ever need to change your standing order in 
      the future please use the details provided below.</p>

<table>
    <tr><th>Bank</th><td>Barclays</td></tr>
    <tr><th>Payee</th><td>Hackspace Foundation</td></tr>
    <tr><th>Sort Code</th><td>20-32-06</td></tr>
    <tr><th>Account Number</th><td>53413292</td></tr>
    <tr><th>Reference</th><td><?=$user->getMemberNumber()?></td></tr>
</table>

<h3>Tools</h3>
    <ul>
        <li><a href="/webcams.html">Webcams</a> (<a href="http://lhs.tormentedtechnologies.com/">Timelapses</a>)</li>
        <li><a href="wiki.php">Link Wiki Accounts</a></li>
    </ul>

<? } else { ?>
    <p>You're not currently a member of the Hackspace Foundation. To become a member, we ask that you pay what you
       think the space is worth to you. Running an organisation like this in London isn't cheap, so please be as
         generous as you can. The minimum subscription is £5/month.</p>
</p>

<h3>Standing Order</h3>
    <p>Set up a monthly standing order with your bank (most banks let you do this online),
       using the following details. Please make sure you enter the reference provided in the
        reference field of your payment, or your subscription will not be recognised.</p>

<table>
    <tr><th>Bank</th><td>Barclays</td></tr>
    <tr><th>Payee</th><td>Hackspace Foundation</td></tr>
    <tr><th>Sort Code</th><td>20-32-06</td></tr>
    <tr><th>Account Number</th><td>53413292</td></tr>
    <tr><th>Reference</th><td><?=$user->getMemberNumber()?></td></tr>
</table>

    <p>Once your payment is set up, the site should reflect it once we reconcile 
        our statements &mdash; this happens approximately daily.</p>

<h3>Can't do Standing Order?</h3>
<p>In our experience standing order is an almost universal payment method in the UK.
    It's the only method which is completely free for both you and us. Consequently, we don't accept
    payment for membership by any other method.</p>

<p> If you genuinely aren't able to use a standing
    order to pay, please drop <a href="mailto:russ@hackspace.org.uk">Russ</a> an email to let us
    know why.</p>

<? } ?>

<? require('../footer.php'); ?>
