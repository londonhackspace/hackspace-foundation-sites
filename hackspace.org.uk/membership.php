<? 
$page = 'membership';
require('header.php'); 

if ($user) {
    fURL::redirect('/members');
}
?>
<h2>Membership</h2>
<p>
    Members of the Hackspace Foundation have a hand in the running of the organisation and 
    can elect directors. Members of any of our spaces (once they're set up) will also be 
    members of the foundation, but you can also become a direct member of the foundation 
    itself.
</p>

<p>
    Foundation membership is paid monthly. Initially we're asking for a donation of £10 per 
    month &mdash; we need to build up cash to pay a deposit as well as have enough in reserve.
    In return for this you'll get full transparency on where your money is going, as well
    as a say in what we do with it.
</p>

<p>
    If you genuinely can't afford £10, you are free to pay less within reason.
</p>
<h3>What happens if it all goes wrong?</h3>
<p>
    Due to the way the foundation is <a href="/organisation">set up</a> as a non-profit, we're not legally 
    allowed to pay profit to anyone &mdash; we must spend the money on hack spaces. If the
    Foundation falls apart, we have to donate any surplus funds to a non-profit with similar goals.
</p>

<h3>How do I join?</h3>
<p>
    To become a member, please <a href="/signup.php">Sign Up</a>.
</p>
<? require('footer.php'); ?>
