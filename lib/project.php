<?php
class Project extends fActiveRecord { 
	public function getState() {
		$state = new ProjectState($this->getStateId());
		return $state->getName();
	}

	public function setState($name) {
		$states = fRecordSet::build('ProjectState',array('name=' => $name), array('id' => 'asc'));
		if(count($states)) {
			$this->setStateId($states[0]->getId());
			return true;
		}
		return false;
	}

	public function outputDuration() {
		$from = new DateTime($this->getFromDate());
		$to = new DateTime($this->getToDate()); 

		$output = 'Stored for ';
		$days = (int)$from->diff($to)->format('%d');
		$months = (int)$from->diff($to)->format('%m');

		$daysString = $days.' day';
		if($days > 1) $daysString .= 's';

		$monthsString = $months.' month';
		if($months > 1) $monthsString .= 's';

		if($days > 0 && $months > 0)
			return 'Request for '.$monthsString.', '.$daysString;
		else if($days > 0)
			return 'Request for '.$daysString;
		else if($months > 0)
			return 'Request for '.$monthsString;
	}

	public function outputDates() {
		$from = new DateTime($this->getFromDate());
		$to = new DateTime($this->getToDate()); 
		return $from->format('jS M Y').' - '.$to->format('jS M Y');
	}

	public function outputLocation() {
		$location = new Location($this->getLocationId());
		$output = 'in the '.strtolower($location->getName());
		$output .= ' ('.$this->getLocation().')';
		return $output;
	}

	public function submitLog($logmsg,$id) {
		$log = new ProjectsLog();
		$log->setProjectId($this->getId());
		$log->setTimestamp(time());
		$log->setDetails($logmsg);
		if($id) $log->setUserId($id);
		$log->store();
	}

	public function submitMailingList($message) {
		$toEmail = 'london-hack-space-test@googlegroups.com';
		$subject = 'Storage Request #'.$this->getId().': '.$this->getName();
		$headers = 'From: no-reply@london.hackspace.org.uk' . "\r\n" .
			'Reply-To: no-reply@london.hackspace.org.uk' . "\r\n" .
			'Content-Type:text/html;charset=utf-8' . "\r\n" .
			'X-Mailer: PHP/' . phpversion();

		mail($toEmail, $subject, $message, $headers);

		// log the post
		$this->submitLog('Posted to the Mailing List',false);
	}

	public function canTransitionStates($from,$to) {
		if(
		   ($to == 'Pending Approval') ||
		   ($from == 'Pending Approval' 	&& ($to == 'Approved' || $to == 'Unapproved' || $to == 'Archived')) ||
		   ($from == 'Unapproved' 			&& ($to == 'Approved' || $to == 'Archived')) ||
		   ($from == 'Approved' 			&& ($to == 'Removed' || $to == 'Unapproved' || $to == 'Extended' || $to == 'Passed Deadline')) ||
		   ($from == 'Passed Deadline' 		&& ($to == 'Removed' || $to == 'Extended')) ||
		   ($from == 'Extended' 			&& ($to == 'Removed' || $to == 'Passed Deadline'))
		)
		   return true;

		return false;
	}

	public function noActivity() {
		$projectslogs = fRecordSet::build('ProjectsLog',array('project_id=' => $this->getId()), array('timestamp' => 'asc'));
		if($this->isShortTerm() && count($projectslogs) == 4) {
			return true;
		} else if(!$this->isShortTerm() && count($projectslogs) == 2) {
			return true;
		}
		return false;
	}

	public function recentPost() {
		$projectslogs = fRecordSet::build('ProjectsLog',array('project_id=' => $this->getId()), array('timestamp' => 'asc'));
		$rlogs = 0;
		$clogs = 0;

		foreach($projectslogs as $log) {
			$clogs++;
			$ltime = new DateTime(date('Y-m-d',$log->getTimestamp()));
			$now = new DateTime(date('Y-m-d'));
			if($ltime >= $now->modify('-1 day'))
				$rlogs++;
		}
		if($this->isShortTerm() && $clogs == 4 && $rlogs == 4) {
			return true;
		} else if(!$this->isShortTerm() && $clogs == 2 && $rlogs == 2) {
			return true;
		}
		return false;
	}

	public function automaticApprovalDuration() {
		return ($this->getLocationId() == 'Yard') ? 7 : 2;
	}

	public function hasExtension() {
		// Has this project been extended
		if($this->getState() == 'Extended')
			return true;

		$logs = fRecordSet::build('ProjectsLog',array('project_id=' => $this->getId(), 'details=' => 'Status changed to Extended'));
		if(count($logs) > 0)
			return true;
		return false;
	}

	public function isShortTerm() {
		$from = new DateTime($this->getFromDate());
		$to = new DateTime($this->getToDate());
		$logs = fRecordSet::build('ProjectsLog',array('project_id=' => $this->getId()));
		if(count($logs) > 0)
			$then = new DateTime(date('Y-m-d',$logs[0]->getTimestamp()));
		else
			return false;

		// Short term projects only get a 2 day extension
		if($from <= $then->modify('+1 day') && $to <= $from->modify('+3 days'))
			return true;
		return false;
	}

	public function getExtensionDuration() {
		if($this->isShortTerm())
			return 2;
		return 14;
	}

	public function getMailingListURL() {
		return 'https://groups.google.com/forum/#!topicsearchin/london-hack-space-test/subject$3A%22Storage$20Request$20$23'.$this->getId().'$3A%22';
	}
}
class Location extends fActiveRecord { }
class ProjectState extends fActiveRecord { }
class ProjectsLog extends fActiveRecord { }
