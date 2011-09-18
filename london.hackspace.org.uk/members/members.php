<? 
$page = 'memberslist';
require( '../header.php' );

if (!isset($user)) {
    fURL::redirect('/login.php?forward=/members/members.php');
}
?>

<h2>Members list</h2>
<?
if($user->isMember()) {
?>
    <p>This is a list of all members, up to date as of the last accounts reconciliation.</p>

    <table>
        <thead>
            <tr><th>Full name</th></tr>
        </thead>
        <tbody>
        <?php
        $users = $db->translatedQuery( 'SELECT full_name FROM users WHERE subscribed=1 ORDER BY lower(full_name)' );
        foreach( $users as $row ):
        ?>
            <tr>
                <td><?php echo htmlspecialchars( $row['full_name'] ) ?></td>
            </tr>
        <?php endforeach; ?>
        </tbody>
   </table>

   <?php $count = $db->translatedQuery( 'SELECT count(*) FROM users WHERE subscribed=1' )->fetchRow(); ?>
   <p><strong>Total:</strong> <?php echo $count['count(*)'] ?></p>

   <p><a href="index.php">Return to membership home</a></p>
<? } else { ?>
   <p>You must be a member to use this page.</p>
<?php } 

require('../footer.php'); ?>
