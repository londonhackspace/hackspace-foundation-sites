<?php require_once( $_SERVER['DOCUMENT_ROOT'] . '/../lib/init.php'); 
header("Content-Type: text/html; charset=utf-8");
?>
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
<!DOCTYPE html>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <? if (isset($noindex) && $noindex == True) { ?>
    <meta name="robots" content="noindex" />
    <? } ?>
    <? if (isset($desc)) { ?>
    <meta name="description" content="<?=$desc ?>" />
    <? } ?>
    <title><? if (isset($title)) echo "$title &mdash; "; ?>London Hackspace</title>

    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no"/>
    <meta name="apple-mobile-web-app-capable" content="yes"/> 
    <meta name="apple-mobile-web-app-status-bar-style" content="black"/>

    <link rel="stylesheet" type="text/css" href="/css/lib/bootstrap/bootstrap.min.css" />
    <link rel="stylesheet" type="text/css" href="/css/main.css?4" />
    <link rel="icon" href="/favicon.ico" />
    <link rel="canonical" href="https://london.hackspace.org.uk<?=$_SERVER['REQUEST_URI']?>" />

    <script type="text/javascript">
      WebFontConfig = {
            google: { families: [ 'Open+Sans:400,700:latin' ] }
                };
      (function() {
            var wf = document.createElement('script');
                wf.src = ('https:' == document.location.protocol ? 'https' : 'http') +
                        '://ajax.googleapis.com/ajax/libs/webfont/1/webfont.js';
                wf.type = 'text/javascript';
                wf.async = 'true';
                    var s = document.getElementsByTagName('script')[0];
                    s.parentNode.insertBefore(wf, s);
                      })();
    </script>

    <!-- HTML5 shim and Respond.js IE8 support of HTML5 elements and media queries -->
    <!--[if lt IE 9]>
      <script src="/javascript/html5shiv.js"></script>
      <script src="/javascript/respond.min.js"></script>
    <![endif]-->
</head>
<body>
<div class="page-container container">
    <div id="login-logout-container">
        <div class="btn-group">
        <?if ($user) { ?>
          <a class="btn btn-default dropdown-toggle" data-toggle="dropdown" href="/members/">
            <?= htmlspecialchars($user->getFullName()) ?> <span class="caret"></span>
          </a>
          <ul class="dropdown-menu pull-right" role="menu">
            <?=menulink('/members/', 'members', 'Membership Status')?>
            <li class="divider"></li>
            <?if (isset($user) && $user->isMember()) {?>
                <?=menulink('/members/code.php', 'code', 'Back Gate Code')?>
                <?=menulink('/members/cards.php', 'cards', 'Manage Access Cards')?>
            <? } ?>
            <?=menulink('/members/wiki.php', 'wiki', 'Wiki')?>
            <?=menulink('/members/edit.php', 'edit', 'Edit Account')?>
            <li class="divider"></li>
            <li><a href="/logout.php">Logout</a></li>
          </ul>
        <? } else { ?>
        <a class="btn btn-default" href="/login.php">Log In</a>
        <a class="btn btn-default" href="/signup.php">Join</a>
        <? } ?>
        </div>
    </div>
    
    <!-- Header section -->
    <? if (isset($large_page_heading)) { ?>
        <div id="hd" class="row large-page-heading">
            <div class="container col-md-4">
                  <a id="logo" href="/"><img alt="logo" src="/images/london.png" width="60" height="60"/></a>
                  <h1>London<br/>Hackspace</h1>
            </div>
            <? if (isset($blurb)) { ?>
            <div class="blurb col-md-8">
                <p>
                    <?= $blurb ?>
                </p>
            </div>
            <? } ?>
        </div><!-- end of hd -->
    <? } else { ?>
        <div id="hd" class="row small-page-heading">
            <div class="col-md-12">
                  <a id="logo" href="/"><img alt="logo" src="/images/london.png" width="60" height="60"/></a>
                  <h1>London<br/>Hackspace</h1>
            </div>
        </div><!-- end of hd -->
    <? } ?>
    <!-- end of Header section -->
    
    <!-- Start of Main Body section -->
    <div id="bd">
        <? if (!isset($hide_menu)) { ?>
            <? require('menu.php'); ?>
            <div id="non-menu-content" class="grid_10">
        <? } ?>
