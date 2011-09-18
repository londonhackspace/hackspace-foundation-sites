<? 
$page = 'wiki';
require( '../header.php' );

if (!$user) {
    fURL::redirect('/login.php?forward=/members/wiki.php');
}
?>
<h2>Wiki Account</h2>
<?

if($user->isMember()) {
    $email = $user->getEmail();


    // Make database connection.
    require $_SERVER['DOCUMENT_ROOT'] . '/../var/mediawiki.php';
    $db = new fDatabase($type, $database, $username, $password, $host, $port );

    // Link or unlink a user.
    if( ( array_key_exists( 'link', $_POST ) || array_key_exists( 'unlink', $_POST ) ) && array_key_exists( 'wikiuser', $_POST ) ) {
        fRequest::validateCSRFToken($_POST['token']);
        $user = (int) $_POST['wikiuser'];
        // Check that the MediaWiki and Hackspace e-mails match (and the former is confirmed).
        try {
            $db->translatedQuery( 'SELECT user_id FROM mwuser WHERE user_id=%i AND user_email=%s AND user_email_authenticated IS NOT NULL', $user, $email )->fetchRow();
            if( array_key_exists( 'link', $_POST ) ) {
                // Check that the MediaWiki user is not already a member of the 'sysop' group.
                try {
                    $db->translatedQuery( 'SELECT ug_user FROM user_groups WHERE ug_user=%i AND ug_group=\'sysop\'', $user )->fetchRow();
                } catch( fNoRowsException $e ) {
                    // Add the MediaWiki user to the 'sysop' group.
                    $db->translatedQuery( 'INSERT INTO user_groups VALUES (%i,\'sysop\')', $user );
                }
            } elseif( array_key_exists( 'unlink', $_POST ) ) {
                // Delete the MediaWiki user from the 'sysop' group.
                $db->translatedQuery( 'DELETE FROM user_groups WHERE ug_user=%i AND ug_group=\'sysop\'', $user );
            }
        } catch( fNoRowsException $e ) {
            echo '<p>That wiki account does not have a confirmed e-mail that matches the e-mail of your Hackspace account.</p>';
        }
    } elseif( array_key_exists( 'create', $_POST ) ) {
        fRequest::validateCSRFToken($_POST['token']);
        try {
            $validator = new fValidation();
            $validator->addRequiredFields( 'username', 'password' );
            $validator->validate();
            if( $_POST['password'] !== $_POST['passwordconfirm'] ) {
                throw new fValidationException( '<p>Passwords do not match.</p>' );
            }

            // Attempt account creation and promotion.
            $username = escapeshellarg( $_POST['username'] );
            $password = escapeshellarg( $_POST['password'] );
            $success = trim( shell_exec( "unset REQUEST_METHOD;php {$path}maintenance/createAndPromote.php --globals $username $password 2>&1 1> /dev/null" ) );
            if( $success === 'account exists.' ) {
                throw new fValidationException( '<p>An account on the wiki with that username already exists.</p>' );
            } elseif( $success !== '' ) {
                throw new fValidationException( '<p>An unknown error ocurred while creating that wiki account, please contact IRC.</p>' );
            } else {
                // Update e-mail address for created user.
                $username = $_POST['username'];
                $db->translatedQuery( 'UPDATE mwuser SET user_email=%s,user_email_authenticated=%s WHERE user_name=%s', $email, date( 'Y-m-d H:i:s' ), $username );
            }
        } catch (fValidationException $e) {
            $error = $e->getMessage();
        } catch (fSQLException $e) {
            $error = "<p>An unexpected SQL error occurred, please contact IRC.</p>";
            trigger_error( $e->getMessage() );
        }
    }

    // Prepare an array of users with MediaWiki and Hackspace e-mails that match (and the former is confirmed).
    $accounts = array();
    $result = $db->translatedQuery( 'SELECT user_id,user_name FROM mwuser WHERE user_email=%s AND user_email_authenticated IS NOT NULL', $email );
    foreach( $result as $row ) {
        // Identify whether or not the account is a 'sysop'.
        $admin = $db->translatedQuery( 'SELECT COUNT(*) FROM user_groups WHERE ug_user=%i AND ug_group=\'sysop\'', $row['user_id'] )->fetchRow();
        $accounts[$row['user_id']] = array(
            'username' => $row['user_name'],
            'linked' => (int) ( $admin['count'] > 0 )
        );
    }
?>
    <p>As a member of London Hackspace you are entitled to administrator rights on <a href="http://wiki.london.hackspace.org.uk">our wiki</a>.</p>
    <?php if( count( $accounts ) > 0 ): ?>
        <p>These wiki accounts have the same confirmed e-mail address as your membership account:</p>
        <table>
            <thead>
                <tr>
                    <th>Username</th>
                    <th>Administrator</th>
                    <th>Options</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach( $accounts as $id => $details ): ?>
                    <tr>
                        <td><?php echo $details['username'] ?></td>
                        <td><?php echo $details['linked'] ? 'Yes' : 'No' ?></td>
                        <td>
                            <form method="POST" style="margin: 0;">
                                <input type="hidden" name="token" value="<?=fRequest::generateCSRFToken()?>" />
                                <input type="hidden" name="wikiuser" value="<?php echo $id ?>" />
                                <input type="submit" name="<?php echo $details['linked'] ? 'unlink' : 'link' ?>" value="<?php echo $details['linked'] ? 'Unlink' : 'Link' ?>" />
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php else: ?>
        <p>No account currently exists on the wiki with the same confirmed e-mail address as your London Hackspace account.  If you already have an account on the wiki you should set or update your e-mail address there, otherwise use the form below to create a wiki account now.</p>
        <hr />
        <?php if( isset( $error ) ) echo $error; ?>
        <form method="POST">
            <input type="hidden" name="token" value="<?=fRequest::generateCSRFToken()?>" />
            <table>
                <tr>
                    <td><label for="username">Wiki username</label></td>
                    <td><input type="text" name="username" value="<?php if( array_key_exists( 'username', $_POST ) ) echo $_POST['username'] ?>" /></td>
                </tr>
                <tr>
                    <td><label for="password">Wiki password</label></td>
                    <td><input type="password" name="password" /></td>
                </tr>
                <tr>
                    <td><label for="passwordconfirm">Confirm wiki password</label></td>
                    <td><input type="password" name="passwordconfirm" /></td>
                </tr>
                <tr>
                    <td colspan="2"><input type="submit" name="create" value="Create" /></td>
                </tr>
            </table>
        </form>
        <br />
    <?php endif; ?>
    <p><a href="index.php">Return to membership home</a></p>
<? } else { ?>
    <p>You must be a member to use this page.</p>
<?php } 

require('../footer.php'); ?>
