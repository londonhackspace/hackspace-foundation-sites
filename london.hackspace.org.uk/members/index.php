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
    <p>You're currently a member of London Hackspace, thanks for your support!</p>
    <h3>GoCardless</h3>
    <?
        // see if the user has actually submitted a payment via gocardless
        $hasgcpayment = false;
        foreach($user->buildPayments() as $payment) {
            if ($payment->getPaymentType() == 2) {
                $hasgcpayment = true;
            }
        }
        if ($hasgcpayment) {
    ?>
    You're using GoCardless to subscribe.
    <? } else { ?>
        <p>We are now moving to GoCardless to handle membership payments. To manage your subscription, please <a href="/gocardless/">click here</a>.</p>
    <? } ?>
<? } else { ?>
    <p>You're not currently a member of London Hackspace. To become a member, we ask that you pay what you
       think the space is worth to you. Running a place like this isn't cheap, so please be as
       generous as you can. The Hackspace requires a minimum of <a href='/cost-of-hacking/'>£15/month</a> per member to run.</p>

<h3>GoCardless </h3>
<p>We are now using GoCardless to handle membership payments. To manage your subscription, please <a href="/gocardless/">click here</a>.</p>

<h3>Tell us about yourself</h3>

<p>While you're waiting for your payment to process, why don't you tell us a bit about yourself? <a href="/members/profile_edit.php">Create a profile</a> to help other members get to know you better. When your payment has been received you'll be able to see other member's profiles too.

<h3>Any problems?</h3>
   <p>If you have any problems paying, please contact 
      <a href="mailto:membership@london.hackspace.org.uk">membership@london.hackspace.org.uk</a>.</p>

<? } ?>

<h3>Your Recent Payments</h3>
<table class="table">
    <thead>
    <tr>
        <th>Date</th>
        <th>Amount</th>
        <th>Type</th>
    </tr>
    </thead>
    <tbody>
    <? foreach($user->buildPayments() as $payment) {?>
    <tr <?php if($payment->getPaymentState() == 3) { ?>style="background-color: lightgrey"<?php } ?>>
        
        <td><?=$payment->getTimestamp()?></td>
        <td>£<?=$payment->getAmount()?></td>
        <td><?php if($payment->getPaymentType() == 1) { ?>Bank<?php } else { ?> GoCardless <?php } if($payment->getPaymentState() == 3) { ?> (Failed) <?php } 
                else if($payment->getPaymentState() == 1) { ?> (Pending) <?php } ?>
    </tr>
    <? } ?>
    </tbody>
</table>

<? require('../footer.php'); ?>
</body>
</html>
