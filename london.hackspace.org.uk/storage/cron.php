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
$now = new DateTime(date('Y-m-d'));

foreach($projects as $project) {
    $from = new DateTime($project->getFromDate());
    $to = new DateTime($project->getToDate());
    $user = new User($project->getUserId());
    $extension = $to->modify('+'.$project->getExtensionDuration().' days');
    $logs = fRecordSet::build('ProjectsLog',array('project_id=' => $project->getId()));
    $last = new DateTime(date('Y-m-d',$logs[count($logs)-1]->getTimestamp()));

    // We only care about the latest due date if it's been extended
    if($project->hasExtension())
        $to = $extension;

    // -----------
    // ----------- TODO
    // automatically approve projects with no ML posts
    if($now > $last->modify('+'.$project->automaticApprovalDuration().' days')) {
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

        if(!$project->hasExtension()) {
            $message .= "We know life and other commitments can get in the way of hackspace projects. To help you finish up and organise your belongings you can extend your deadline once for ".$project->getExtensionDuration()." days. Simply click the link below:<br/><br/>".
                "[link]<br/><br/>";
        }

        $message .= "If you've already removed your project from the space, you can disable these alerts by marking your project as 'Removed'. Simply click the link below:" .
            "[link]<br/><br/>".
            "If you need more time please submit a new storage request via our website:<br/><br/>".
            "https://london.hackspace.org.uk/storage/<br/><br/>".
            "Thanks,<br/><br/>".
            "The London Hackspace trustees<br/>".
            "trustees@london.hackspace.org.uk";

        $subject = 'London Hackspace Storage Request #'.$project->getId().': '.$project->getName();
        $headers = 'From: trustees@london.hackspace.org.uk' . "\r\n" .
            'Reply-To: trustees@london.hackspace.org.uk' . "\r\n" .
            'Content-Type:text/html;charset=utf-8' . "\r\n" .
            'X-Mailer: PHP/' . phpversion();

        mail($user->getEmail(), $subject, $message, $headers);
    }

    // a day after removal update the status to 'Passed Deadline'
    if($now == $to->modify('+1 day')) {
        $project->setState('Passed Deadline');
        $project->store();

        // log the update
        $logmsg = 'Status automatically changed to '.$project->getState();
        $project->submitLog($logmsg);
        $project->submitMailingList($logmsg);
    }

    // reminder email every 7 days after removal
    if($now > $to && $now->format('w') == $to->format('w')) {
        // log the update
        $logmsg = 'Reminder sent to owner regarding passed deadline';
        $project->submitLog($logmsg);
        $project->submitMailingList($logmsg);
    }
}
?>