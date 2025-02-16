<ul class="contact-list">
  <? if($this_user->getEmergencyName() && $this_user->getEmergencyPhone()) { ?>
    <li><span class="glyphicon glyphicon-heart"></span> I have an emergency contact </li>
  <? } ?>
  <? if($user_profile->getAllowEmail()) { ?>
    <li><span class="glyphicon glyphicon-envelope"></span> <a href="mailto:<?=$this_user->getEmail()?>"><?=$this_user->getEmail()?></a></li>
  <? } ?>
  <? if($user_profile->getWebsite() != '') { ?>
    <li><span class="glyphicon glyphicon-globe"></span> <a href="<?=htmlspecialchars($user_profile->getWebsite()) ?>" target="_blank"><?=htmlspecialchars($user_profile->getWebsite()) ?></a></li>
  <? } ?>
</ul>
<? if($this_user->hasUsersAliases()) {?>
  <h4>Aliases</h4>
  <ul class="aliases-list social-aliases">
    <? foreach($this_user->buildUsersAliases() as $alias) {?>
      <li><span class="member-social-icon iconlhs-<?=substr(strtolower(preg_replace("/[^0-9a-zA-Z]/","",$alias->getAliasId())),0,14);?>" title="<?=$alias->getAliasId()?>"></span>
        <?
        $username_html = htmlspecialchars($alias->getUsername());
        switch($alias->getAliasId()) {
          case 'Facebook': echo '<a target="_blank" href="https://www.facebook.com/' . $username_html . '">' . $username_html . '</a>'; break;
          case 'YouTube': echo '<a target="_blank" href="https://www.youtube.com/user/' . $username_html . '">' . $username_html . '</a>'; break;
          case 'GitHub': echo '<a target="_blank" href="https://github.com/' . $username_html . '">' . $username_html . '</a>'; break;
          case 'Google+': echo '<a target="_blank" href="https://plus.google.com/' . $username_html . '">' . $username_html . '</a>'; break;
          case 'Twitter': echo '<a target="_blank" href="https://twitter.com/' . $username_html . '">' . $username_html . '</a>'; break;
          case 'LinkedIn': echo '<a target="_blank" href="http://www.linkedin.com/in/' . $username_html . '">' . $username_html . '</a>'; break;
          case 'Flickr': echo '<a target="_blank" href="https://www.flickr.com/photos/' . $username_html . '">' . $username_html . '</a>'; break;
          case 'IRC': echo '<a target="_blank" href="http://webchat.freenode.net/?channels=london-hack-space">' . $username_html . '</a>'; break;
          case 'Callsign': echo '<a target="_blank" href="https://wiki.london.hackspace.org.uk/view/Amateur_Radio_Callsigns">' . $username_html . '</a>'; break;
          case 'Hackspace Wiki': echo '<a target="_blank" href="https://wiki.london.hackspace.org.uk/view/User:' . $username_html . '">' . $username_html . '</a>'; break;
          case 'XMPP/Jabber': echo '<a target="_blank" href="xmpp:' . $username_html . '">' . $username_html . '</a>'; break;
          case 'RSS': echo '<a target="_blank" href="' . $username_html . '">RSS Feed</a>'; break;
          case 'Ello': echo '<a target="_blank" href="https://ello.co/' . $username_html . '">' . $username_html . '</a>'; break;
          case 'Fediverse': list($null, $user_name, $user_domain) = split('@',$username_html); echo '<a target="_blank" href="https://'. $user_domain. '/@' . $user_name . '">' . $username_html . '</a>'; break;
          // we don't do anything with 'Minecraft' atm
          default: echo $username_html;
        }?>
      </li>
    <? } ?>
  </ul>
<? } ?>
