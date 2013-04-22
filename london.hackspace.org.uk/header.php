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
    <link rel="stylesheet" type="text/css" href="/css/lib/960gs/reset.css" />
    <link rel="stylesheet" type="text/css" href="/css/lib/960gs/text.css" />
    <link rel="stylesheet" type="text/css" href="/css/lib/960gs/960.css" />
    <link rel="stylesheet" type="text/css" href="/css/main.css" />
    <link rel="icon" href="/favicon.ico" />
</head>
<body>
<div id="doc" class="container_12">
    <div id="login-logout-container" class="container_12">
        <?if ($user) { ?>
                <p id="loggedin">
                    Logged in as <a href="/members"><?= htmlspecialchars($user->getFullName()) ?></a>.
                    <a href="/logout.php">Logout</a>
                </p>
        <? } else { ?>
                <p>
                    <a href="/login.php">Login</a></li>
                </p>
        <? } ?>
    </div>
    
    <!-- Header section -->
    <? if (isset($large_page_heading)) { ?>
        <div id="hd" class="container_12 large-page-heading">
            <div class="grid_4">
                <a href="/"><img alt="logo" src="/images/london.png"/></a><h1>London<br/>Hackspace</h1>
            </div>
            <? if (isset($blurb)) { ?>
            <div class="blurb grid_8">
                <p>
                    <?= $blurb ?>
                </p>
            </div>
            <? } ?>
        <hr/>
        </div><!-- end of hd -->
    <? } else { ?>
        <div id="hd" class="container_12 small-page-heading">
            <div class="grid_11">
                <a href="/"><img alt="logo" src="/images/london.png"/></a><h1>London Hackspace</h1>
            </div>
        <hr/>
        </div><!-- end of hd -->
    <? } ?>
    <!-- end of Header section -->
    
    <!-- Start of Main Body section -->
    <div id="bd" class="container_12">
        <? if (!isset($hide_menu)) { ?>
            <? require('menu.php'); ?>
            <div id="non-menu-content" class="grid_10">
        <? } ?>
