<?php
function __autoload($class_name)
{
    $flourish_root =  dirname(__FILE__) . '/../../lib/flourish/';
    $file = $flourish_root . $class_name . '.php';
    if (file_exists($file)) {
        require_once($file);
        return;
    }
    throw new Exception('The class ' . $class_name . ' could not be loaded');
}

require_once(dirname(__FILE__) . '/../../lib/user.php');
require_once(dirname(__FILE__) . '/../../lib/project.php');

$db = new fDatabase('sqlite', dirname(__FILE__) . '/../../var/database.db');

fORMDatabase::attach($db);

$projects = fRecordSet::build('Project',array('state='=>array('Approved','Passed Deadline','Extended')), array('name' => 'asc'));
foreach($projects as $project) {
    $from = new DateTime($project->getFromDate());
    $to = new DateTime($project->getToDate());
    $now = new DateTime(date('Y-m-d'));
    $user = new User($project->getUserId());
    $extension = $to->modify('+14 days');
    $logs = fRecordSet::build('ProjectsLog',array('project_id=' => $project->getId()));
    $then = new DateTime(date('Y-m-d',$logs[0]->getTimestamp()));
    $last = new DateTime(date('Y-m-d',$logs[count($logs)-1]->getTimestamp()));
    $shortTerm = false;
    $hasBeenExtended = false;

    // Short term projects only get a 2 day extension
    if($from <= $then->modify('+1 day') && $to <= $from->modify('+3 days')) {
        $shortTerm = true;
        $extension = $to->modify('+2 days');
    }
    // Has this project been extended
    $logs = fRecordSet::build('ProjectsLog',array('project_id=' => $project->getId(), 'details=' => 'Status changed to Extended'));
    if(count($logs) > 0) {
        $to = $extension;
        $hasBeenExtended = true;
    }

    // -----------
    // ----------- TODO
    // automatically approve indoor projects after 2 days with no ML posts
    if($now > $last->modify('+2 days') && $project->getLocationId != 'Yard') {
        // ML post count?
    }
    // automatically approve yard projects after 7 days with no ML posts
    if($now > $last->modify('+7 days') && $project->getLocationId == 'Yard') {
        // ML post count?
    }
    // LINK to extend status
    // LINK to remove status
    // -----------
    // -----------    

    if( // reminder email 3 few days before removal
        ($from < $now && $now == $to->modify('-3 days')) ||
        // reminder email on the day of removal
        ($now == $to) ||
        // reminder email a day after removal
        ($now == $to->modify('+1 day')) ||
        // reminder email every 7 days after removal
        ($now > $to && $now->format('w') == $to->format('w'))
    ) {
        $message = "Dear ".htmlspecialchars($user->getFullName()).",<br/><br/>". 
            "This is a friendly reminder that you had intended to remove your project '".$project->getName()."' on ".$from->format('jS M Y').".<br/>";

        if(!$hasBeenExtended && !$shortTerm) {
            $message .= "We know life and other commitments can get in the way of hackspace projects. To help you finish up and organise your belongings you can extend your deadline once for two weeks. Simply click the link below:<br/><br/>".
                "[link]<br/><br/>";
        } else if(!$hasBeenExtended && $shortTerm) {
            $message .= "We know life and other commitments can get in the way of hackspace projects. To help you finish up and organise your belongings you can extend your deadline once for two days. Simply click the link below:<br/><br/>".
                "[link]<br/><br/>";
        }

        $message .= "If you've already removed your project from the space, you can disable these alerts by marking your project as 'Removed'. Simply click the link below:" .
            "[link]<br/><br/>".
            "If you need more time please submit a new storage request via our website:<br/><br/>".
            "https://london.hackspace.org.uk/storage/<br/><br/>".
            "Thanks,<br/><br/>".
            "The London Hackspace trustees<br/>".
            "trustees@london.hackspace.org.uk";
        sendEmail($user->getEmail(),$message,$project);
    }

    // a day after removal update the status to 'Passed Deadline'
    if($now == $to->modify('+1 day')) {
        $project->setState('Passed Deadline');
        $project->store();

        // log the update
        $logmsg = 'Status automatically changed to '.$project->getState();
        $log = new ProjectsLog();
        $log->setProjectId($project->getId());
        $log->setTimestamp(time());
        $log->setDetails($logmsg);
        $log->store();

        // send to mailing list
        $toEmail = 'london-hack-space-test@googlegroups.com';
        $subject = 'Storage Request #'.$project->getId().': '.$project->getName();
        $message =  $logmsg;

        $headers = 'From: no-reply@london.hackspace.org.uk' . "\r\n" .
            'Reply-To: no-reply@london.hackspace.org.uk' . "\r\n" .
            'Content-Type:text/html;charset=utf-8' . "\r\n" .
            'X-Mailer: PHP/' . phpversion();

        mail($toEmail, $subject, $message, $headers);

        // log the google groups post
        $log = new ProjectsLog();
        $log->setProjectId($project->getId());
        $log->setTimestamp(time()+1);
        $log->setDetails('Posted to the Mailing List');
        $log->store();
    }

    // reminder email every 7 days after removal
    if($now > $to && $now->format('w') == $to->format('w')) {
        // log the update
        $logmsg = 'Reminder sent regarding passed deadline';
        $log = new ProjectsLog();
        $log->setProjectId($project->getId());
        $log->setTimestamp(time());
        $log->setDetails($logmsg);
        $log->store();

        // send to mailing list
        $toEmail = 'london-hack-space-test@googlegroups.com';
        $subject = 'Storage Request #'.$project->getId().': '.$project->getName();
        $message =  $logmsg;

        $headers = 'From: no-reply@london.hackspace.org.uk' . "\r\n" .
            'Reply-To: no-reply@london.hackspace.org.uk' . "\r\n" .
            'Content-Type:text/html;charset=utf-8' . "\r\n" .
            'X-Mailer: PHP/' . phpversion();

        mail($toEmail, $subject, $message, $headers);

        // log the google groups post
        $log = new ProjectsLog();
        $log->setProjectId($project->getId());
        $log->setTimestamp(time()+1);
        $log->setDetails('Posted to the Mailing List');
        $log->store();
    }
}

function sendEmail($to,$message,$project) {
    $subject = 'London Hackspace Storage Request #'.$project->getId().': '.$project->getName();
    $headers = 'From: trustees@london.hackspace.org.uk' . "\r\n" .
        'Reply-To: trustees@london.hackspace.org.uk' . "\r\n" .
        'Content-Type:text/html;charset=utf-8' . "\r\n" .
        'X-Mailer: PHP/' . phpversion();

    mail($to, $subject, $message, $headers);
}
?>