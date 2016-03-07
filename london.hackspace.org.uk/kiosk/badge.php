<?
$title = 'Name Bdge Sticker';
require('./header.php');
$cards = fRecordSet::build('Card', array('uid=' => $_GET['cardid']));
if($cards->count() == 0) {
    fURL::redirect("/kiosk/addcard.php?cardid=" . $_GET['cardid']);
}
$card = $cards->getRecord(0);
$user = new User($card->getUserId());
$user->load();
$user_profile = $user->createUsersProfile();
/*

Hello my name is
[name]
and you can find me on
irc:
email:
the web:


*/
if (isset($_POST['print']) && $user->isMember()) {
    $data = array(
        'name' => $user->getFull_Name(),
        'items' => array(),
    );

    if($user_profile->getAllowEmail()) { 
        $data['items']['email'] = $user->getEmail();
    }

    if($user_profile->getWebsite() != '') {
        $data['items']['website'] = $user_profile->getWebsite();
    }
    if($user->hasUsersAliases()) {
        foreach($user->buildUsersAliases() as $alias) {
            $data['items'][$alias->getAliasId()] = $alias->getUsername();
            // fix up twitter handles
            if ($alias->getAliasId() == 'Twitter') {
                if ($alias->getUsername()[0] != '@') {
                    $data['items'][$alias->getAliasId()] = '@' . $alias->getUsername();
                }
            }
        }
    }

    $data_string = json_encode($data);
    echo $data_string;
#    $ch = curl_init('http://kiosk.london.hackspace.org.uk:12345/print/badge');
#    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
#    curl_setopt($ch, CURLOPT_POSTFIELDS, $data_string);
#    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
#    curl_setopt($ch, CURLOPT_HTTPHEADER, array(
#                'Content-Type: application/json',
#                'Content-Length: ' . strlen($data_string)));
#    $result = curl_exec($ch);
#    curl_close($ch);
    echo("<p>Your sticker is being printed now.</p>");
}

?>

<? if($user->isMember()) { ?>

<p>On this page you can print a name badge to stick to yourself.</p>

<p>It will have:</p>

<form method="post">
<table class="table">
<tbody>
    <tr><th>Your name:</th><td><?=$user->getFull_Name()?></td></tr>

    <? if($user_profile->getAllowEmail()) { ?>
    <tr><th>Your email:</th><td><?=$user->getEmail()?></td></tr>
    <? } ?>

    <? if($user_profile->getWebsite() != '') { ?>
    <tr><th>Your website:</th><td><?=$user_profile->getWebsite() ?></td></tr>
    <? } ?>
    <? if($user->hasUsersAliases()) {?>
    <? foreach($user->buildUsersAliases() as $alias) {?>
        <tr><th><?=$alias->getAliasId()?>:</th><td><?=$alias->getUsername()?></td></tr>
    <? } ?>
    <? } ?>

    <tr><td colspan="2">... and a qr code with a link to your profile page.</td></tr>
    <tr><td><button name="print" value="<?=$user->getId()?>" class="btn btn-primary">Print Sticker</button></td></tr>
</tbody>
</table>
</form>
<? } ?>

<?require('./footer.php')?>
</body>
</html>
 
