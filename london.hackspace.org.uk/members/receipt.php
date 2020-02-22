<?
require('../header-minimal.php');

if (!$user) {
    fURL::redirect('/login.php?forward=/members/receipt.php');
}

if (isset($_GET['userid']) && $user->isAdmin()) {
    $user = new User($_GET['userid']);
}

$hide_copyright = true;

$from = new fDate('now - 1 year');
$to = new fDate('now');

if ($_GET['from']) {
  $from = new fDate($_GET['from']);
}

if ($_GET['to']) {
  $to = new fDate($_GET['to']);
}

$transactions = $user->buildPayments($from, $to);

$minimum = 5;

$total_membership = 0;
$total_donations = 0;
$count = 0;

?>
<div id="bd">
<div id="non-menu-content" class="grid_10">
<div style="padding-top: 1em; padding-bottom:2em; float:left; width:100%;">
  <div style="float:left">London Hackspace Ltd.<br>
    388 High Road<br>
    Wembley<br>
    London<br>
    HA9 6AR<br>
  </div>

  <div style="float:right">
  <span style="font-weight: bold; font-size: 2em;padding">Membership Receipt</span>
  <p style="text-align:right">Account: <strong><?=$user->getEmail()?></strong></p>
  </div>
</div>

<div style="clear:both; margin-top: 4em;">
  <form action="" method="get" class="hidden-print form-inline">
    <label>From:</label>  <input class="input-small" type="text" name="from" value="<?=$from?>">
    <label>To:</label>  <input class="input-small" type="text" name="to" value="<?=$to?>">
    <button class="btn">Generate</button>
  </form>
</div>

<table class="table" style="margin:2em;">
<thead>
    <tr><th>Date</th><th>Membership Payment</th><th>Donation</th><th>Total</th></tr>
</thead>
<? foreach($transactions as $transaction) {
  if($transaction->getPaymentState() != 2) {
    // not a cleared payment
    continue;
  }
  $amount = $transaction->getAmount();
  $mem_amount = min($amount, $minimum);
  $total_membership += $mem_amount;
  $donation = max($amount - $mem_amount, 0);
  $total_donations += $donation;
  $count++;
?>
    <tr><td><?=$transaction->getTimestamp()?></td>
        <td>£<?=number_format($mem_amount, 2)?></td>
        <td>£<?=number_format($donation, 2)?></td>
        <td>£<?=number_format($mem_amount + $donation, 2)?></td>
    </tr>
<?
}
?>
</table>
<h3>Total</h3>
<table class="table" style="margin:2em;">
<tr>
  <th>Membership subscriptions (up to £<?=number_format($minimum, 2)?> per month):</th><td>£<?=number_format($total_membership, 2)?></td></tr>
  <th>Donations (payments above minimum):</th><td>£<?=number_format($total_donations, 2)?></td></tr>
  <th>Total paid:</th><td>£<?=number_format($total_membership + $total_donations, 2)?></td>
</table>

<footer>London Hackspace Ltd is a company limited by guarantee in England and Wales with number 06807563.<br>
No VAT is included as London Hackspace is not VAT registered.<br>
London Hackspace is not a registered charity. Your donations are not eligible for gift aid.
</footer>

<? require('../footer.php'); ?>
</body>
</html>
