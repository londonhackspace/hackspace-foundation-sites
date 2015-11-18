#!/usr/bin/php

#
# If the LDAP DB gets wiped for some reason this script recreates the content.
# n.b. does not restore Admins, since we don't track that anywhere :/

<?php
echo "If you're running this, something's gone wrong.";
echo "Please make sure you sanitise everything in the database before running this script. Then remove these lines from the code.";

exit;

function __autoload($class_name)
{
    $flourish_root =  dirname(__FILE__) . '/../lib/flourish/';
    $file = $flourish_root . $class_name . '.php';
    if (file_exists($file)) {
        require_once($file);
        return;
    }
    throw new Exception('The class ' . $class_name . ' could not be loaded');
}
require_once(dirname(__FILE__) . '/../lib/user.php');
require_once(dirname(__FILE__) . '/../lib/usersprofile.php');
$db = new fDatabase('sqlite', dirname(__FILE__) . '/../var/database.db');
fORMDatabase::attach($db);

$users = fRecordSet::build('User',array('subscribed=' => '1'));

$now = new DateTime(date('Y-m-d'));
$nowTime = new DateTime();
echo "Starting process ".$nowTime->format('g:ia jS M')."\n";

if(count($users) > 0)
    echo(count($users)." users.\n\n"); 

foreach($users as $user) {

    $user_profile = $user->createUsersProfile();
    if ($user_profile->getAllowEmail() && $user->getLdapemail() == '') {
        $email = $user->getEmail();
    } else {
        $email = $user->getLdapemail();
    }
    
    $uid = $user->getId();
    $uid += 100000;
    $uid = escapeshellarg( $uid );
    
    echo "Adding ".$user->getLdapuser()."\n";
    
    $username = $user->getLdapuser();
    $nthash = $user->getLdapnthash();
    $sshahash = $user->getLdapsshahash();
    $shell = $user->getLdapshell();
    
    $success = trim( shell_exec( "sudo -g ldapadmin /var/www/hackspace-foundation-sites/bin/ldap-add.sh $username $uid $nthash $sshahash $shell $email 2>&1" ) );
    if( $success !== 'User added ok' ) {
        echo 'Error creating account:';
        echo $success;
        echo "\n";
    }

}



echo("All done.\n"); 
?>
