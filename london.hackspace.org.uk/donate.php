<? 
$page = 'donate';
require( './header.php' );
?>

<h2>Donate</h2>
<p>Here's how to donate to London Hackspace. We prefer bank transfer, as we don't have to pay commission.
    Since we're not a registered charity yet, you unfortunately can't claim Gift Aid back on donations to us.</p>

<h3>Bank Transfer</h3>
<? if ($user) { ?>
<p>Our bank details are as follows:</p>
<table>
    <tr><th>Bank</th><td>Barclays</td></tr>
    <tr><th>Payee</th><td>London Hackspace Ltd.</td></tr>
    <tr><th>Sort Code</th><td>20-32-06</td></tr>
    <tr><th>Account Number</th><td>53413292</td></tr>
</table>
<? } else { ?>
<p>Due to direct debit fraud, we require you to <a href="/login.php">log in</a> before you can see our bank details.</p>
<? } ?>

<h3>Paypal</h3>
<p>
<form action="https://www.paypal.com/cgi-bin/webscr" method="post">
<input type="hidden" name="cmd" value="_s-xclick">
<input type="hidden" name="hosted_button_id" value="3741369">
<input type="image" src="https://www.paypal.com/en_GB/i/btn/btn_donateCC_LG.gif" border="0" name="submit" alt="PayPal - The safer, easier way to pay online.">
<img alt="" border="0" src="https://www.paypal.com/en_GB/i/scr/pixel.gif" width="1" height="1">
</form>
</p>

<h3>Bitcoin</h3>
<p>Our Bitcoin address is <strong>1aiBPLdHEW8wu9WpufdBT7kpJRCkGR9P1</strong>.</p>

<?php require('./footer.php'); ?>
