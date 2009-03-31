<?php require_once( $_SERVER['DOCUMENT_ROOT'] . '/../lib/init.php'); ?>
<!DOCTYPE html>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <? if ($desc) { ?>
    <meta name="description" content="<?=$desc ?>" />
    <? } ?>
    <title><? if ($title) echo "$title &mdash; "; ?>Hackspace Foundation</title>
    <link rel="stylesheet" type="text/css" 
        href="http://yui.yahooapis.com/combo?2.7.0/build/reset-fonts-grids/reset-fonts-grids.css&amp;2.7.0/build/base/base-min.css" /> 
    <link rel="stylesheet" type="text/css" href="/css/main.css" />
    <link rel="icon" href="/favicon.ico" />
</head>
<body>
<div id="doc" class="yui-t1">
    <div id="hd">
        <a href="/"><img src="/images/logo.png" alt="Hackspace Foundation"/></a><h1>Hackspace<br/>Foundation</h1>
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
