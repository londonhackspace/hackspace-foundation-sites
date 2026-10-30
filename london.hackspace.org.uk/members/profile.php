<?php
require('../../lib/init.php');

$id = NULL;

if ( array_key_exists('PATH_INFO', $_SERVER) ) {
    $id = substr($_SERVER['PATH_INFO'], 1);
}

if (!isset($_GET['id']) && is_null($id) ) {
  fURL::redirect('/members/');
} else {
    if (is_null($id)) {
        $id = $_GET['id'];
    }
	try {
	  $this_user = new User(filter_var($id, FILTER_SANITIZE_NUMBER_INT));
	} catch(fNotFoundException $e) {
    header('HTTP/1.1 404 Not Found');
    echo "Profile not found";
    exit;
	}
}

if (is_null($user)) {
    $title = "Please login";
} else {
  if (($user->isMember() && $this_user->isMember())
	|| ($user->getMemberNumber() == $this_user->getMemberNumber())
	|| $user->isAdmin()) {
    $title = "Member Profile: {$this_user->getFullName()}";
  } else {
    $title = "Member Profile of non-subscriber";
  }
}
if ($user && $user->getMemberNumber() == $this_user->getMemberNumber()) {
  $page = 'profile';
} else {
  $page = 'memberslist';
}
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
	<?php } ?>
<div class="row profile profile-view">
	<div class="col-md-3">
    <div class="member-avatar">
        <span class="thumbnail">
            <img src="/members/photo.php?name=<?=$user_profile->getPhoto() ?>" class="display img-responsive" alt="User photo"/>
        </span>
    </div>
    <?php if($this_user->hasLearnings()) {?>
      <div class="member-training">
        <?php foreach($this_user->buildLearnings() as $training) {?>
			    <a href="<?=$training->getUrl()?>" class="training-badge" target="_blank">
            <img src="/images/trained-<?=strtolower(str_replace(' ','',$training->getName()));?>.png"
                 title="<?=$training->getDescription()?>"
                 alt="<?=$training->getDescription()?>"
            /></a>
        <?php } ?>
      </div>
    <?php } ?>
	</div>
  <div class="col-md-9">
      <?php if($user->getMemberNumber() == $this_user->getMemberNumber()) { ?>
        <small class="profile_edit">
          <a class="btn btn-default btn-sm" href="/members/profile_edit.php">Edit my profile</a>
        </small>
      <?php } ?>
      <div class="profile-heading">
        <h3>
          <?= htmlspecialchars($this_user->getFullName()) ?>
        </h3>
        <p class="details">
          <?=$this_user->getMemberNumber()?>
          <?php if($this_user->firstTransaction() != null) {
              echo ' joined in '.$this_user->firstTransaction();
            } if($user_profile->getAllowDoorbot() && $this_user->getDoorbotTimestamp() != '') {
              echo ', last seen '.date('dS M Y', strtotime($this_user->getDoorbotTimestamp()));
            } ?>
        </p>
      </div>
      <div class="profile-body">
        <ul class="contact-list">
            <?php if ($this_user->getEmergencyName() && $this_user->getEmergencyPhone()) { ?>
              <li><span class="glyphicon glyphicon-heart"></span> I have an emergency contact </li>
            <?php } ?>
            <?php if ($user_profile->getAllowEmail()) { ?>
              <li><span class="glyphicon glyphicon-envelope"></span> <a href="mailto:<?= $this_user->getEmail() ?>"><?= $this_user->getEmail() ?></a></li>
            <?php } ?>
            <?php if ($user_profile->getWebsite() != '') { ?>
              <li><span class="glyphicon glyphicon-globe"></span> <a href="<?= htmlspecialchars($user_profile->getWebsite()) ?>" target="_blank"><?= htmlspecialchars($user_profile->getWebsite()) ?></a></li>
            <?php } ?>
          </ul>
          <?php if ($this_user->hasUsersAliases()) { ?>
            <h4>Aliases</h4>
            <ul class="aliases-list social-aliases">
              <?php foreach ($this_user->buildUsersAliases() as $alias) { ?>
                <li><span class="member-social-icon iconlhs-<?= substr(strtolower(preg_replace("/[^0-9a-zA-Z]/", "", $alias->getAliasId())), 0, 14); ?>" title="<?= $alias->getAliasId() ?>"></span>
                  <?php
                  $username_html = htmlspecialchars($alias->getUsername());
                  switch ($alias->getAliasId()) {
                    case 'Facebook':
                      echo '<a target="_blank" href="https://www.facebook.com/' . $username_html . '">' . $username_html . '</a>';
                      break;
                    case 'YouTube':
                      echo '<a target="_blank" href="https://www.youtube.com/user/' . $username_html . '">' . $username_html . '</a>';
                      break;
                    case 'GitHub':
                      echo '<a target="_blank" href="https://github.com/' . $username_html . '">' . $username_html . '</a>';
                      break;
                    case 'Google+':
                      echo '<a target="_blank" href="https://plus.google.com/' . $username_html . '">' . $username_html . '</a>';
                      break;
                    case 'Twitter':
                      echo '<a target="_blank" href="https://twitter.com/' . $username_html . '">' . $username_html . '</a>';
                      break;
                    case 'LinkedIn':
                      echo '<a target="_blank" href="http://www.linkedin.com/in/' . $username_html . '">' . $username_html . '</a>';
                      break;
                    case 'Flickr':
                      echo '<a target="_blank" href="https://www.flickr.com/photos/' . $username_html . '">' . $username_html . '</a>';
                      break;
                    case 'IRC':
                      echo '<a target="_blank" href="http://webchat.freenode.net/?channels=london-hack-space">' . $username_html . '</a>';
                      break;
                    case 'Callsign':
                      echo '<a target="_blank" href="https://wiki.london.hackspace.org.uk/view/Amateur_Radio_Callsigns">' . $username_html . '</a>';
                      break;
                    case 'Hackspace Wiki':
                      echo '<a target="_blank" href="https://wiki.london.hackspace.org.uk/view/User:' . $username_html . '">' . $username_html . '</a>';
                      break;
                    case 'XMPP/Jabber':
                      echo '<a target="_blank" href="xmpp:' . $username_html . '">' . $username_html . '</a>';
                      break;
                    case 'RSS':
                      echo '<a target="_blank" href="' . $username_html . '">RSS Feed</a>';
                      break;
                    case 'Ello':
                      echo '<a target="_blank" href="https://ello.co/' . $username_html . '">' . $username_html . '</a>';
                      break;
                    case 'Fediverse':
                      list($null, $user_name, $user_domain) = explode('@', $username_html);
                      echo '<a target="_blank" href="https://' . $user_domain . '/@' . $user_name . '">' . $username_html . '</a>';
                      break;
                    case 'Bluesky':
                      echo '<a target="_blank" href="https://bsky.app/profile/' . $username_html . '">' . $username_html . '</a>';
                      break;
                      // we don't do anything with 'Minecraft' atm
                    default:
                      echo $username_html;
                  } ?>
                </li>
              <?php } ?>
            </ul>
          <?php } 
          $projects = $user_profile->getDescription();
         if(!empty($projects)) { ?>
          <h4>Projects I'm working on</h4>
          <p><?=nl2br(htmlspecialchars($projects ?? '')) ?></p>
        <?php }
           if($this_user->hasInterests()) {
            require('profile/interests.php');
           } ?>
      </div>
  </div>
</div>
<br/>
<?php } else if (($user->isMember() && $this_user->isMember()) || ($user->getMemberNumber() == $this_user->getMemberNumber()) || $user->isAdmin()){ ?>
<div class="row profile">
	<div class="col-md-3">
		<div class="member-avatar">
            <img src="/members/photo.php?name=" class="display"/>
    </div>
  </div>
	<div class="col-md-9">
		<?php if($user->getMemberNumber() == $this_user->getMemberNumber()) { ?>
		<small class="profile_edit"><a class="btn btn-default btn-sm" href="/members/profile_edit.php">Edit my profile</a></small>
		<?php } ?>

		<h3>
			<?= htmlspecialchars($this_user->getFullName()) ?>
			<p><small><?=$this_user->getMemberNumber()?></small></p>
		</h3>
    <?php if($this_user->getEmergencyName() && $this_user->getEmergencyPhone()) { ?>
    <ul class="contact-list">
      <li><span class="glyphicon glyphicon-heart"></span> I have supplied an emergency contact </li>
    </ul>
    <?php } ?>
  </div>
</div>
<?php } else { ?>
   <p>You don't have access to this page.</p>
<?php }
require('../footer.php'); ?>
</body>
</html>
