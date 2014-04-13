<? 
$page = 'profile';
$title = "Member Profile";
$desc = '';
require('../header.php');

if (!isset($user)) {
    fURL::redirect('/login.php?forward=/members/profile.php');
}

if(!isset($_GET['id']))
  $this_user = $user;
else {
	try {
	  $this_user = new User(filter_var($_GET['id'], FILTER_SANITIZE_NUMBER_INT));
	} catch(fNotFoundException $e) {
	  $this_user = $user;
	}
}

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
            <img src="photo.php?name=<?=$user_profile->getPhoto() ?>" class="display"/>
        </div>
        <? if($this_user->hasLearnings()) {?>
        <div class="member-training">
            <? foreach($this_user->buildLearnings() as $training) {?>
			<a href="<?=$training->getUrl()?>" class="training-badge" target="_blank"><img src="/images/trained-<?=strtolower(str_replace(' ','',$training->getName()));?>.png" title="<?=$training->getDescription()?>" /></a>
            <? } ?>
			<p><small><a href="https://wiki.london.hackspace.org.uk/view/Training">More information about training</a></small></p>
        </div>
        <? } ?>
	</div>
	<div class="col-md-9">
		<? if($user->getMemberNumber() == $this_user->getMemberNumber()) { ?>
		<small class="profile_edit"><a class="btn btn-default btn-sm" href="/members/profile_edit.php">Edit my profile</a></small>
		<? } ?>
		<h3>
			<?= htmlspecialchars($this_user->getFullName()) ?>
			<p><small><?=$this_user->getMemberNumber()?><br/><? if($this_user->firstTransaction() != null) {
				echo ' Joined '.$this_user->firstTransaction(); 
			} if($user_profile->getAllowDoorbot() && $this_user->getDoorbotTimestamp() != '') {
				echo ', last seen '.date('dS M Y', strtotime($this_user->getDoorbotTimestamp()));
			} ?>
			</small></p>
		</h3>
		<ul class="aliases-list">
			<? if($user_profile->getAllowEmail()) { ?>
			<li><span class="glyphicon glyphicon-envelope"></span> <a href="mailto:<?=$this_user->getEmail()?>"><?=$this_user->getEmail()?></a></li>
			<? } ?>
			<? if($user_profile->getWebsite() != '') { ?>
			<li><span class="glyphicon glyphicon-globe"></span> <a href="<?=$user_profile->getWebsite() ?>" target="_blank"><?=$user_profile->getWebsite() ?></a></li>
			<? } ?>
		</ul>
        <? if($this_user->hasUsersAliases()) {?>
		<h4>Aliases</h4>
		<ul class="aliases-list">
            <? foreach($this_user->buildUsersAliases() as $alias) {?>
			<li><img src="/images/icon-<?=strtolower(str_replace(' ','',$alias->getAliasId()));?>.png" title="<?=$alias->getAliasId()?>" class="member-social-icon"/> 
				<? switch($alias->getAliasId()) {
					case 'Facebook': echo '<a target="_blank" href="https://www.facebook.com/' . $alias->getUsername() . '">' . $alias->getUsername() . '</a>'; break;
					case 'YouTube': echo '<a target="_blank" href="https://www.youtube.com/user/' . $alias->getUsername() . '">' . $alias->getUsername() . '</a>'; break;
					case 'GitHub': echo '<a target="_blank" href="https://github.com/' . $alias->getUsername() . '">' . $alias->getUsername() . '</a>'; break;
					case 'Google+': echo '<a target="_blank" href="https://plus.google.com/' . $alias->getUsername() . '">' . $alias->getUsername() . '</a>'; break;
					case 'Twitter': echo '<a target="_blank" href="https://twitter.com/' . $alias->getUsername() . '">' . $alias->getUsername() . '</a>'; break;
					case 'LinkedIn': echo '<a target="_blank" href="http://www.linkedin.com/in/' . $alias->getUsername() . '">' . $alias->getUsername() . '</a>'; break;
					case 'Flickr': echo '<a target="_blank" href="https://www.flickr.com/photos/' . $alias->getUsername() . '">' . $alias->getUsername() . '</a>'; break;
					case 'IRC': echo '<a target="_blank" href="http://webchat.freenode.net/?channels=london-hack-space">' . $alias->getUsername() . '</a>'; break;
					case 'Hackspace Wiki': echo '<a target="_blank" href="https://wiki.london.hackspace.org.uk/view/User:' . $alias->getUsername() . '">' . $alias->getUsername() . '</a>'; break;
					default: echo $alias->getUsername();
				}?>
			</li>
            <? } ?>
		</ul>
        <? } ?>

		<? if($user_profile->getDescription() != '') { ?>
		<h4>Projects I'm working on</h4>
		<p><?=$user_profile->getDescription() ?></p>
		<? } ?>

        <? if($this_user->hasInterests()) {?>
	    <div class="interests">
			<h4>Interests</h4>
	        <div class="list">
	            <? $interest_category = ''; $interest_count = 0; foreach($this_user->buildInterests() as $interest) {?>
					<? if($interest_category != $interest->getCategory()) { 
						$interest_category = $interest->getCategory();
					?>
						<? if($interest_count > 0) { ?>
						</ul>
					</div>
				    <? } ?>
		        	<div>
		        		<h5><?=$interest->getCategory() ?></h5>
						<ul>
				    <? } ?>
							<li>
								<? if($interest->getUrl() != null && $interest->getUrl() != '') { ?>
								<a href="<?=$interest->getUrl() ?>" target="_blank"><?=$interest->getName() ?></a>
								<? } else { ?>
								<?=$interest->getName() ?>
								<? } ?>
							</li>
					<? $interest_count++; ?>
		        <? } ?>
					</ul>
				</div>
		   </div>
	    </div>
		<? } ?>
	</div>
</div>
<br/>
<? } else if (($user->isMember() && $this_user->isMember()) || ($user->getMemberNumber() == $this_user->getMemberNumber()) || $user->isAdmin()){ ?>
<div class="row profile">
	<div class="col-md-3">
		<div class="member-avatar">
            <img src="photo.php?name=" class="display"/>
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
