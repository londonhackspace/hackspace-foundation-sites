<?
$title = 'Name Badge Sticker';
require('./header.php');

ensureKioskUser();
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
        'name' => $user->getFullName(),
        'items' => array(),
    );

    if($user_profile->getAllowEmail() && isset($_POST['email'])) {
        $data['items']['email'] = $user->getEmail();
    }

    if($user_profile->getWebsite() != '' && isset($_POST['website'])) {
        $data['items']['website'] = $user_profile->getWebsite();
    }
    if($user->hasUsersAliases()) {
        foreach($user->buildUsersAliases() as $alias) {
            if (isset($_POST[$alias->getAliasId()])) {
                $data['items'][$alias->getAliasId()] = $alias->getUsername();
                // fix up twitter handles
                if ($alias->getAliasId() == 'Twitter') {
                    if ($alias->getUsername()[0] != '@') {
                        $data['items'][$alias->getAliasId()] = '@' . $alias->getUsername();
                    }
                }
            }
        }
    }

    $data_string = json_encode($data);
    $ch = curl_init('http://kiosk.london.hackspace.org.uk:12345/print/badge');
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
    curl_setopt($ch, CURLOPT_POSTFIELDS, $data_string);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, array(
                'Content-Type: application/json',
                'Content-Length: ' . strlen($data_string)));
    $result = curl_exec($ch);
    curl_close($ch);
    echo("<p>Your sticker is being printed now.</p>");
}

function is_checked($thing)
{
    if (isset($_POST['print'])) {
        if (isset($_POST[$thing])) {
            return "checked";
        }
        return "";
    }
    return "checked";
}

?>

<? if($user->isMember()) { ?>

<p>On this page you can print a name badge to stick to yourself.</p>

<p>It will have:</p>

<form method="post">
<table class="table">
<tbody>
    <tr><th>Your name:</th><td><?=htmlspecialchars($user->getFullName())?></td></tr>

    <? if($user_profile->getAllowEmail()) { ?>
    <!-- <input type="checkbox" id="checkbox-2-1" class="regular-checkbox big-checkbox" /><label for="checkbox-2-1"></label> -->
    <tr><th><input type="checkbox" <?=is_checked("email")?> name="email" id="email" class="regular-checkbox big-checkbox" /><label for="email"></label> Your email:</th><td><?=htmlspecialchars($user->getEmail()) ?></td></tr>
    <? } ?>

    <? if($user_profile->getWebsite() != '') { ?>
    <tr><th><input type="checkbox" <?=is_checked("website")?> name="website" id="website" class="regular-checkbox big-checkbox" /><label for="website"></label> Your website:</th><td><?=htmlspecialchars($user_profile->getWebsite()) ?></td></tr>
    <? } ?>
    <? if($user->hasUsersAliases()) {?>
    <? foreach($user->buildUsersAliases() as $alias) {?>
        <tr><th><input type="checkbox" <?=is_checked($alias->getAliasId())?> name="<?=$alias->getAliasId()?>" id="<?=$alias->getAliasId()?>" class="regular-checkbox big-checkbox" /><label for="<?=$alias->getAliasId()?>"></label> <?=$alias->getAliasId()?>:</th><td><?=htmlspecialchars($alias->getUsername()) ?></td></tr>
    <? } ?>
    <? } ?>

    <tr><td><button name="print" value="<?=$user->getId()?>" class="btn btn-primary">Print Sticker</button></td></tr>
</tbody>
</table>
</form>
<? } ?>

<?require('./footer.php')?>
</body>
</html>
 
