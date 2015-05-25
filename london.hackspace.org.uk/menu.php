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
                <?=menulink('/storage/list.php', 'storagelist', 'Storage Requests')?>
            <? } ?>
            <? if (isset($user)) { ?>
            <li class="dropdown">
                    <a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button"
                        aria-expanded="false">Access <span class="caret"></span></a>
                    <ul class="dropdown-menu" role="menu">
                        <?=menulink('/members/cards.php', 'cards', 'Access Cards')?>    
                        <?if (isset($user) && $user->isMember()) {?>
                        <?=menulink('/members/code.php', 'code', 'Gate Code')?>
                        <?=menulink('/members/tools.php', 'tools', 'Tools')?>
                        <?=menulink('/members/ldap.php', 'LDAP', 'Edit LDAP Account')?>
                        <? } ?>
                    </ul>
                </li>
            
            <? } ?>
        </ul>
</nav>
