<? 
$page = 'about';
$desc = 'The London Hackspace is a non-profit hackerspace in central London. We\'re a community-run workshop for people to come to share tools and knowledge.';
$blurb = 'A non-profit hackerspace in London: a community-run workshop where people come to share tools and knowledge.';
$hide_menu = 1;
$show_twitter_feed = 1;
$large_page_heading = 1;
require('header.php'); ?>

<div id="home-page-container" class="container_12">
    <div class="grid_4">
        <div class='home-page-section'>
            <div class="title">
                <h3>Look Around</h3>
            </div>
            <p class="flickr-badge-container">
                  <script type="text/javascript" src="badge.php"></script>
            </p>
        </div>
        <div class='home-page-section'>
            <div class="title">
                <h3>Twitter</h3>
            </div>
            <div class="twitter-container">
              <p>
                <a class="twitter-timeline" data-dnt="true" href="https://twitter.com/londonhackspace" data-widget-id="316326238916591616" height="300">Tweets by @londonhackspace</a>
              </p>
            </div>
        </div>
    </div>
        
    <div class="grid_4">
        <div class='home-page-section'>
            <div class="title">
                <h3>Visit us on a Tuesday</h3>
            </div>
            <p>
                We hold open evenings <strong>every Tuesday</strong> from <strong>7pm</strong> 
                where members and non-members hang out, hack on projects, socialise, and collaborate. 
                <a href="http://wiki.london.hackspace.org.uk/view/Weekly_Open_Evenings">Learn more</a>.
            </p>
        </div>

        <div class='home-page-section'>
            <div class="title">
                <h3>Events</h3>
            </div>
            <p>
                We host a variety of events covering topics from electronics to brewing and biohacking to lockpicking.
                For more details, see our <a href='/events/'>events calendar</a>.
            </p>
        </div>

        <div class='home-page-section'>
            <div class="title">
                <h3>Get involved</h3>
            </div>
            <p>
                Our members have a hand in the running of the organisation as well as 24/7 access to the space.
                Pay us what you think is fair &mdash; <strong><a href='signup.php'>join London Hackspace</a></strong>.
            </p>
        </div>

         <div class='home-page-section'>
            <div class="title">
                <h3>Help us out</h3>
            </div>
            <p>
              We're a cash-strapped non-profit and the cost of renting a space in London is extortionate.
              Please help us out by donating <a href="/donate.php">money</a> or 
              <a href="http://wiki.london.hackspace.org.uk/view/Guides/Bringing_items_to_the_space">equipment</a>.
            </p>
        </div>
        
    </div>
    <div class="grid_4">
        
        <div class='home-page-section'>
            <div class="title">
                <h3>How to find us</h3>
            </div>
            <p>
                We have a <a href="https://wiki.london.hackspace.org.uk/wiki/447_Hackney_Road">great space</a> on 
                Hackney Road, open to our members 24 hours a day.
                We hold regular <a href="/events/">events</a> (often free) which are open to everyone.
            </p>
            <div class="google-maps-container">
                <p>
                    <iframe id="google-maps-content" src="https://maps.google.co.uk/maps?f=q&amp;source=embed&amp;hl=en&amp;q=London+Hackspace&amp;sll=51.530746,-0.076218&amp;ie=UTF8&amp;hq=London+Hackspace&amp;hnear=&amp;t=m&amp;z=14&amp;iwloc=near&amp;cid=11666902510411106101&amp;ll=51.532083,-0.060795&amp;output=embed"></iframe><br />
                    <a id='google-maps-link' href='http://goo.gl/maps/7BVpY'>View larger map</a>                </p>
                <p id='hackspace-physical-address' class="vcard">
                    <span class="fn org">London Hackspace</span><br/>
                    <span class="street-address">447 Hackney Road</span><br/>
                    <span class="locality region">London</span><br/>
                    <span class="postal-code">E2 9DY</span>
                </p>
            </div>
        </div>
        
        <div class='home-page-section'>
            <div class="title">
                <h3>Resources</h3>
            </div>
            <div>
                <ul>
                    <li><a href="https://wiki.london.hackspace.org.uk/">The wiki</a>, for more about the space</li>
                    <li><a href="http://webchat.freenode.net/?channels=london-hack-space">Chat to us on IRC</a></li>
                    <li><a href="signup.php">Become a member</a></li>
                    <li><a href="/organisation/">About the organisation</a></li>
                </ul>
            </div>
        </div>
    </div>
</div><!-- end of home-page-container -->


<? require('footer.php'); ?>
