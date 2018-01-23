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
    <h3>Reference Number</h3>
    <p>Your standing order reference number is: <strong><?=$user->getMemberNumber()?></strong></p>
<? } else { ?>
    <p>You're not currently a member of London Hackspace. To become a member, we ask that you pay what you
       think the space is worth to you. Running a place like this isn't cheap, so please be as
       generous as you can. The Hackspace costs around £15/month per member to run, but for students, 
       retirees or low income members the minimum subscription is £5/month.</p>

<h3>Standing Order</h3>
    <p>Set up a monthly standing order with your bank (most banks let you do this online),
       using the following details.</p>

    <p><strong>Please carefully check that you have entered the reference provided in the
       reference field of your payment</strong>. Your payment is processed automatically,
        and that can't happen without the correct reference.</p>

<table class="table">
    <tr>
        <th>Bank</th>
        <td>Barclays</td>
    </tr>
    <tr>
        <th>Payee</th>
        <td>London Hackspace Ltd.</td>
    </tr>
    <tr>
        <th>Sort Code</th>
        <td>20-32-06</td>
    </tr>
    <tr>
        <th>Account Number</th>
        <td>53413292</td>
    </tr>
    <tr>
        <th>Reference</th>
        <td style="font-family:monospace"><?=$user->getMemberNumber()?></td>
    </tr>
</table>

<p>Don't worry if the name doesn't fit in full.</p>

    <p>Once the payment has left your account, it can take up to four working days to be reflected here.</p>

<h3>Can't do Standing Order?</h3>

  <p>Currently, the only way to be recognised as a member is for your membership payment to appear in our bank account
      with the correct payment reference. If you can't do this by standing order, you can send a one-off
      bank transfer with the same details, or bring these details to any Barclays branch and pay in cash.
      Please make sure that the reference is correct; bank cashiers frequently get it wrong.</p>

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
