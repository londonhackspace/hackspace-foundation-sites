<?php
require_once( $_SERVER['DOCUMENT_ROOT'] . '/../lib/init.php');

$subscribers = $db->translatedQuery( "
  select
    u.id,
    u.subscribed,
    u.full_name,
    u.ldapuser,
    coalesce(u.nickname, u.full_name) nick,
    u.gladosfile,
    c.uid
  from
    cards c,
    users u
  where
    c.user_id = u.id
    and c.active = true
  order by
    u.id
" );

$lastid = null;
$subs = array();

foreach( $subscribers as $row ) {
  if ($row['id'] != $lastid) {
    $lastid = $row['id'];
    $sub = array(
      'id' => $row['id'],
      'name' => $row['full_name'],
      'nick' => $row['nick'],
      'ldap' => $row['ldapuser'],
      'subscribed' => $row['subscribed'] == 't',
      'gladosfile' => $row['gladosfile'],
      'perms' => array(),
      'cards' => array()
    );
    $subs[] = $sub;
  }
  $subs[count($subs)-1]['cards'][] = $row['uid'];
}
echo json_encode($subs);

/*
Allow permissions to delegated - button on tool control system to say "I allow this person"
*/


/*[{
  'id': 1,
  'irc_nick': 'hax0r',
  'full_name': 'Lee Thacker',
  'perms': ['laser', 'makerbot'],
  'cards': ['abcd1234'],
}, {...}, ...]
*/
