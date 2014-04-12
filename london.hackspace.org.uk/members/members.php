<? 
$page = 'memberslist';
require( '../header.php' );

if (!isset($user)) {
    fURL::redirect('/login.php?forward=/members/members.php');
}

$last = array_pop($db->query('select max(date(timestamp)) from transactions')->fetchRow());
$include_unsubscribed = ($user->isAdmin() && isset($_GET['unsubscribed']) && $_GET['unsubscribed'] == 'on') ? true : false;
?>

<h2>Members list</h2>
<? if($user->isMember()) { ?>
  <p>This is a list of all members, up to date as of the last accounts reconciliation (<?=$last?>).</p>
    <p>Please keep this list among members only.</p>

	<form class="search-form">
	    <div class="form-group">
			<div class="input-group">
				<input type="search" class="form-control" placeholder="Search...">
				<span class="input-group-btn">
					<button class="btn btn-primary"><span class="glyphicon glyphicon-search"></span></button>
				</span>
			</div>
	    </div>
	</form>
	<? if($user->isAdmin()) { ?>
	<form class="subscription-form" method="get">
		<div class="checkbox">
			<label>
				<input type="checkbox" <? if($include_unsubscribed) { echo 'checked'; } ?> name="unsubscribed" id="unsubscribed"> Include unsubscribed members
			</label>
		</div>
	</form>
	<? } ?>

	<p><strong>Total:</strong> <span class="search-total"></span></p>
    <table class="search tablesorter">
        <thead>
            <tr>
                <th class="member-id">ID</th>
				<? if($user->isAdmin()) { ?>
	                <th>E</th>
				<? } ?>
				<? if($include_unsubscribed) { ?>
	                <th>£</th>
				<? } ?>
                <th>Full name</th>
				<? if($user->isAdmin()) { ?>
	                <th>Profile</th>
	                <th>Doorbot</th>
				<? } ?>
            </tr>
        </thead>
        <tbody>
        <?php
        $subscription_query = ($include_unsubscribed) ? '' : 'WHERE subscribed=1';
        $users = $db->translatedQuery( 'SELECT id, subscribed, full_name, email, nickname, has_profile, disabled_profile FROM users '.$subscription_query.' ORDER BY lower(full_name)');
        foreach( $users as $row ):
        ?>
            <tr>
                <td class="member-id"><?= $row['id'] ?></td>
				<? if($user->isAdmin()) { ?>
					<td><a href="mailto:<?= htmlspecialchars( $row['email'] ) ?>" title="<?= htmlspecialchars( $row['email'] ) ?>"><span class="glyphicon glyphicon-envelope"></span></a><p class="hidden"><?= htmlspecialchars( $row['email'] ) ?></p></td>
				<? } ?>
				<? if($include_unsubscribed) { ?>
	                <td><? if($row['subscribed']) { ?><span class="glyphicon glyphicon-ok"></span><? } ?><p class="hidden"><?=($row['subscribed'] == 0) ? 'unsubscribed' : 'subscribed'; ?></p></td>
				<? } ?>
                <td>
					<? if(!$user->isAdmin() && $row['has_profile'] == 1 && $row['disabled_profile'] == 0) { ?>
	                	<a href="/members/profile.php?id=<?=$row['id']?>" title=""><?= htmlspecialchars( $row['full_name'] ) ?></a>
                	<? } else if(!$user->isAdmin()) { ?>
	               		<?= htmlspecialchars( $row['full_name'] ) ?>
                	<? } else { ?>
	                	<a href="/members/member.php?id=<?=$row['id']?>" title=""><?= htmlspecialchars( $row['full_name'] ) ?></a>
                	<? } ?>
                </td>
				<? if($user->isAdmin()) { ?>
	                <td>
					<? if($row['has_profile'] == 1 && $row['disabled_profile'] == 0) { ?>
	                	<p class="hidden">profile</p><a href="/members/profile.php?id=<?=$row['id']?>" title="visit member's profile"><span class="glyphicon glyphicon-user"></span></a>
					<? } ?>
	                </td>
		            <td><?= htmlspecialchars( $row['nickname'] ) ?></td>
				<? } ?>
            </tr>
        <?php endforeach; ?>
        </tbody>
   </table>
   <br/>

   <p><a href="index.php">Return to membership home</a></p>
<? } else { ?>
   <p>You must be a member to use this page.</p>
<?php } 

require('../footer.php'); ?>
<script type="text/javascript" src="/javascript/jquery.tablesorter.min.js"></script>
<script type="text/javascript">
$(document).ready(function() { 
	$(".search").tablesorter( {sortList: <? if($include_unsubscribed) echo "[[3,0]]"; else if($user->isAdmin()) echo "[[2,0]]"; else echo "[[1,0]]"; ?>} );
	$('.search-form button').click(function(e) {
		e.preventDefault;
		e.stopPropagation;
		$('.search-form input').trigger('keyup');
		return false;
	});
	<? if($user->isAdmin()) { ?>
	$('input[name="unsubscribed"]').change(function(e) {
		$('.subscription-form').submit();
	});
	<? } ?>
	$('input[type="search"]').keyup(function(e) {
		window.location.hash='#'+$(this).val();
		var searchString = $(this).val().toLowerCase();
		var count = 0;
		$('.search tbody tr').each(function() {
			$(this).removeClass('hidden');
			var found = 0;
			$('td',$(this)).each(function() {
				if($(this).text().toLowerCase().search(searchString) > -1 || ($('a', $(this)).attr('title') && $('a', $(this)).attr('title').toLowerCase().search(searchString) > -1))
					found++;
			});
			if(found==0) 
				$(this).addClass('hidden');
			else
				count++;
		});
		$('.search-total').text(count);
	}).val(window.location.hash.substring(1)).trigger('keyup');
});
</script>
</body>
</html>