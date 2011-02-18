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
        <?=menulink('/events', 'events', 'Events');?>
<?if (isset($user)) {?>
        <?=menulink('/members/', 'members', 'Members Home')?>
    <? if($user->isMember()) { ?>
        <?=menulink('/members/members.php', 'memberslist', 'Members List')?>
    <? } ?>
        <?=menulink('/members/edit.php', 'edit', 'Edit Account')?>
        <?=menulink('/members/donate.php', 'donate', 'Donate')?>
<? } ?>
    </ul>
</div>
