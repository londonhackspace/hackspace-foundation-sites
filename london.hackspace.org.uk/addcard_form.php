<?
/*
 * For a card-adding station return a page that can be filled in with the scanned UID
 * 
 * There are two ways to use this page (the second is preferable on a shared machine)
 * 
 * - start a browser with the UID in the querystring (with --incognito or equivalent)
 * - download temporarily, replace {0} with the UID, and point a browser at that file
 * 
 * This page will only be served over SSL and will redirect the user to a login page.
 * 
 */

require_once( $_SERVER['DOCUMENT_ROOT'] . '/../lib/init.php');
if ($user) {
    fSession::destroy();
    fURL::redirect();
}

$uid = isset($_GET['uid']) ? htmlentities($_GET['uid']) : '{0}';

?>
<!DOCTYPE html>
<html>
<head>
    <title>Add card redirect</title>
    <style type="text/css">
        form {display: none}
    </style>
</head>
<body>
    <p>Redirecting, please wait...</p>
    <form name="addcard" action="<?=fURL::getDomain() ?>/login_and_addcard.php" method="post">
        <label for="uid">Card to add</label>
        <input type="text" name="uid" value="<?=$uid?>"/>
        <input type="submit" name="addcard" value="Add"/>
    </form>
    <script type="text/javascript">
        document.forms.addcard.submit();
    </script>
</body>
</html>
<?
