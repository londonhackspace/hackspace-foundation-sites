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
    <ul id="menu">
        <?=menulink('/', 'about', 'About');?>
        <?=menulink('/organisation', 'organisation', 'Organisation');?>
        <li><a href="http://wiki.hackspace.org.uk">Wiki</a></li>
        <?=menulink('/donate', 'donate', 'Donate');?>
        <?=menulink('/membership.php', 'membership', 'Membership');?>
        <?=menulink('/contact', 'contact', 'Contact Us');?>
    </ul>
</div>
