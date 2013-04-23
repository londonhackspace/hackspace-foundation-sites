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
    <p>Please keep this list among members only.</p>

    <table>
        <thead>
            <tr>
                <th class="member-id"><a class="sortable" href="?order=id">ID</a></th>
                <th><a class="sortable" href="?order=name">Full name</a></th>
            </tr>
        </thead>
        <tbody>
        <?php
        $order = 'lower(full_name)';
        if ($_GET['order'] == 'id') $order = 'id';
        $users = $db->translatedQuery( 'SELECT id, full_name FROM users WHERE subscribed=1 ORDER BY ' . $order );
        foreach( $users as $row ):
        ?>
            <tr>
                <th class="member-id"><?php echo $row['id'] ?></th>
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
