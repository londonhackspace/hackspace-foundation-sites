<? 
$page = 'membership';
require('../header.php'); 

if (!$user) {
    fURL::redirect('/');
}
?>
<h2>Wiki Account</h2>
<?

if($user->isMember()) {
    $email = $user->getEmail();

    // Make database connection.
    require $_SERVER['DOCUMENT_ROOT'] . '/../etc/mediawiki.php';
    mysql_connect( $server, $username, $password );
    mysql_select_db( $database );

    // Link or unlink a user.
    if( ( array_key_exists( 'link', $_POST ) || array_key_exists( 'unlink', $_POST ) ) && array_key_exists( 'wikiuser', $_POST ) ) {
        $user = (int) $_POST['wikiuser'];
        // Check that the MediaWiki and Hackspace e-mails match (and the former is confirmed).
        $confirmed = (bool) mysql_fetch_object( mysql_query('SELECT user_email,user_email_authenticated FROM user WHERE
                user_id=' . mysql_escape_string($user) . ' and
                user_email="' . mysql_escape_string($email) . '" and
                user_email_authenticated is not null' ) );
        if( $confirmed ) {
            if( array_key_exists( 'link', $_POST ) ) {
                // Check that the MediaWiki user is not already a member of the 'sysop' group.
                $exists = (bool) mysql_fetch_object( mysql_query( 'SELECT ug_user FROM user_groups WHERE
                    ug_user=' . mysql_escape_string($user) . ' and
                    ug_group="sysop"' ) );
                if( !$exists ) {
                    // Add the MediaWiki user to the 'sysop' group.
                    mysql_query( 'INSERT INTO user_groups SET ug_user=' . mysql_escape_string($user) . ',ug_group="sysop"' );
                }
            } elseif( array_key_exists( 'unlink', $_POST ) ) {
                // Delete the MediaWiki user from the 'sysop' group.
                mysql_query( 'DELETE FROM user_groups WHERE ug_user=' . mysql_escape_string($user) . ' AND ug_group="sysop"' );
            }
        }
    } elseif( array_key_exists( 'create', $_POST ) ) {
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
                error_reporting( E_ALL );
                $username = mysql_real_escape_string( $_POST['username'] );
                mysql_query( 'UPDATE user SET user_email="' . $email . '",user_email_authenticated="' . date( 'YmsHis' ) . '" WHERE user_name="' . $username . '"' );
            }

        } catch (fValidationException $e) {
            $error = $e->getMessage();
        } catch (fSQLException $e) {
            $error = "<p>An unexpected error occurred, please contact IRC.</p>";
            trigger_error( $e );
        }
    }

    // Prepare an array of users with MediaWiki and Hackspace e-mails that match (and the former is confirmed).
    $accounts = array();
    $result = mysql_query( 'select user_id,user_name from user where
        user_email = "' . $email . '" and
        user_email_authenticated is not null' );
    while( $row = mysql_fetch_object( $result ) ) {
        // Identify whether or not the account is a 'sysop'.
        $admin = (bool) mysql_fetch_object( mysql_query( 'select * from user_groups where
            ug_user=' . $row->user_id . ' and ug_group="sysop"' ) );
        $accounts[$row->user_id] = array(
            'username' => $row->user_name,
            'linked' => $admin
        );
    }
?>
    <p>As a member of the Hackspace Foundation you are entitled to administrator rights on <a href="http://wiki.hackspace.org.uk">our wiki</a>.</p>
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
                                <input type="hidden" name="wikiuser" value="<?php echo $id ?>">
                                <input type="submit" name="<?php echo $details['linked'] ? 'unlink' : 'link' ?>" value="<?php echo $details['linked'] ? 'Unlink' : 'Link' ?>">
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php else: ?>
        <p>No account currently exists on the wiki with the same confirmed e-mail address as your Hackspace Foundation account.  If you already have an account on the wiki you should set or update your e-mail address there, otherwise use the form below to create a wiki account now.</p>
        <hr>
        <?php if( isset( $error ) ) echo $error; ?>
        <form method="POST">
            <table>
                <tr>
                    <td><label for="username">Wiki username</label></td>
                    <td><input type="text" name="username" value="<?php if( array_key_exists( 'username', $_POST ) ) echo $_POST['username'] ?>"></td>
                </tr>
                <tr>
                    <td><label for="password">Wiki password</label></td>
                    <td><input type="password" name="password"></td>
                </tr>
                <tr>
                    <td><label for="passwordconfirm">Confirm wiki password</label></td>
                    <td><input type="password" name="passwordconfirm"></td>
                </tr>
                <tr>
                    <td colspan="2"><input type="submit" name="create" value="Create"></td>
                </tr>
            </table>
        </form>
        <p></p>
    <?php endif; ?>
    <p><a href="index.php">Return to membership home</a></p>
<? } else { ?>
    <p>You must be a member to use this page.</p>
<?php } 

require('../footer.php'); ?>
