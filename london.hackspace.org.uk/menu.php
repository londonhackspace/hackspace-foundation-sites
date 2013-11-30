<?php
function menulink($url, $name, $title) {
    global $page;
    $ret = '<li';
    if ($page == $name) {
        $ret .= ' class="active"';
    }   
    $ret .= '>';
    if ($page != $name) {
        $ret .= '<a href="' . $url . '">';
    }
    $ret .= $title;
    if ($page != $name) {
        $ret .= '</a>';
    }
    $ret .= '</li>';
    return $ret;
}

?>
<div id="menu-container" class='grid_2'>
    <nav id="menu" class="menu">
        <h4>Options</h4>
        <ul id="main-menu" class="menu">
            <?=menulink('/', 'about', 'Home');?>
            <li><a href="http://wiki.london.hackspace.org.uk">Wiki</a></li>
            <? if (!isset($user)) { ?>
                <?=menulink('/signup.php', 'membership', 'Join');?>
            <? } ?>
            <?=menulink('/events/', 'events', 'Events');?>
            <?=menulink('/organisation/', 'organisation', 'Organisation');?>
            <?=menulink('/donate.php', 'donate', 'Donate')?>
        </ul>
    <?if (isset($user)) {?>
        <h4>Member Options</h4>
        <ul id="member-menu" class="menu">
            <?=menulink('/members/', 'members', 'Members Home')?>
        <? if($user->isMember()) { ?>
            <?=menulink('/members/members.php', 'memberslist', 'Members List')?>
            <?=menulink('/members/code.php', 'code', 'Code Access')?>
            <?=menulink('/members/webcams.php', 'webcams', 'Webcams')?>
            <?=menulink('/members/wiki.php', 'wiki', 'Wiki Access')?>
        <? } ?>
            <?=menulink('/members/cards.php', 'cards', 'Cards')?>
            <?=menulink('/members/edit.php', 'edit', 'Edit Account')?>
        </ul>
    <? } ?>
    </nav>
</div><!-- end of menu-container -->
