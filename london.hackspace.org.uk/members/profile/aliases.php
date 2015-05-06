<ul class="contact-list">
  <? if($this_user->getEmergencyName() && $this_user->getEmergencyPhone()) { ?>
    <li><span class="glyphicon glyphicon-heart"></span> I have an emergency contact </li>
  <? } ?>
  <? if($user_profile->getAllowEmail()) { ?>
    <li><span class="glyphicon glyphicon-envelope"></span> <a href="mailto:<?=$this_user->getEmail()?>"><?=$this_user->getEmail()?></a></li>
  <? } ?>
  <? if($user_profile->getWebsite() != '') { ?>
    <li><span class="glyphicon glyphicon-globe"></span> <a href="<?=$user_profile->getWebsite() ?>" target="_blank"><?=$user_profile->getWebsite() ?></a></li>
  <? } ?>
</ul>
<? if($this_user->hasUsersAliases()) {?>
  <h4>Aliases</h4>
  <ul class="aliases-list social-aliases">
    <? foreach($this_user->buildUsersAliases() as $alias) {?>
      <li><span class="member-social-icon iconlhs-<?=substr(strtolower(preg_replace("/[^0-9a-zA-Z]/","",$alias->getAliasId())),0,14);?>" title="<?=$alias->getAliasId()?>"></span>
        <? switch($alias->getAliasId()) {
          case 'Facebook': echo '<a target="_blank" href="https://www.facebook.com/' . $alias->getUsername() . '">' . $alias->getUsername() . '</a>'; break;
          case 'YouTube': echo '<a target="_blank" href="https://www.youtube.com/user/' . $alias->getUsername() . '">' . $alias->getUsername() . '</a>'; break;
          case 'GitHub': echo '<a target="_blank" href="https://github.com/' . $alias->getUsername() . '">' . $alias->getUsername() . '</a>'; break;
          case 'Google+': echo '<a target="_blank" href="https://plus.google.com/' . $alias->getUsername() . '">' . $alias->getUsername() . '</a>'; break;
          case 'Twitter': echo '<a target="_blank" href="https://twitter.com/' . $alias->getUsername() . '">' . $alias->getUsername() . '</a>'; break;
          case 'LinkedIn': echo '<a target="_blank" href="http://www.linkedin.com/in/' . $alias->getUsername() . '">' . $alias->getUsername() . '</a>'; break;
          case 'Flickr': echo '<a target="_blank" href="https://www.flickr.com/photos/' . $alias->getUsername() . '">' . $alias->getUsername() . '</a>'; break;
          case 'IRC': echo '<a target="_blank" href="http://webchat.freenode.net/?channels=london-hack-space">' . $alias->getUsername() . '</a>'; break;
          case 'Callsign': echo '<a target="_blank" href="https://wiki.london.hackspace.org.uk/view/Amateur_Radio_Callsigns">' . $alias->getUsername() . '</a>'; break;
          case 'Hackspace Wiki': echo '<a target="_blank" href="https://wiki.london.hackspace.org.uk/view/User:' . $alias->getUsername() . '">' . $alias->getUsername() . '</a>'; break;
          case 'XMPP/Jabber': echo '<a target="_blank" href="xmpp:' . $alias->getUsername() . '">' . $alias->getUsername() . '</a>'; break;
          case 'RSS': echo '<a target="_blank" href="' . $alias->getUsername() . '">RSS Feed</a>'; break;
          case 'Ello': echo '<a target="_blank" href="https://ello.co/' . $alias->getUsername() . '">' . $alias->getUsername() . '</a>'; break;
          // we don't do anything with 'Minecraft' atm
          default: echo $alias->getUsername();
        }?>
      </li>
    <? } ?>
  </ul>
<? } ?>
