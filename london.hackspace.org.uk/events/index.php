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

$apiConfig = array(
	// Definition of class specific values, like file paths and so on.
	'classes' => array(
		// If you want to pass in OAuth 2.0 settings, they will need to be
		// structured like this.
		'Google_Auth_OAuth2' => array(
			// Simple API access key, also from the API console. Ensure you get
			// a Server key, and not a Browser key.
			'developer_key' => 'AIzaSyBQqwZslHPDib9PNYPRn6-6viWG6DE0Dpw',
		)
	),

	// Definition of service specific values like scopes, oauth token URLs,
	// etc. Example:
	'services' => array(
		'calendar' => array('scope' => 'https://www.googleapis.com/auth/calendar'),
	),
);

$client = new Google_Client($apiConfig);
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
<p>You can add <a href="http://www.google.com/calendar/ical/gc1bopmh3c5n0ogvlo6ceujlkc%40group.calendar.google.com/public/basic.ics">add the iCal feed to your calendar</a>.</p>

<div class="row">
<div class="col-md-5">

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
?>
<h3>Coming up</h3>
<?
$optParams = array(
	'orderBy'=>'startTime',
	'singleEvents'=>true, 
	'maxResults'=>5,
	'timeMin'=>'2014-04-20T00:00:00-04:00', 
	'timeMax'=>'2014-04-27T23:59:59-04:00'
);
$eventsList = $service->events->listEvents($cid, $optParams);
while(true) {
  foreach ($eventsList->getItems() as $event) {
  	$dateStart = new DateTime($event->getStart()->getDateTime());
  	$dateEnd = new DateTime($event->getEnd()->getDateTime());
  	$match = '/\b(?:(?:https?|ftp|file):\/\/|www\.|ftp\.)[-A-Z0-9+&@#\/%=~_|$\(\)?!:,.]*[A-Z0-9+&@#\/%=~_|$]/i';
  	preg_match_all($match, $event->getDescription(), $url, PREG_PATTERN_ORDER);
  	$desc = preg_replace($match,'',$event->getDescription());
  	?>
  	<div class="calendar-event">
  		<div class="calendar-date">
  			<div class="calendar-month"><?=$dateStart->format('M')?></div>
  			<div class="calendar-day"><?=$dateStart->format('d')?></div>
  		</div>
  		<div class="calendar-einfo">
	  		<a target="_blank" href="<? if(isset($url[0]) && isset($url[0][0])) echo $url[0][0]; ?>"><strong><?=$event->getSummary()?></strong></a><br/>
	  		<small><?=$dateStart->format('g:ia')?>-<?=$dateEnd->format('g:ia')?></small><br/>
	  		<small><?=$event->getLocation()?></small><br/>
	  		<?=$desc?>
  		</div>
  	</div>
    <?
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
<div class="col-md-7">
<h3>At a glance</h3>

<iframe src="https://www.google.com/calendar/embed?showCalendars=0&amp;showTitle=0&amp;showNav=0&amp;showPrint=0&amp;showTabs=0&amp;height=600&amp;wkst=1&amp;bgcolor=%23FFFFFF&amp;src=gc1bopmh3c5n0ogvlo6ceujlkc%40group.calendar.google.com&amp;color=%235229A3&amp;ctz=Europe%2FLondon" style=" border-width:0 " width="576" height="400" frameborder="0" scrolling="no"></iframe>
</div>
</div>

<? require('../footer.php'); ?>
<script type="text/javascript">
/*
$.ajax({
  url: 'https://www.googleapis.com/calendar/v3/calendars/gc1bopmh3c5n0ogvlo6ceujlkc%40group.calendar.google.com',
  dataType: 'json',
  success: function(data, status) {
  	console.log(status);
  	console.log(data);
  }
});
*/

</script>
</body>
</html>
