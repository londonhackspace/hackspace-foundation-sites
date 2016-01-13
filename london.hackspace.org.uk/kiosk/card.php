<?
$title = 'Membership Management';
$page = 'main';
require('./header.php');
$cardid = strtoupper($_GET['cardid']);
$cards = fRecordSet::build('Card', array('uid=' => $cardid));
if($cards->count() == 0) {
    fURL::redirect("/kiosk/addcard.php?cardid=" . $cardid);
}
$card = $cards->getRecord(0);
$user = new User($card->getUserId());
$user->load();

if ($user->isMember()) {
    $result = fRecordSet::build(
        'Transaction',
        array('user_id=' => $user->getId(),
            'timestamp>' => new fDate('2009-01-01'),
            'timestamp<' => new fDate('now')
        ),
        array('timestamp' => 'desc')
        );

    if (sizeof($result) > 1) {
        $expires = strtotime($result[0]->getTimestamp());
        # 30 days ~= a month
        # we don't include the 14 days grace period here.
        $expires += 30 * 24 * 60 * 60;
        $expires = date('d F Y', $expires);
    } else {
        # This is a special case for Russ, whose payments don't get
        # automatically recognised due to issues with payments between
        # barclays accounts on the same login
        $expires = null;
    }
}

?>

<? if ($user->isMember()) {
?>
<div class="alert alert-success" role="alert">
    <strong>Welcome, <?=$user->prepareFullName()?></strong>.
    You are a member of London Hackspace.
</div>
<? } else { ?>
<div class="alert alert-warning" role="alert">
    Hi, <?=$user->prepareFullName()?>.
    <strong>You are not currently a member of London Hackspace</strong>.
</div>
<? } ?>
<table class="table">
<tr><th>Member ID</th><td> <?=$user->getMemberNumber()?></td></tr>
<? if ($user->isMember() and $expires) { ?>
<tr><th>Subscription Expiry</th><td> <?=$expires ?></td></tr>
<? } ?>
</table>

<h2>Print Membership Stickers</h2>
<div class="btn-group">
<a href="/kiosk/storage.php?cardid=<?=$cardid?>" class="btn btn-default">Storage Request</a>
<a href="/kiosk/box.php?cardid=<?=$cardid?>" class="btn btn-default">Member Box</a>
</div>

<h2>Other Stickers</h2>

<div class="btn-group">
<a href="/kiosk/fixme.php?cardid=<?=$cardid?>" class="btn btn-default">Fix Me</a>
<a href="/kiosk/hackme.php?cardid=<?=$cardid?>" class="btn btn-default">Hack Me</a>
<a href="/kiosk/nod.php?cardid=<?=$cardid?>" class="btn btn-default">Notice of Disposal</a>
</div>

<? if ($user->getId() == 1) { ?>
<div class="btn-group">
    <a href="/kiosk/meeting.php?cardid=<?=$cardid?>" class="btn btn-default">Meeting Attendance</a>
</div>
<? } ?>

<?require('./footer.php')?>
</body>
</html>
