<div class="collapsed navbar-collapse" data-toggle="collapse" data-target=".navbar">Menu</div>
<nav class="navbar navbar-default collapse" role="navigation">
        <ul class="nav navbar-nav">
            <?=menulink('/', 'about', 'Home');?>
            <li><a href="http://wiki.london.hackspace.org.uk">Wiki</a></li>
            <? if (!isset($user)) { ?>
                <?=menulink('/signup.php', 'membership', 'Join');?>
            <? } ?>
            <?=menulink('/events/', 'events', 'Events');?>
            <?=menulink('/organisation/', 'organisation', 'Organisation');?>
            <?=menulink('/donate.php', 'donate', 'Donate')?>
            <?if (isset($user) && $user->isMember()) {?>
                <?=menulink('/members/members.php', 'memberslist', 'Members List')?>
                <?=menulink('/members/webcams.php', 'webcams', 'Webcams')?>
            <? } ?>
        </ul>
</nav>