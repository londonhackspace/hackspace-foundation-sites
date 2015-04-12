#!/usr/bin/php

<?php
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
require_once(dirname(__FILE__) . '/../lib/project.php');
$db = new fDatabase('sqlite', dirname(__FILE__) . '/../var/database.db');
fORMDatabase::attach($db);

$projects = fRecordSet::build('Project',array('state_id='=>array('1','2','4','5')), array('id' => 'asc'));
$now = new DateTime(date('Y-m-d'));
$nowTime = new DateTime();
echo "Starting process ".$nowTime->format('g:ia jS M')."\n";

if(count($projects) > 0)
    echo(count($projects)." outstanding storage request(s).\n"); 

foreach($projects as $project) {
    echo('checking for changes to "#'.$project->getId() . ': ' . $project->getName()."\" ...\n");

    $from = new DateTime($project->getFromDate());
    $to = new DateTime($project->getToDate());
    $user = new User($project->getUserId());
    $extension = $to->modify('+'.$project->getExtensionDuration().' days');
    $to->modify('-'.$project->getExtensionDuration().' days');
    $logs = fRecordSet::build('ProjectsLog',array('project_id=' => $project->getId()));
    if(count($logs) > 0)
        $postedTime = new DateTime(date("c", $logs[0]->getTimestamp()));

    // We only care about the latest due date if it's been extended
    if($project->hasExtension())
        $to = $extension;

    // automatically unapprove projects when user is unsubscribed
    if(!$user->isMember() && $project->getState() == 'Pending Approval') {
        $project->setState('Unapproved');
        $project->store();

        // email the owner
        $message = "Dear ".htmlspecialchars($user->getFullName()).",<br/><br/>". 
            "Just to let you know our records show you're no longer a paying member at London Hackspace. Your outstanding storage request <a href=\"https://london.hackspace.org.uk/storage/".$project->getId()."\">".$project->getName()."</a> has been automatically unapproved. If you're having trouble with your membership payment please <a href=\"mailto:contact@london.hackspace.org.uk\">get in touch</a>.<br/><br/>".
            "Best,<br/>".
            "Monkeys in the machine";
        $subject = 'London Hackspace Storage Request #'.$project->getId().': '.$project->getName();
        $headers = 'From: no-reply@london.hackspace.org.uk' . "\r\n" .
            'Reply-To: contact@london.hackspace.org.uk' . "\r\n" .
            'Content-Type:text/html;charset=utf-8' . "\r\n" .
            'X-Mailer: PHP/' . phpversion();
        mail($user->getEmail(), $subject, $message, $headers);

        // log the update
        $logmsg = 'Owner isn\'t a subscribed member, status automatically changed to '.$project->getState();
        $project->submitLog($logmsg,false);
        $project->submitMailingList($logmsg);
        echo($logmsg."\n");
    }

    // automatically pass the deadline on projects when the user is unsubscribed
    if(!$user->isMember() && ($project->getState() == 'Approved' || $project->getState() == 'Extended')) {
        $project->setState('Passed Deadline');
        $project->store();

        // email the owner
        $message = "Dear ".htmlspecialchars($user->getFullName()).",<br/><br/>". 
            "Just to let you know our records show you're no longer a paying member at London Hackspace. Your project <a href=\"https://london.hackspace.org.uk/storage/".$project->getId()."\">".$project->getName()."</a> currently approved for storage at London Hackspace has been automatically set to passed deadline. You'll need to remove it from the space ASAP. If you're having trouble with your membership payment please <a href=\"mailto:contact@london.hackspace.org.uk\">get in touch</a>.<br/><br/>".
            "Best,<br/>".
            "Monkeys in the machine";
        $subject = 'London Hackspace Storage Request #'.$project->getId().': '.$project->getName();
        $headers = 'From: no-reply@london.hackspace.org.uk' . "\r\n" .
            'Reply-To: contact@london.hackspace.org.uk' . "\r\n" .
            'Content-Type:text/html;charset=utf-8' . "\r\n" .
            'X-Mailer: PHP/' . phpversion();
        mail($user->getEmail(), $subject, $message, $headers);

        // log the update
        $logmsg = 'Owner isn\'t a subscribed member, status automatically changed to '.$project->getState();
        $project->submitLog($logmsg,false);
        $project->submitMailingList($logmsg);
        echo($logmsg."\n");
    }

    // automatically approve projects with no ML posts
    if($user->isMember() && $nowTime > $postedTime->modify('+'.$project->automaticApprovalDuration().' days') && $project->noActivity() && $project->getState() == 'Pending Approval') {
        // ML post count?
        $out = array();
        $pathToPhatomJs = '/usr/local/bin/phantomjs';
        $pathToJsScript = 'storage-requests-phantomjs-ml-scrape.js';
        $stdOut = exec(sprintf('%s %s %s', $pathToPhatomJs,  $pathToJsScript, $project->getId()), $out);

        // Approved it!
        if($out[0] == 'Posts found 1') {
            echo($project->automaticApprovalDuration()." days passed and no comments on the Mailing List. Approved!\n");
            $project->setState('Approved');
            $project->store();

            // email the owner
            $message = "Dear ".htmlspecialchars($user->getFullName()).",<br/><br/>". 
                "Just to let you know your project <a href=\"https://london.hackspace.org.uk/storage/".$project->getId()."\">".$project->getName()."</a> has been automatically approved for storage at London Hackspace.<br/><br/>".
                "Best,<br/>".
                "Monkeys in the machine";
            $subject = 'London Hackspace Storage Request #'.$project->getId().': '.$project->getName();
            $headers = 'From: no-reply@london.hackspace.org.uk' . "\r\n" .
                'Reply-To: no-reply@london.hackspace.org.uk' . "\r\n" .
                'Content-Type:text/html;charset=utf-8' . "\r\n" .
                'X-Mailer: PHP/' . phpversion();
            mail($user->getEmail(), $subject, $message, $headers);

            // log the update
            $logmsg = $project->automaticApprovalDuration().' days passed and no comments on the Mailing List, status automatically changed to '.$project->getState();
            $project->submitLog($logmsg,false);
            $project->submitMailingList($logmsg);
        }
    }

    // standard message
    $message = "Dear ".htmlspecialchars($user->getFullName()).",<br/><br/>". 
        "This is a friendly reminder that you had committed to remove your project <a href=\"https://london.hackspace.org.uk/storage/".$project->getId()."\">".$project->getName()."</a> from the London Hackspace by ".$to->format('jS M Y').".<br/><br/>";

    if(!$project->hasExtension()) {
        $message .= "We know life and other commitments can get in the way of hackspace projects. To help you finish up and organise your belongings you can extend your deadline once for ".$project->getExtensionDuration()." days <a href=\"https://london.hackspace.org.uk/storage/".$project->getId()."\">the request page</a>.<br/><br/>";
    }

    $message .= "If you've already removed your project from the space, you can disable these alerts by marking your project as 'Removed' on <a href=\"https://london.hackspace.org.uk/storage/".$project->getId()."\">the request page</a>.<br/><br/>" .
        "If you need more time please <a href=\"https://london.hackspace.org.uk/storage/edit.php\">submit a new storage request</a>.<br/><br/>".
        "Best,<br/>".
        "Monkeys in the machine";

    $subject = 'London Hackspace Storage Request #'.$project->getId().': '.$project->getName();
    $headers = 'From: no-reply@london.hackspace.org.uk' . "\r\n" .
        'Reply-To: no-reply@london.hackspace.org.uk' . "\r\n" .
        'Content-Type:text/html;charset=utf-8' . "\r\n" .
        'X-Mailer: PHP/' . phpversion();

    // reminder email 3 days before removal
    if($from < $now && $now == $to->modify('-3 days') && $project->getState() != 'Pending Approval') {
        echo("3 days until deadline, sending reminder\n");
        mail($user->getEmail(), $subject, $message, $headers);
        $project->submitLog('Three days before removal, reminder email sent to owner',false);
    }
    $to->modify('+3 days');

    // reminder email a day after removal
    if($now == $to->modify('+1 day') && $project->getState() != 'Pending Approval') {
        echo("Day after deadline, sending reminder\n");
        mail($user->getEmail(), $subject, $message, $headers);
        $project->submitLog('Day after removal, reminder email sent to owner',false);
    }
    $to->modify('-1 day');

    // reminder email every 7 days after removal
    if($now > $to && $now->format('w') == $to->format('w') && $project->getState() != 'Pending Approval') {
        echo("Anniversary of deadline, sending reminder and logging\n");
        mail($user->getEmail(), $subject, $message, $headers);

        $logmsg = 'Week anniversary of Passed Deadline, reminder email sent to owner';
        $project->submitLog($logmsg,false);
        $project->submitMailingList($logmsg);
    }

    // a day after removal update the status to 'Passed Deadline'
    if($now >= $to->modify('+1 day') && $project->getState() != 'Pending Approval' && $project->getState() != 'Passed Deadline') {
        echo("Setting status to Passed Deadline and updating Mailing List\n");
        $project->setState('Passed Deadline');
        $project->store();

        $logmsg = 'Status automatically changed to '.$project->getState();
        $project->submitLog($logmsg,false);
        $project->submitMailingList($logmsg);
    }
    $to->modify('-1 day');

    // if it was never approved (manually or automatically) three weeks after it was meant to start update the status to 'Archived'
    if($now >= $from->modify('+21 days') && $project->getState() == 'Pending Approval') {
        echo("Old request detected, setting status to Archived\n");
        $project->setState('Archived');
        $project->store();

        $project->submitLog('Status automatically changed to Unapproved',false);
        $project->submitLog('Status automatically changed to '.$project->getState(),false);
    }
}
if(count($projects) == 0)
    echo("No outstanding requests.\n"); 
echo("All done.\n"); 
?>
