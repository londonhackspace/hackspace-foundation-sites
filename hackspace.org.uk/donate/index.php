<? 
$page = 'donate';
require('../header.php'); ?>
<h2>Donate</h2>
<p>The Hackspace Foundation is a non-profit organisation which gladly accepts donations.
    If you're planning on using our spaces regularly or contributing to the Foundation you should also
    <a href="/membership.php">become a member</a>.
</p>

<p>There are two ways to donate to the Hackspace Foundation. We prefer bank transfer, as we don't have to pay commission.</p>

<h3>Bank Transfer (BACS/FPS)</h3>
<p>Our bank details are as follows:</p>
<table>
    <tr><th>Bank</th><td>Barclays</td></tr>
    <tr><th>Payee</th><td>Hackspace Foundation</td></tr>
    <tr><th>Sort Code</th><td>20-32-06</td></tr>
    <tr><th>Account Number</th><td>53413292</td></tr>
</table>

<h3>Paypal</h3>
<p>
<form action="https://www.paypal.com/cgi-bin/webscr" method="post">
<input type="hidden" name="cmd" value="_s-xclick">
<input type="hidden" name="hosted_button_id" value="3741369">
<input type="image" src="https://www.paypal.com/en_GB/i/btn/btn_donateCC_LG.gif" border="0" name="submit" alt="PayPal - The safer, easier way to pay online.">
<img alt="" border="0" src="https://www.paypal.com/en_GB/i/scr/pixel.gif" width="1" height="1">
</form>
</p>

<? require('../footer.php'); ?>
