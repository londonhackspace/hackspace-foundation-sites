<?php require_once( $_SERVER['DOCUMENT_ROOT'] . '/../lib/init.php'); ?>
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
    <link rel="stylesheet" type="text/css" href="/css/yui-combo.css" />
    <link rel="stylesheet" type="text/css" href="/css/main.css" />
    <link rel="icon" href="/favicon.ico" />
</head>
<body>
<div id="doc" class="yui-t1">
    <div id="hd">
        <a href="/"><img alt="logo" src="/images/london.png"/></a><h1>London<br/>Hackspace</h1>
<?if ($user) { ?>
        <p id="loggedin">Logged in as <a href="/members"><?=$user->getFullName()?></a>.
                        <a href="/logout.php">Logout</a></p>
<? } else { ?>
        <ul id="membermenu">
            <li><a href="/login.php">Login</a></li>
        </ul>
<? } ?>
    </div>
    <? require('menu.php'); ?>
    <div id="bd">
        <div id="yui-main">
            <div class="yui-b">
