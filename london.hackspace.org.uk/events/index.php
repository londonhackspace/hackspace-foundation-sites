<?
$page = 'events';
$title = 'Events';
require '../header.php';

// choose which month to query, defaults to this month
$month = (int) date('n');
$year = (int) date('Y');
$day = (int) date('j');
if (isset($_GET['month'])) {
    $display = filter_var($_GET['month'], FILTER_SANITIZE_NUMBER_INT);
    $month = (int) substr($display, 4, 2);
    $year = (int) substr($display, 0, 4);
    $day = 1;
}
$startOfTheMonth = mktime(0, 0, 0, $month, 1, $year);
$timestamp = mktime(0, 0, 0, $month, $day, $year);
?>

<h2>Events</h2>
<p>You can <a href="http://www.google.com/calendar/ical/gc1bopmh3c5n0ogvlo6ceujlkc%40group.calendar.google.com/public/basic.ics">add the iCal feed to your calendar</a>.</p>
<p><a href="https://wiki.london.hackspace.org.uk/view/Guides/Planning_an_event">Guide for submitting new events.</a></p>
<div class="row">
<div class="col-md-8 calendar-alldays-container">

<?
// init the calendar and retrieve the events
$calendar = new Calendar();
$eventsList = $calendar->getEvents($startOfTheMonth);

// render the events
$lastDate = null;
$hasEvents = array();
while (true) {
    foreach ($eventsList->getItems() as $event) {
        $dateStart = new DateTime($event->getStart()->getDateTime());
        $dateEnd = new DateTime($event->getEnd()->getDateTime());

        if ($event->getRecurringEventId()) {
            $repeatTag = $calendar->getRepeatRule($event->getRecurringEventId());
            $repeatStyle = ($repeatTag == 'monthly') ? 'warning' : 'info';
        }

        $match = '/\b(?:(?:https?|ftp|file):\/\/|www\.|ftp\.)[-A-Z0-9+&@#\/%=~_|$\(\)?!:,.]*[A-Z0-9+&@#\/%=~_|$]/i';
        preg_match_all($match, $event->getDescription(), $url, PREG_PATTERN_ORDER);
        $desc = preg_replace($match, '', $event->getDescription());

        if ($lastDate != null && $lastDate != $dateStart->format('M-d')) {
            ?>
  	</div>
	<?}
        if ($lastDate == null || $lastDate != $dateStart->format('M-d')) {
            $defaultShow = '';
            $isSelected = '';
            if ((int) $dateStart->format('dmY') < (int) date('dmY', $timestamp)) {
                $defaultShow = ' hidden';
            }
            if ((int) $dateStart->format('dmY') == (int) date('dmY', $timestamp)) {
                $isSelected = ' selected';
            }
            ?>
  	<div class="calendar-day-container<?php echo $defaultShow ?><?php echo $isSelected ?>" data-date="<?php echo $dateStart->format('Ymd') ?>">
	  	<div class="calendar-title">
			<div class="calendar-date">
				<div class="calendar-month"><?php echo $dateStart->format('M') ?></div>
				<div class="calendar-day"><?php echo $dateStart->format('d') ?></div>
			</div>
	  		<div class="calendar-einfo">
	  			<h3><?php echo $dateStart->format('l') ?>
	  				<?if ($dateStart->format('d-m-Y') == date('d-m-Y')) {?>
	  					<span class="label label-plain">Today</span>
	  				<?}?>
	  			</h3>
			</div>
		</div>
	<?}?>
	  	<div class="calendar-event">
			<div class="calendar-time">
		  		<small><?php echo $dateStart->format('g:ia') ?></small>
			</div>
	  		<div class="calendar-einfo">
		  		<h4>
		  		<?if ($event->getRecurringEventId()) {?>
		  		<span class="label label-<?php echo $repeatStyle ?>"><?php echo $repeatTag ?></span>
		  		<?}
        if (isset($url[0]) && isset($url[0][0])) {?>
		  			<a target="_blank" href="<?php echo $url[0][0] ?>"><?php echo strip_tags($event->getSummary()) ?></a>
		  		<?} else {?>
		  			<?php echo strip_tags($event->getSummary()) ?>
		  		<?}?>
		  		<br/>
		  		<small><?php echo strip_tags($event->getLocation()) ?></small>
		  		</h4>
		  		<p><?php echo nl2br(strip_tags(trim($desc))) ?></p>
	  		</div>
	  	</div>
    <?
        if (!isset($hasEvents[$dateStart->format('Ymd')])) {
            $hasEvents[$dateStart->format('Ymd')] = 1;
        } else if ($hasEvents[$dateStart->format('Ymd')] < 4) {
            $hasEvents[$dateStart->format('Ymd')]++;
        }

        $lastDate = $dateStart->format('M-d');
    }
    $pageToken = $eventsList->getNextPageToken();
    if ($pageToken) {
        $param = array('pageToken' => $pageToken);
        $eventsList = $service->events->listEvents($cid, array_merge($optParams, $param));
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
		<a class="month-next" title="<?php echo date("F Y", strtotime("+1 month", $startOfTheMonth)) ?>" href="?month=<?php echo date("Ym", strtotime("+1 month", $startOfTheMonth)) ?>"><span class="glyphicon glyphicon-chevron-right"></span></a>
		<a class="month-prev" title="<?php echo date("F Y", strtotime("-1 month", $startOfTheMonth)) ?>" href="?month=<?php echo date("Ym", strtotime("-1 month", $startOfTheMonth)) ?>"><span class="glyphicon glyphicon-chevron-left"></span></a>
		<button class="day-today" type="button">Today</button>
		<h6><?php echo date('F Y', $startOfTheMonth) ?></h6>
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
				<?php echo $calendar->displayMonth($timestamp, $hasEvents); ?>
			</tr>
		</tbody>
	</table>
</div>

</div>
</div>

<?require '../footer.php';?>
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
