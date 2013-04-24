<? 
$page = 'about';
$desc = 'The London Hackspace is a non-profit, community-run hacker space in central London. We provide infrastructure for our members to make and learn things.';
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
                <a class="twitter-timeline" data-dnt="true" href="https://twitter.com/londonhackspace" data-widget-id="316326238916591616">Tweets by @londonhackspace</a>
            </div>
        </div>
    </div>
        
    <div class="grid_4">
        <div class='home-page-section'>
            <div class="title">
                <h3>Visit us on a Tuesday</h3>
            </div>
            <p>
                We hold open evenings every Tuesday from 8pm where people hang out and hack on projects, socialise and collaborate. <a href="http://wiki.london.hackspace.org.uk/view/Weekly_Open_Evenings">Learn more</a>.
            </p>
        </div>


        <div class='home-page-section'>
            <div class="title">
                <h3>Events</h3>
            </div>
            <p>
                We host a variety of events. For more details, view the <a href='/events/'>London Hackspace Events calendar</a>
            </p>
        </div>

        <div class='home-page-section'>
            <div class="title">
                <h3>Become a member</h3>
            </div>
            <p>
                Our members have a hand in the running of the organisation as well as 24/7 access to the space. Pay what you think is fair &mdash; <a href='signup.php'>join the London Hackspace</a>
            </p>
        </div>
        
    </div>
    <div class="grid_4">
        
        <div class='home-page-section'>
            <div class="title">
                <h3>How to find us</h3>
            </div>
            <p>
                We have a <a href="http://wiki.hackspace.org.uk/wiki/447_Hackney_Road">great new space</a> on Hackney Road, which is open to our members 24 hours a day,
                and we hold regular <a href="/events/">events</a> (often free) which are open to everyone.
            </p>
            <div class="google-maps-container">
                <p>
                    <iframe id="google-maps-content" src="http://maps.google.co.uk/maps?f=q&amp;source=embed&amp;hl=en&amp;q=London+Hackspace&amp;sll=51.530746,-0.076218&amp;ie=UTF8&amp;hq=London+Hackspace&amp;hnear=&amp;t=m&amp;z=14&amp;iwloc=near&amp;cid=11666902510411106101&amp;ll=51.532083,-0.060795&amp;output=embed"></iframe><br />
                    <a id='google-maps-link' href='http://goo.gl/maps/7BVpY'>View larger map</a>                </p>
                <p id='hackspace-physical-address'>
                    London Hackspace<br/>
                    447 Hackney Road<br/>
                    London<br/>
                    E2 9DY
                </p>
            </div>
        </div>
        
        <div class='home-page-section'>
            <div class="title">
                <h3>Resources</h3>
            </div>
            <div>
                <ul>
                    <li><a href="http://wiki.hackspace.org.uk/">The Wiki</a>, Full of projects and interesting information.</li>
                    <li><a href="http://webchat.freenode.net/?channels=london-hack-space">Chat to us on IRC.</a></li>
                    <li><a href="signup.php">Become a Member.</a></li>
                    <li><a href="/organisation/">About the Organisation.</a></li>                        
                </ul>
            </div>
        </div>
        
        
    </div>
        
</div><!-- end of home-page-container -->


<? require('footer.php'); ?>
