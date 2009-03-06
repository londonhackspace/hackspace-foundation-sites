<? 
$page = 'members';
require('../header.php'); 
?>
<h2>Members Area</h2>

<? if($user->isMember()) { ?>
    <p>You're currently a member of the Hackspace Foundation.</p>

<? } else { ?>
    <p>You're not currently a member of the Hackspace Foundation.
        Membership is a recommended donation of £10 per month, with a 
        minimum of £5 per month. To become a member, you have two options:
        We'd much prefer you set up a standing order, but you can also 
        pay with PayPal, although it costs us a bit more money in fees and hassle.</p>

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

    <p>Once your payment is set up, your payment will show up on the site once we reconcile 
        our statements &mdash; this usually happens at least twice a month.</p>

<h3>PayPal</h3>


<? } ?>

<? require('../footer.php'); ?>
