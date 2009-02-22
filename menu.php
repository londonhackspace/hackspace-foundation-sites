<?php
function menulink($url, $name, $title) {
    global $page;
    $ret = '';
    if ($page != $name) {
        $ret .= '<a href="' . $url . '">';
    }
    $ret .= $title;
    if ($page != $name) {
        $ret .= '</a>';
    }
    return $ret;
}

?>
<div class="yui-b">
    <ul id="menu">
        <li><?=menulink('/organisation', 'organisation', 'Organisation');?>
            <? if ($section == 'organisation') { ?>
                <ul class="submenu">
                    <li><?=menulink('/organisation/arts.php', 'arts', 'Articles of Association');?></li>
                    <li><?=menulink('/organisation/mem.php', 'mem', 'Memorandum of Association');?></li>
                </ul>
            <? } ?>
        </li>
        <li><a href="http://www.beeonastring.com">Wiki</a></li>
        <li><?=menulink('/contact', 'contact', 'Contact Us');?></li>
    </ul>
</div>
