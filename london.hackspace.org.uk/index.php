<? 
$page = 'about';
$desc = 'The London Hackspace is a non-profit hackerspace in central London. We\'re a community-run workshop for people to come to share tools and knowledge.';
$blurb = 'A non-profit hackerspace in London: a community-run workshop where people come to share tools and knowledge.';
$hide_menu = 1;
$show_twitter_feed = 1;
$large_page_heading = 1;
require('header.php'); ?>

<div id="home-page-container" class="row">
    <div class="col-md-4">
        <section>
            <h3 class="collapsed" data-toggle="collapse" data-target=".flickr-badge-container">Photo stream</h3>
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
        
    <div class="col-md-4">
        <section>
            <h3 class="collapsed" data-toggle="collapse" data-target=".visitus-collapse">Visit us on a Tuesday</h3>
            <p class="visitus-collapse collapse">
                We hold open evenings <strong>every Tuesday</strong> from <strong>7pm</strong> 
                where members and non-members hang out, hack on projects, socialise, and collaborate. 
                <a href="http://wiki.london.hackspace.org.uk/view/Weekly_Open_Evenings">Learn more</a>.
            </p>
        </section>

        <section>
            <h3 class="collapsed" data-toggle="collapse" data-target=".events-collapse">Events</h3>
            <p class="events-collapse collapse">
                We host a variety of events covering topics from electronics to brewing and biohacking to lockpicking.
                For more details, see our <a href='/events/'>events calendar</a>.
            </p>
        </section>

        <section>
            <h3 class="collapsed" data-toggle="collapse" data-target=".member-collapse">Membership</h3>
            <p class="member-collapse collapse">
                Our members have a hand in the running of the organisation as well as 24/7 access to the space.
                Pay us what you think is fair &mdash; <strong><a href='signup.php'>join London Hackspace</a></strong>.
            </p>
        </section>

         <section>
            <h3 class="collapsed" data-toggle="collapse" data-target=".donate-collapse">Donations</h3>
            <p class="donate-collapse collapse">
              We're a cash-strapped non-profit and the cost of renting a space in London is extortionate.
              Please help us out by donating <a href="/donate.php">money</a> or 
              <a href="http://wiki.london.hackspace.org.uk/view/Guides/Bringing_items_to_the_space">equipment</a>.
            </p>
        </section>
        
    </div>
    <div class="col-md-4">
        
        <section>
            <h3 class="collapsed" data-toggle="collapse" data-target=".locate-collapse">Location</h3>
            <div class="locate-collapse collapse">
                <p>
                    We have a <a href="https://wiki.london.hackspace.org.uk/wiki/447_Hackney_Road">great space</a> on 
                    Hackney Road, open to our members 24 hours a day.
                    We hold regular <a href="/events/">events</a> (often free) which are open to everyone.
                </p>
                <p>
                    <a href='http://goo.gl/maps/7BVpY'>
                      <img id="google-map" src="//maps.googleapis.com/maps/api/staticmap?markers=color:red%7Clabel:A%7C51.532083,-0.060795&zoom=14&size=350x250&visual_refresh=true&key=AIzaSyD1tdIb23oau0tQJrxBxO9umFDQkyzyqAE&sensor=false">
                      </a>
                </p>
                <p id='hackspace-physical-address' class="vcard">
                    <span class="fn org">London Hackspace</span><br/>
                    <span class="street-address">447 Hackney Road</span><br/>
                    <span class="locality region">London</span><br/>
                    <span class="postal-code">E2 9DY</span>
                </p>
            </div>
        </section>
        
        <section>
            <h3 class="collapsed" data-toggle="collapse" data-target=".resource-collapse">Resources</h3>
            <ul class="resource-collapse collapse">
                <li><a href="https://wiki.london.hackspace.org.uk/">The wiki</a>, for more about the space</li>
                <li><a href="http://webchat.freenode.net/?channels=london-hack-space">Chat to us on IRC</a></li>
                <li><a href="signup.php">Become a member</a></li>
                <li><a href="/organisation/">About the organisation</a></li>
            </ul>
        </section>
    </div>
</div><!-- end of home-page-container -->


<? require('footer.php'); ?>
