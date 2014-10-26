<?php require_once( $_SERVER['DOCUMENT_ROOT'] . '/../lib/init.php');
header("Content-Type: text/html; charset=utf-8");
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
</head>
<body>
