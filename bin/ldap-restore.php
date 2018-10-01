#!/usr/bin/php

#
# If the LDAP DB gets wiped for some reason this script recreates the content.
# n.b. does not restore Admins, since we don't track that anywhere :/

<?php
#echo "If you're running this, something's gone wrong.";
#echo "Please make sure you sanitise everything in the database before running this script. Then remove these lines from the code.";

#exit;

#function __autoload($class_name)
#{
#    echo $class_name."\n";
#    $flourish_root =  dirname(__FILE__) . '/../lib/flourish/';
#    $file = $flourish_root . $class_name . '.php';
#    if (file_exists($file)) {
#        require_once($file);
#        return;
#    }
#    throw new Exception('The class ' . $class_name . ' could not be loaded');
#}

$_SERVER['DOCUMENT_ROOT'] = '/var/www/hackspace-foundation-sites/bin';

require_once(dirname(__FILE__) . '/../lib/init.php');
#require_once(dirname(__FILE__) . '/../lib/config.php');
#require_once(dirname(__FILE__) . '/../lib/flourish/fActiveRecord.php');

#require_once(dirname(__FILE__) . '/../lib/user.php');
#require_once(dirname(__FILE__) . '/../lib/usersprofile.php');

#$db = new fDatabase('postgresql', $DB_NAME, $DB_USER, $DB_PASSWORD);

#fORMDatabase::attach($db);

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

    if (!$user->isMember()) {
        continue;
    }
    
    $uid = $user->getId();
    $uid += 100000;
    $uid = escapeshellarg( $uid );
    
    $username = $user->getLdapuser();
    $nthash = $user->getLdapnthash();
    $sshahash = $user->getLdapsshahash();
    $shell = $user->getLdapshell();

    if ($shell == '') {
        continue;
    }

    if ($username == '') {
        continue;
    }

    if ($sshahash == '') {
        continue;
    }

    echo "Adding ".$user->getLdapuser()." ".$user->isMember()." ".$user->isAdmin()."\n";
    
    $success = trim( shell_exec( "/var/www/hackspace-foundation-sites/bin/ldap-add.sh $username $uid $nthash $sshahash $shell $email 2>&1" ) );
    if( $success !== 'User added ok' ) {
        echo 'Error creating account:';
        echo $success;
        echo "\n";
    }
    
    if ($user->isAdmin()) {
        echo "adding ".$username." to admins";
    }

}



echo("All done.\n"); 
?>
