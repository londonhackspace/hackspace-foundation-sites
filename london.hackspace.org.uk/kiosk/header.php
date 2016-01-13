<?php
require_once( $_SERVER['DOCUMENT_ROOT'] . '/../lib/init.php');
header("Content-Type: text/html; charset=utf-8");
?>
<!DOCTYPE html>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <meta name="robots" content="noindex" />
    <title>Kiosk</title>
    <link rel="stylesheet" type="text/css" href="//netdna.bootstrapcdn.com/bootstrap/3.1.1/css/bootstrap.min.css" />
    <link rel="stylesheet" type="text/css" href="/css/kiosk.css" />
  </head>
<body>
<? if (!isset($suppress_card_input)) { ?>
    <form action="card.php" style="position:absolute; left:-9999px;">
        <input type="text" name="cardid" id="cardid" accesskey="i"/>
    </form>
<? } ?>
<div class="page-container container">
    <div class="page-header">
        <h1><img alt="logo" src="/images/london.png" width="60" height="60"/>&nbsp;<?=$title?></h1>
    </div>
    <div class="row">
        <div class="col-md-8 col-md-offset-2">
