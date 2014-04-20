<?
require('../../lib/init.php');
if (!isset($_GET['id'])) {
  fURL::redirect('/members/');
} else {
	try {
	  $this_user = new User(filter_var($_GET['id'], FILTER_SANITIZE_NUMBER_INT));
	} catch(fNotFoundException $e) {
    header('HTTP/1.1 404 Not Found');
    echo "Profile not found";
    exit;
	}
}

$title = "Member Profile: {$this_user->getFullName()}";
$desc = '';
require('../header.php');

ensureLogin();

if(
	(($user->isMember() && $this_user->isMember())
	|| ($user->getMemberNumber() == $this_user->getMemberNumber())
	|| $user->isAdmin())
	&& $this_user->getHasProfile() == 1 && $this_user->getDisabledProfile() == 0
) {
	$user_profile = $this_user->createUsersProfile();

	if(!$this_user->isMember() && ($user->getMemberNumber() == $this_user->getMemberNumber())) { ?>
		<div class="alert alert-info"><p>Thanks! Your profile will become available to other members when your payment has been received.</p></div>
	<? } ?>
<div class="row profile">
	<div class="col-md-3">
    <div class="member-avatar">
        <span class="thumbnail">
            <img src="/members/photo.php?name=<?=$user_profile->getPhoto() ?>" class="display img-responsive" alt="User photo"/>
        </span>
    </div>
    <? if($this_user->hasLearnings()) {?>
      <div class="member-training well well-sm">
        <? foreach($this_user->buildLearnings() as $training) {?>
			    <a href="<?=$training->getUrl()?>" class="training-badge" target="_blank">
            <img src="/images/trained-<?=strtolower(str_replace(' ','',$training->getName()));?>.png"
                 title="<?=$training->getDescription()?>"
                 alt="<?=$training->getDescription()?>"
            /></a>
        <? } ?>
      </div>
    <? } ?>
	</div>
  <div class="col-md-9">
    <div class="panel panel-default">
      <? if($user->getMemberNumber() == $this_user->getMemberNumber()) { ?>
        <small class="profile_edit">
          <a class="btn btn-default btn-sm" href="/members/profile_edit.php">Edit my profile</a>
        </small>
      <? } ?>
      <div class="panel-heading">
        <h3 class="panel-title">
          <?= htmlspecialchars($this_user->getFullName()) ?>
        </h3>
        <p class="details">
          <?=$this_user->getMemberNumber()?>
          <? if($this_user->firstTransaction() != null) {
              echo ' &mdash; joined in '.$this_user->firstTransaction();
            } if($user_profile->getAllowDoorbot() && $this_user->getDoorbotTimestamp() != '') {
              echo ', last seen '.date('dS M Y', strtotime($this_user->getDoorbotTimestamp()));
            } ?>
        </p>
      </div>
      <div class="panel-body">
        <? require('profile/aliases.php'); ?>
        <? if($user_profile->getDescription() != '') { ?>
          <h4>Projects I'm working on</h4>
          <p><?=stripslashes($user_profile->getDescription()) ?></p>
        <? }
           if($this_user->hasInterests()) {
            require('profile/interests.php');
           } ?>
      </div>
    </div>
  </div>
</div>
<br/>
<? } else if (($user->isMember() && $this_user->isMember()) || ($user->getMemberNumber() == $this_user->getMemberNumber()) || $user->isAdmin()){ ?>
<div class="row profile">
	<div class="col-md-3">
		<div class="member-avatar">
            <img src="/members/photo.php?name=" class="display"/>
    </div>
  </div>
	<div class="col-md-9">
		<? if($user->getMemberNumber() == $this_user->getMemberNumber()) { ?>
		<small class="profile_edit"><a class="btn btn-default btn-sm" href="/members/profile_edit.php">Edit my profile</a></small>
		<? } ?>

		<h3>
			<?= htmlspecialchars($this_user->getFullName()) ?>
			<p><small><?=$this_user->getMemberNumber()?></small></p>
		</h3>
  </div>
</div>
<? } else { ?>
   <p>You don't have access to this page.</p>
<?php }
require('../footer.php'); ?>
</body>
</html>