<?
$page = 'new_subscription';
$hide_menu = true;
require('../header.php');
ensureLogin();

if ($user->isMember()) {
    fURL::redirect('/members');
}

?>
<h2>Set up your subscription</h2>
    <p class="alert alert-info">
        You're not currently a member of London Hackspace. To become a member, you need to set up a
        monthly payment below.
    </p>

    <p>We ask that you pay what you think London Hackspace is worth to you. London Hackspace is a
       non-profit organisation which costs over £10,000 each month to run.</p>

    <p><strong>We recommend that you pay £15 or more per month</strong> so we can keep improving the space.
       For those on low incomes, the minimum monthly payment is £5.</p>

<h3>Pay by Direct Debit</h3>
    <p>Our preferred method of payment is by Direct Debit, which will automatically collect the
       membership payment from your bank account every month.</p>

    <form method="POST" action="/payments/gocardless_payment.php">
        <input type="hidden" name="request_token"
                value="<?php echo fRequest::generateCSRFToken('/payments/gocardless_payment.php') ?>">
        <div class="form-group">
            <label for="paymentAmount">Monthly payment</label>
            <div class="input-group">
                <div class="input-group-addon">£</div>
                <input class="form-control" id="paymentAmount" name="paymentAmount" value="15">
            </div>
        </div>
        <button class="btn btn-primary">Pay by Direct Debit</button>
    </form>

    <p>Direct Debit payments are collected by GoCardless and covered by the Direct Debit
       Guarantee. You can cancel your subscription at any time through GoCardless or your bank.</p>

<h3>Other payment methods</h3>
    <p>We also support payment by setting up a standing order with your bank.</p>

    <p><a class="btn btn-default" data-toggle="collapse" data-target="#standingInfo"
            aria-expanded="false" aria-controls="collapseExample">
        Pay by standing order</a>
    </p>

    <div id="standingInfo" class="collapse">
        <div class="well">

    <p>Set up a monthly standing order with your bank (most banks let you do this online),
       using the following details.</p>

    <p><strong>Please carefully check that you have entered the reference provided in the
       reference field of your payment</strong>. Your payment is processed automatically,
        and that can't happen without the correct reference.</p>

<table>
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
</div>
</div>

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

<? require('../footer.php'); ?>
</body>
</html>
