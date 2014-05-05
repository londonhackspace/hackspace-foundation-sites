<?
$page = 'faces';
require( '../header.php' );

ensureLogin();

if($user->isMember()) { 
    $newUsersCount = 0;
    $newUsers = $db->translatedQuery( "SELECT users.id, photo, full_name, (SELECT min(timestamp) FROM transactions WHERE user_id = users.id) AS first FROM users, users_profiles p WHERE users.id = p.user_id AND first like '".date('Y-m')."%' AND photo != '' AND users.disabled_profile = 0 AND subscribed = 1 ORDER BY users.id DESC");
    if($newUsers->countReturnedRows() > 0) {
?>
<h4>New members in <?=date('F')?>, go up and say hi!</h4> 

        <?php
        foreach( $newUsers as $user ) { 
			if($user['photo'] != null && $user['photo'] != '') { ?>
            	<a href="/members/profile/<?=$user['id']?>"><img style="max-width: 80px;padding-bottom:5px;" src="/members/photo/<?=$user['photo'] ?>_med.png" alt="<?=htmlspecialchars($user['full_name']) ?>" title="<?=htmlspecialchars($user['full_name'])?>"/></a>
        <?	}
        } 
    }

    $users = $db->translatedQuery( "SELECT id, photo, full_name FROM users JOIN users_profiles ON (id=user_id) WHERE photo != '' AND users.disabled_profile = 0 AND subscribed = 1 ORDER BY lower(full_name)"); ?>

<h2><?=$users->countReturnedRows()?> faces of London Hackspace.</h2> 

<?  foreach( $users as $user ) { ?>
    <a href="/members/profile/<?=$user['id']?>"><img style="max-width: 80px;padding-bottom:5px;" src="/members/photo/<?=$user['photo'] ?>_med.png" alt="<?=htmlspecialchars($user['full_name']) ?>" title="<?=htmlspecialchars($user['full_name'])?>"/></a>
<? } 
 } else { ?>
   <p>You must be a member to use this page.</p>
<?php } 

require('../footer.php'); ?>
</body>
</html>
