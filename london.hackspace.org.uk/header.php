<?php require_once('header-minimal.php'); ?>
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
                <?=menulink('/members/code.php', 'code', 'Car Park Code')?>
                <?=menulink('/members/cards.php', 'cards', 'Manage Access Cards')?>
            <? } ?>
            <?=menulink("/members/profile/{$user->getId()}", 'profile', 'View My Profile')?>
            <?=menulink('/members/edit.php', 'edit', 'Edit Account')?>
            <li class="divider"></li>
            <li><a href="/logout">Logout</a></li>
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
              <? if ($user && !fSession::get('suppress_profile_notification') && !$user->getHasProfile() && $page != 'edit') {?>
                <div class="profile-alert alert alert-info alert-dismissable">
                  <button type="button" class="close" data-dismiss="alert" data-persist="suppress_profile_notification"
                    aria-hidden="true">&times;</button>
                  You haven't filled in your member profile details yet.
                  <a href="/members/profile_edit.php" class="alert-link">Complete your profile</a> to let other members know more about you.
                </div>
              <? } ?>
        <? } ?>
