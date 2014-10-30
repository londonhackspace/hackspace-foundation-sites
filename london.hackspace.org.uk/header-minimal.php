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

    <link rel="stylesheet" type="text/css" href="//netdna.bootstrapcdn.com/bootstrap/3.1.1/css/bootstrap.min.css" />
    <link rel="stylesheet" type="text/css" href="/css/main.css?9" />
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
      <script src="https://oss.maxcdn.com/libs/html5shiv/3.7.0/html5shiv.js"></script>
      <script src="https://oss.maxcdn.com/libs/respond.js/1.4.2/respond.min.js"></script>
    <![endif]-->

    <? if (isset($extra_head)) { ?>
        <?=$extra_head ?>
    <? } ?>
</head>
<body>
<div class="page-container container">
