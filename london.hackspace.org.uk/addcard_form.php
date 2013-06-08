<?
/*
 * For a card-adding station, return a page that can be filled in
 * with the scanned UID, to keep it out of the browser history.
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
