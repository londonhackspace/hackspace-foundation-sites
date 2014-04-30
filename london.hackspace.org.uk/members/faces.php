<?
$page = 'faces';
require( '../header.php' );

ensureLogin();

if($user->isMember()) { 
    $newUsersCount = 0;
    $newUsers = $db->translatedQuery( "SELECT DISTINCT t1.user_id, photo, full_name, (SELECT timestamp FROM transactions AS t2 WHERE t1.user_id = t2.user_id ORDER BY t2.timestamp ASC LIMIT 1) AS first FROM transactions AS t1, users, users_profiles WHERE t1.user_id = users.id AND users.id = users_profiles.user_id AND first like '".date('Y-m')."%' AND photo != '' ORDER BY t1.user_id DESC;");
    if($newUsers->countReturnedRows() > 0) {
?>
<h4>New members in <?=date('F')?>, go up and say hi!</h4> 

        <?php
        foreach( $newUsers as $user ) { 
			if($user['photo'] != null && $user['photo'] != '') { ?>
            	<a href="/members/profile/<?=$user['user_id']?>"><img style="max-width: 80px;" src="/members/photo.php?name=<?=$user['photo'] ?>&amp;size=med" alt="<?=htmlspecialchars($user['full_name']) ?>" title="<?=htmlspecialchars($user['full_name'])?>"/></a>
        <?	}
        } 
    }

    $users = $db->translatedQuery( "SELECT id, photo, full_name FROM users JOIN users_profiles ON (id=user_id) WHERE photo != '' ORDER BY lower(full_name)"); ?>

<h2><?=$users->countReturnedRows()?> faces of London Hackspace.</h2> 

<?  foreach( $users as $user ) { ?>
            	<a href="/members/profile/<?=$user['id']?>"><img style="max-width: 80px;padding-bottom:5px;" src="/members/photo.php?name=<?=$user['photo'] ?>&amp;size=med" alt="<?=htmlspecialchars($user['full_name']) ?>" title="<?=htmlspecialchars($user['full_name'])?>"/></a>
<? } 
 } else { ?>
   <p>You must be a member to use this page.</p>
<?php } 

require('../footer.php'); ?>
</body>
</html>
