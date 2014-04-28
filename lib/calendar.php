<?

set_include_path(get_include_path() . PATH_SEPARATOR . dirname(__FILE__) . '/GoogleAPI/src');
require_once "Google/Client.php";
require_once "Google/Service/Calendar.php";
require_once "Google/Auth/AssertionCredentials.php";

class Calendar {

	private $service_account_name = null;
	private $key_file_location = null;
	private $google_cache_file_directory = null;
	private $recurringTags = array();

	public function __construct() {
		$this->set();
		$this->config();
		$this->auth();
		$this->getCalendar();
	}

	/*
	 * define the auth and config vars
	 */
	private function set() {
		$this->service_account_name = '428545140576-m9u2qace910m7r1cdqh8nu31bju0r14u@developer.gserviceaccount.com';
		$this->key_file_location = '../../var/876cb3fbc7ebd130f2eb7528ad3d46c066d46f23-privatekey.p12';
		$this->google_cache_file_directory = $_SERVER['DOCUMENT_ROOT'] . '/../var/Google_Client';
		$this->cache_timeout = 10800; // 3hrs in seconds
	}

	/*
	 * Setup Google API configuration
	 */
	private function config() {
		$this->config = new Google_Config();
		$this->config->setClassConfig('Google_Cache_File', array('directory' => $this->google_cache_file_directory));
		$this->config->setCacheClass('Google_Cache_File');

		$this->client = new Google_Client($this->config);
		$this->client->setApplicationName("London Hackspace Events");
		$this->client->setApprovalPrompt('force');
		$this->client->setAccessType('offline');

		$this->cache = new Google_Cache_File($this->client);
	}

	/*
	 * Authenticate to Google API
	 */
	private function auth() {
		if (isset($_SESSION['service_token'])) {
			$this->client->setAccessToken($_SESSION['service_token']);
		}

		$key = file_get_contents($this->key_file_location);
		$cred = new Google_Auth_AssertionCredentials(
		    $this->service_account_name,
		    array('https://www.googleapis.com/auth/calendar'),
		    $key
		);
		$this->client->setAssertionCredentials($cred);

		if($this->client->getAuth()->isAccessTokenExpired()) {
			$this->client->getAuth()->refreshTokenWithAssertion($cred);
			$_SESSION['service_token'] = $this->client->getAccessToken();
		}
	}

	/*
	 * Get the first calendar we have access to
	 */
	private function getCalendar() {
		$this->service = new Google_Service_Calendar($this->client);
		$this->calendarList = $this->service->calendarList->listCalendarList();

		$this->cid = null;
		foreach ($this->calendarList->getItems() as $calendarListEntry) {
			$this->cid = $calendarListEntry->getId();
			break;
		}		
	}

	/*
	 * Get a list of events for a given date
	 */
	public function getEvents($timestamp) {

		// begin calendar query
		$optParams = array(
			'orderBy'=>'startTime',
			'singleEvents'=>true,
			'maxResults'=>100,
			'timeMin'=>date('Y-m',$timestamp).'-01T00:00:00-01:00', 
			'timeMax'=>date('Y-m',$timestamp).'-'.date('t',$timestamp).'T23:59:59-01:00'
		);

		// Cache this query
		$cacheName = 'events_cache-'.date('Y-m',$timestamp);
		if($this->cache->get($cacheName, $this->cache_timeout)) {
			// If cache is there & younger than 12 hours then load cached data
		    $eventsList = $this->cache->get($cacheName);
		}
		else {
		    //If it is not then make Calendar API request to Google and get the results
			$eventsList = $this->service->events->listEvents($this->cid, $optParams);
		    $this->cache->set($cacheName, $eventsList); // Save results into cache
		}

		return $eventsList;	
	}

	public function getRepeatRule($eid) {
		if(!isset($this->recurringTags[$eid])) {
			$cacheName = 'repeater_cache-'.$eid;
			if($this->cache->get($cacheName, $this->cache_timeout)) {
				// If cache is there & younger than 12 hours then load cached data
			    $repeater = $this->cache->get($cacheName);
			}
			else {
			    //If it is not then make Calendar API request to Google and get the results
				$repeater = $this->service->events->get($this->cid, $eid);
			    $this->cache->set($cacheName, $repeater); // Save results into cache
			}
			$recurrence = $repeater->getRecurrence();
			$this->recurringTags[$eid] = $recurrence[0];
	  	}

	  	$repeats = '';
	  	if((strpos($this->recurringTags[$eid],'FREQ=WEEKLY') != false) && (strpos($this->recurringTags[$eid],'INTERVAL=2') != false)) {
	  		$repeats = 'bi-weekly';
	  	}
	  	elseif((strpos($this->recurringTags[$eid],'FREQ=WEEKLY') != false)) {
	  		$repeats = 'weekly';
	  	}
	  	elseif((strpos($this->recurringTags[$eid],'FREQ=MONTHLY') != false)) {
	  		$repeats = 'monthly';
	  	}
	  	return $repeats;
	}

	public function displayMonth($timestamp,$hasEvents) {
		// days and weeks
		$day = date('j',$timestamp);
		$month = date('n',$timestamp);
		$monthNum = date('m',$timestamp);
		$year = date('Y',$timestamp);
		$running_day = date('w',mktime(0,0,0,$month,1,$year));
		$days_in_month = date('t',mktime(0,0,0,$month,1,$year));
		$days_in_this_week = 1;
		$day_counter = 0;
		$dates_array = array();

		// row for week one
		$calendar = '';

		// print "blank" days until the first of the current week
		$fillNumber = $running_day;
		if($running_day == 0)
			$fillNumber = 7;

		for($x = 1; $x < $fillNumber; $x++) {
			$calendar .= '<td class="blank"> </td>';
			$days_in_this_week++;
		}

		// add in the day numbers
		for($list_day = 1; $list_day <= $days_in_month; $list_day++) {
			$style = ''; $events = '';
			$dataDate = $year.$monthNum.str_pad($list_day, 2, "0", STR_PAD_LEFT);

			if($list_day == (int)date('j',mktime(0,0,0,$month,$day,$year))) {
				$isToday = '';
				if(mktime(0,0,0,$month,$day,$year) == mktime(0,0,0,date('n'),date('j'),date('Y'))) {
					$isToday = ' today';
				}

				$style = ' class="selected'.$isToday.'"';
			}
			if(isset($hasEvents[$dataDate]))
				$events = ' class="has-events'.$hasEvents[$dataDate].'"';

			$calendar .= '<td'.$style.' data-date="'.$dataDate.'"><div'.$events.'>'.$list_day.'</div></td>';
				
			if($days_in_this_week == 7) {
				$calendar .= '</tr>';
				if(($day_counter+1) != $days_in_month) {
					$calendar .= '<tr>';
				}
				$running_day = 0;
				$days_in_this_week = 0;
			}
			$days_in_this_week++; $running_day++; $day_counter++;
		}

		// finish the rest of the days in the week 
		if($days_in_this_week < 8) {
			for($x = 1; $x <= (8 - $days_in_this_week); $x++) {
				$calendar.= '<td class="blank"> </td>';
			}
		}

		return $calendar;
	}
}