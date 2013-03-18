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
<div class="yui-b">
    <ul id="menu" class="menu">
        <?=menulink('/', 'about', 'About');?>
        <li><a href="http://wiki.hackspace.org.uk">Wiki</a></li>
        <?=menulink('/signup.php', 'membership', 'Join');?>
        <?=menulink('/events/', 'events', 'Events');?>
        <?=menulink('/organisation/', 'organisation', 'Organisation');?>
        <?=menulink('/donate.php', 'donate', 'Donate')?>
<?if (isset($user)) {?>
        <?=menulink('/members/', 'members', 'Members Home')?>
    <? if($user->isMember()) { ?>
        <?=menulink('/members/members.php', 'memberslist', 'Members List')?>
        <?=menulink('/members/code.php', 'code', 'Code Access')?>
        <?=menulink('/members/webcams.php', 'webcams', 'Webcams')?>
        <?=menulink('/members/wiki.php', 'wiki', 'Wiki Access')?>
    <? } ?>
        <?=menulink('/members/cards.php', 'cards', 'Cards')?>
        <?=menulink('/members/edit.php', 'edit', 'Edit Account')?>
<? } ?>
    </ul>
</div>
