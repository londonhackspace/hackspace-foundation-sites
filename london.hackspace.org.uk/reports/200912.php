<? 
$page = 'reports';
$title = 'December 2009';
require('../header.php'); 
?>
<h2>Hackspace Foundation report &mdash; <?=$title?></h2>

<p>Users stats not available this month because I forgot to collect them.</p>

<h3>Income Statement</h3>
<table class="financial">
    <tr><th colspan="2">Revenues</th></tr>
    <tr><td class="sub">Donations</td><td class="amount">£132.96</td></tr>
    <tr><td class="sub">Interest</td><td class="amount">£0.14</td></tr>
    <tr><td class="sub">Subscriptions</td><td class="amount">£735.00</td></tr>
    <tr><td>Total Revenue</td><td class="amount">£868.10</td></tr>
    <tr><th colspan="2">Expenses</th></tr>
    <tr><td class="sub">Entertainment</td><td class="amount">£66.62</td></tr>
    <tr><td class="sub">Rent</td><td class="amount">£420.00</td></tr>
    <tr><td>Total Expenses</td><td class="amount">£486.62</td></tr>
    <tr><th>Net Income</th><td class="amount">£381.48</td></tr>
    <tr></tr>
</table>

<h3>Balance Sheet</h3>
<table class="financial">
    <tr><th colspan="2">Assets</th></tr>
    <tr><th colspan="2" class="sub">Current Assets</th></tr>
    <tr><td class="sub2">Petty Cash</td><td class="amount">£132.96</td></tr>
    <tr><td class="sub2">Cash in Bank</td><td class="amount">£3,655.61</td></tr>
    <tr><td class="sub">Fixed Assets</td><td class="amount">£758.90</td></tr>
    <tr><td>Total Assets</td><td class="amount">£4,547.47</td></tr>
    <tr><th colspan="2">Liabilities</th></tr>
    <tr><td class="sub2">Donations (Leeds)</td><td class="amount">£726.90</td></tr>
    <tr><td>Total Liabilities</td><td class="amount">£726.90</td></tr>
    <tr><th colspan="2">Equity</th></tr>
    <tr><td>Retained Earnings</td><td class="amount">£3,820.57</td></tr>
    <tr><td>Total Equity</td><td class="amount">£3,820.57</td></tr>
    <tr><th>Total Liabilites and Equity</th><td class="amount">£4,547.47</td></tr>
    <tr></tr>
</table>

<? require('../footer.php'); ?>
</body>
</html>