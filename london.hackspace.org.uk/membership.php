<? 
$page = 'membership';
require('header.php'); 

if ($user) {
    fURL::redirect('/members');
}
?>
<h2>Membership</h2>
<p>
    The London Hackspace is run by the <a href="http://hackspace.org.uk">Hackspace Foundation</a>, which is a members-owned 
    non-profit association. Members have a hand in the running of the organisation as 
    well as access to the space. 
</p>

<p>
    Membership is paid monthly by standing order. We're currently asking for a subscription of £20 per 
    month, although this will increase once we find a larger space. In return for this 
    you'll get full transparency on where your money is going, as well as a say in what 
    we do with it.
</p>

<p>
    If you genuinely can't afford £20, you are free to pay less within reason. More is also good.
</p>

<h3>How do I join?</h3>
<p>
    To become a member, please <a href="/signup.php">register</a> first.
</p>
<? require('footer.php'); ?>
