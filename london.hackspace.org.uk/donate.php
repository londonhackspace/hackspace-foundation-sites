<? 
$page = 'donate';
$title = 'Donate';
require( './header.php' );
?>

<h2>Donate</h2>
<p>London Hackspace is a non-profit organisation which provides facilites and hosts events for the geek community in London.
    It costs us <a href="/cost-of-hacking/">more than £10,000 per month</a>
    to keep our doors open. Your donation can help us make our space more comfortable, buy better tools,
    and run more events.</p>

<div class="panel panel-info">
<div class="panel-heading">Please note</div>
<div class="panel-body">
If you want to pay your membership fees for London Hackspace, this can be done using GoCardless.
Please <a href="/login.php">log in</a> or <a href="/signup.php">sign up</a> for further details.
</div>
</div>

<div class="container-fluid">
<div class="row">

<div class="col-md-4">
<div class="panel panel-default">
    <div class="panel-heading"><h3 class="panel-title">Paypal</h3></div>
    <div class="panel-body">
        <form action="https://www.paypal.com/donate" method="post" target="_top">
            <input type="hidden" name="hosted_button_id" value="6KS7W6AUTCFRL" />
            <input type="image" src="https://www.paypalobjects.com/en_GB/i/btn/btn_donate_LG.gif" border="0" name="submit" title="PayPal - The safer, easier way to pay online!" alt="Donate with PayPal button" />
            <img alt="" border="0" src="https://www.paypal.com/en_GB/i/scr/pixel.gif" width="1" height="1" />
        </form>
    </div>
</div>
</div>

<div class="col-md-4">
<div class="panel panel-default">
    <div class="panel-heading"><h3 class="panel-title">Bitcoin</h3></div>
    <div class="panel-body">
    Apologies we currently do not accept Bitcoin donations
</div>
</div>
</div>

<div class="col-md-4">
<div class="panel panel-default">
    <div class="panel-heading"><h3 class="panel-title">Bank Transfer</h3></div>
        <? if ($user) { ?>
        <table class="table">
        <tbody>
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
        </tbody>
        <tfoot>
            <tr><td colspan="2">
            Don't worry if the name doesn't fit in full.
            </td></tr>
        </tfoot>
        </table>


        <? } else { ?>
        <div class="panel-body">
            Due to direct debit fraud, we require you to <a href="/login.php">log in</a>
            before you can see our bank details.
        </div>
        <? } ?>
</div>
</div>

</div>

<?php require('./footer.php'); ?>
</body>
</html>
