<?
$page = 'about';
$desc = 'The London Hackspace is a non-profit hackerspace in London. We\'re a community-run workshop for people to come to share tools and knowledge.';
$blurb = 'A non-profit hackerspace in London: a community-run workshop where people come to share tools and knowledge.';
$hide_menu = 1;
$show_twitter_feed = 1;
$large_page_heading = 1;
require('header.php'); ?>

<div id="home-page-container" class="row">
    <div class="col-md-4 col-md-push-4">
        <section>
            <h3>Visit us on a Tuesday</h3>
            <p>
                We hold open evenings <strong>every Tuesday</strong> from <strong>7pm</strong>
                where members and non-members hang out, hack on projects, socialise, and collaborate.
                <a href="http://wiki.london.hackspace.org.uk/view/Weekly_Open_Evenings">Learn more</a>.
            </p>
        </section>

        <section>
            <h3>Events</h3>
            <p>
                We host a variety of events covering topics from electronics to brewing and biohacking to lockpicking.
                For more details, see our <a href='/events/'>events calendar</a>.
            </p>
        </section>

        <section>
            <h3>Location</h3>
            <p>
              London Hackspace is now in Wembley with parking and convenient transport links.
            </p>
        </section>

        <section>
            <h3>Membership</h3>
            <p>
                Our members have a hand in the running of the organisation as well as 24/7 access to the space.
                Pay us what you think is fair &mdash; <strong><a href='signup.php'>join London Hackspace</a></strong>.
            </p>
        </section>

         <section>
            <h3>Donations</h3>
            <p>
              We're a cash-strapped non-profit and the cost of renting a space in London is extortionate.
              Please help us out by donating <a href="/donate.php">money</a> or
              <a href="http://wiki.london.hackspace.org.uk/view/Guides/Bringing_items_to_the_space">equipment</a>.
            </p>
        </section>

    </div>

    <div class="col-md-4 col-md-push-4">
        <section>
            <h3>Location</h3>
            <div>
              <p>
                Our address is:
              </p>
              <p>
                388 High Road<br>
                Wembley<br>
                HA9 6AR<br>
              </p>
            </div>
        </section>
        <section>
            <h3>Resources</h3>
            <ul>
                <li><a href="https://wiki.london.hackspace.org.uk/">The wiki</a>, for more about the space</li>
                <li><a href="https://groups.google.com/forum/#!forum/london-hack-space">Chat to us on the mailing list</a></li>
                <li><a href="http://webchat.freenode.net/?channels=london-hack-space">Chat to us on IRC</a></li>
                <li><a href="signup.php">Become a member</a></li>
                <li><a href="/organisation/">About the organisation</a></li>
		<li><a href="https://github.com/londonhackspace/hackspace-foundation-sites/blob/master/london.hackspace.org.uk/index.php">"Edit" this page (you'll need a github account)</a></li>
            </ul>
        </section>
    </div>
    <div class="col-md-4 col-md-pull-8">
        <section>
            <h3 class="collapsed" data-toggle="collapse" data-target="#flickr-badge-container">Photo stream</h3>
            <div id="flickr-badge-container" class="collapse">
              <? include('flickr.html') ?>
              <p><a href="http://www.flickr.com/groups/londonhackspace/pool">View more on Flickr&hellip;</a></p>
            </div>
        </section>

        <section>
            <h3 class="collapsed" data-toggle="collapse" data-target=".twitter-container">Twitter</h3>
            <div class="twitter-container collapse">
              <p>
                <a class="twitter-timeline" data-dnt="true" href="https://twitter.com/londonhackspace" data-widget-id="316326238916591616" height="300">Tweets by @londonhackspace</a>
              </p>
            </div>
        </section>
    </div>

</div><!-- end of home-page-container -->


<? require('footer.php'); ?>
<script type="text/javascript">
// homepage cleanup events for touch devices and prevent collapseable sections for wide screens
if($('#home-page-container').length > 0) {
	var moved = false;
    $('#home-page-container h3').bind('touchmove', function(e) {
    	moved = true;
	});
    $('#home-page-container h3').bind('touchend', function(e) {
    	e.preventDefault();
    	e.stopPropagation();
        if($(window).width() <= 480 && !moved) {
        	$(this).trigger('click');
        }
        moved = false;
       	return false;
	});
    $('#home-page-container h3').bind('click', function(e) {
        //alert($(window).width());
        if($(window).width() > 480) {
	        e.preventDefault();
            e.stopPropagation();
        }
    });
}
</script>
</body>
</html>
