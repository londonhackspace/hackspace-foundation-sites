<? 
$page = 'events';
$title = 'Events';
require('../header.php');

set_include_path(get_include_path() . PATH_SEPARATOR . '../../lib/GoogleAPI/src');
require_once "Google/Client.php";
require_once "Google/Service/Calendar.php";
require_once "Google/Auth/AssertionCredentials.php";

/*
 * Authenticate to Google Developer API
 */
$config = new Google_Config();
$config->setClassConfig('Google_Cache_File', array('directory' => $_SERVER['DOCUMENT_ROOT'] . '/../var/Google_Client'));
$config->setCacheClass('Google_Cache_File');

$client = new Google_Client($config);
$client->setApplicationName("London Hackspace Events");
$client->setApprovalPrompt('force');
$client->setAccessType('offline');

if (isset($_SESSION['service_token'])) {
	$client->setAccessToken($_SESSION['service_token']);
}

$service_account_name = '428545140576-m9u2qace910m7r1cdqh8nu31bju0r14u@developer.gserviceaccount.com';
$key_file_location = '../../var/876cb3fbc7ebd130f2eb7528ad3d46c066d46f23-privatekey.p12';

$key = file_get_contents($key_file_location);
$cred = new Google_Auth_AssertionCredentials(
    $service_account_name,
    array('https://www.googleapis.com/auth/calendar'),
    $key
);
$client->setAssertionCredentials($cred);

if($client->getAuth()->isAccessTokenExpired()) {
	$client->getAuth()->refreshTokenWithAssertion($cred);
	$_SESSION['service_token'] = $client->getAccessToken();
}
?>

<h2>Events</h2>
<p>You can <a href="http://www.google.com/calendar/ical/gc1bopmh3c5n0ogvlo6ceujlkc%40group.calendar.google.com/public/basic.ics">add the iCal feed to your calendar</a>.</p>

<div class="row">
<div class="col-md-8 calendar-alldays-container">

<?
/*
 * Authenticated, now access Calendar API
 */

$service = new Google_Service_Calendar($client);
$calendarList = $service->calendarList->listCalendarList();

$cid = null;
foreach ($calendarList->getItems() as $calendarListEntry) {
	$cid = $calendarListEntry->getId();
	break;
}

// choose which month to query, defaults to this month
$thisDisplayMonth = (int)date('n');
$thisDisplayYear = (int)date('Y');
$thisDisplayDay = (int)date('j');
if(isset($_GET['month'])) {
	$display = filter_var($_GET['month'], FILTER_SANITIZE_NUMBER_INT);
	$thisDisplayMonth = (int)substr($display,4,2);
	$thisDisplayYear = (int)substr($display,0,4);
	$thisDisplayDay = 1;
}
$thisDisplayTimestamp = mktime(0,0,0,$thisDisplayMonth,1,$thisDisplayYear);

// begin calendar query
$optParams = array(
	'orderBy'=>'startTime',
	'singleEvents'=>true,
	'maxResults'=>100,
	'timeMin'=>date('Y-m',$thisDisplayTimestamp).'-01T00:00:00-01:00', 
	'timeMax'=>date('Y-m',$thisDisplayTimestamp).'-'.date('t',$thisDisplayTimestamp).'T23:59:59-01:00'
);

// Cache this query
$cache = new Google_Cache_File($client);
$cacheName = 'events_cache-'.date('Y-m',$thisDisplayTimestamp);
if($cache->get($cacheName)) { //,43200)) {
	// If cache is there & younger than 12 hours then load cached data
    $eventsList = $cache->get($cacheName);
}
else {
    //If it is not then make Calendar API request to Google and get the results
	$eventsList = $service->events->listEvents($cid, $optParams);
    $cache->set($cacheName, $eventsList); // Save results into cache
}


$lastDate = null;
$recurringTags = array();
$hasEvents = array();
while(true) {
  foreach ($eventsList->getItems() as $event) {
  	$dateStart = new DateTime($event->getStart()->getDateTime());
  	$dateEnd = new DateTime($event->getEnd()->getDateTime());
  	$match = '/\b(?:(?:https?|ftp|file):\/\/|www\.|ftp\.)[-A-Z0-9+&@#\/%=~_|$\(\)?!:,.]*[A-Z0-9+&@#\/%=~_|$]/i';
  	preg_match_all($match, $event->getDescription(), $url, PREG_PATTERN_ORDER);
  	$desc = preg_replace($match,'',$event->getDescription());

  	if($lastDate != null && $lastDate != $dateStart->format('M-d')) {
  	?>
  	</div>
	<? }
  	if($lastDate == null || $lastDate != $dateStart->format('M-d')) {
  		$defaultShow = '';
  		$isSelected = '';
  		if((int)$dateStart->format('dmY') < (int)date('dmY',mktime(0,0,0,$thisDisplayMonth,$thisDisplayDay,$thisDisplayYear))) {
  			$defaultShow = ' hidden';
  		}
  		if((int)$dateStart->format('dmY') == (int)date('dmY',mktime(0,0,0,$thisDisplayMonth,$thisDisplayDay,$thisDisplayYear))) {
  			$isSelected = ' selected';
  		}
  	?>
  	<div class="calendar-day-container<?=$defaultShow?><?=$isSelected?>" data-date="<?=$dateStart->format('Ymd')?>">
	  	<div class="calendar-title">
			<div class="calendar-date">
				<div class="calendar-month"><?=$dateStart->format('M')?></div>
				<div class="calendar-day"><?=$dateStart->format('d')?></div>
			</div>
	  		<div class="calendar-einfo">
	  			<h3><?=$dateStart->format('l')?>
	  				<? if($dateStart->format('d-m-Y') == date('d-m-Y')) { ?>
	  					<span class="label label-plain">Today</span>
	  				<? } ?>
	  			</h3>
			</div>
		</div>
	<? } ?>
	  	<div class="calendar-event">
			<div class="calendar-time">
		  		<small><?=$dateStart->format('g:ia')?></small>
			</div>
	  		<div class="calendar-einfo">
		  		<h4>
		  		<? if($event->getRecurringEventId()) { 
		  			if(!isset($recurringTags[$event->getRecurringEventId()])) {
						$cacheName = 'ecache-'.$event->getRecurringEventId();
						if($cache->get($cacheName)) { //,43200)) {
							// If cache is there & younger than 12 hours then load cached data
						    $repeater = $cache->get($cacheName);
						}
						else {
						    //If it is not then make Calendar API request to Google and get the results
							$repeater = $service->events->get($cid, $event->getRecurringEventId());
						    $cache->set($cacheName, $repeater); // Save results into cache
						}
						$recurringTags[$event->getRecurringEventId()] = $repeater->getRecurrence();
				  	}

				  	$repTag = $recurringTags[$event->getRecurringEventId()][0];
				  	$repeats = '';
				  	$repStyle = 'info';
				  	if((strpos($repTag,'FREQ=WEEKLY') != false) && (strpos($repTag,'INTERVAL=2') != false)) {
				  		$repeats = 'bi-weekly';
				  	}
				  	elseif((strpos($repTag,'FREQ=WEEKLY') != false)) {
				  		$repeats = 'weekly';
				  	}
				  	elseif((strpos($repTag,'FREQ=MONTHLY') != false)) {
				  		$repeats = 'monthly';
				  		$repStyle = 'warning';
				  	}
		  		?>
		  		<span class="label label-<?=$repStyle?>"><?=$repeats?></span>
		  		<? } 
		  		if(isset($url[0]) && isset($url[0][0])) { ?>
		  			<a target="_blank" href="<?=$url[0][0]?>"><?=$event->getSummary()?></a>
		  		<? } else { ?> 
		  			<?=$event->getSummary()?>
		  		<? } ?>
		  		<br/>
		  		<small><?=$event->getLocation()?></small>
		  		</h4>
		  		<p><?=$desc?></p>
	  		</div>
	  	</div>
    <?
	if(!isset($hasEvents[$dateStart->format('Ymd')]))
		$hasEvents[$dateStart->format('Ymd')] = 1;
	else if($hasEvents[$dateStart->format('Ymd')] < 4)
		$hasEvents[$dateStart->format('Ymd')]++;

    $lastDate = $dateStart->format('M-d');
  }
  $pageToken = $eventsList->getNextPageToken();
  if ($pageToken) {
    $param = array('pageToken' => $pageToken);
    $eventsList = $service->events->listEvents($cid, array_merge($optParams,$param));
  } else {
    break;
  }
}

?>
	</div>
</div>
<div class="col-md-4">
<div class="calendar-datepicker">
	<div class="header">
		<a class="month-next" title="<?=date("F Y", strtotime("+1 month",$thisDisplayTimestamp) )?>" href="?month=<?=date("Ym", strtotime("+1 month",$thisDisplayTimestamp) )?>"><span class="glyphicon glyphicon-chevron-right"></span></a>
		<a class="month-prev" title="<?=date("F Y", strtotime("-1 month",$thisDisplayTimestamp) )?>" href="?month=<?=date("Ym", strtotime("-1 month",$thisDisplayTimestamp) )?>"><span class="glyphicon glyphicon-chevron-left"></span></a>
		<button class="day-today" type="button">Today</button>
		<h6><?=date('F Y',$thisDisplayTimestamp)?></h6>
	</div>
	<table>
		<thead>
			<tr>
				<th title="Monday">Mo</th>
				<th title="Tuesday">Tu</th>
				<th title="Wednesday">We</th>
				<th title="Thursday">Th</th>
				<th title="Friday">Fr</th>
				<th title="Saturday">Sa</th>
				<th title="Sunday">Su</th>
			</tr>
		</thead>
		<tbody>
			<tr>
<?
	// days and weeks
	$month = date('n',$thisDisplayTimestamp);
	$monthNum = date('m',$thisDisplayTimestamp);
	$year = date('Y',$thisDisplayTimestamp);
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

		if($list_day == (int)date('j',mktime(0,0,0,$thisDisplayMonth,$thisDisplayDay,$thisDisplayYear))) {
			$isToday = '';
			if(mktime(0,0,0,$thisDisplayMonth,$thisDisplayDay,$thisDisplayYear) == mktime(0,0,0,date('n'),date('j'),date('Y'))) {
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
	echo $calendar;
?>
			</tr>
		</tbody>
	</table>
</div>

</div>
</div>

<? require('../footer.php'); ?>
<script type="text/javascript">
$(document).ready(function() { 
	$('.calendar-datepicker td[data-date]').bind('click touchend', function(e) {
		e.preventDefault();

		// select the calendar entry
		$('.calendar-datepicker td[data-date]').removeClass('selected');
		$(this).addClass('selected');

		// show the events
		var index = $('.calendar-day-container[data-date='+$(this).data('date')+']').index();
		if(index == -1) {
			var iterate = 0;
			while(index == -1) {
				if(parseInt($('.calendar-day-container:eq('+iterate+')').data('date')) > parseInt($(this).data('date'))) {
					index = $('.calendar-day-container:eq('+iterate+')').index();
				}
				iterate++;
			}
		}

		$('.calendar-day-container').removeClass('hidden').removeClass('selected');
		$('.calendar-day-container:lt('+index+')').addClass('hidden');
		$('.calendar-day-container:gt('+(index+5)+')').addClass('hidden');
		$('.calendar-day-container:eq('+index+')').addClass('selected');
	});
	$('.calendar-datepicker .day-today').bind('click touchend', function(e) {
		e.preventDefault();
		if($('.calendar-datepicker td.today').length > 0)
			$('.calendar-datepicker td.today').click();
		else {
			window.location.href = '/events/';
		}
	});
});
</script>
</body>
</html>
