<?php
require_once( $_SERVER['DOCUMENT_ROOT'] . '/../lib/init.php');

$subscribers = $db->translatedQuery( "
  select
    u.id,
    u.subscribed,
    ifnull(u.nickname, u.full_name) nick,
    u.gladosfile,
    c.uid
  from
    cards c,
    users u
  where
    c.user_id = u.id
    and c.active = 1
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
      'nick' => $row['nick'],
      'subscribed' => $row['subscribed'] == 1,
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
