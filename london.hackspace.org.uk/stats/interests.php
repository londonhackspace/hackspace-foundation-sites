<? 
$page = 'retention';
require( '../header.php' );

if (!isset($user)) {
    fURL::redirect('/login.php?forward=/stats/interests.php');
}
if (!$user->isMember()) {
    fURL::redirect('/members/index.php');
}
?>
<div class="stats-page">
<h2>Member Interests</h2>
<?

$graph = Array();

$totalQuery = $db->translatedQuery( "SELECT COUNT(user_id) FROM users, users_profiles p WHERE users.id = p.user_id AND disabled_profile = 0 AND subscribed = 1");
$total = $totalQuery->fetchScalar();
$query = $db->translatedQuery( "SELECT COUNT(user_id) AS total, name, category FROM users_interests, interests WHERE users_interests.interest_id = interests.interest_id GROUP BY name HAVING total > 2 ORDER BY total DESC;");
$cats = $db->translatedQuery( "SELECT COUNT(DISTINCT user_id) AS total, category FROM users_interests, interests WHERE users_interests.interest_id = interests.interest_id GROUP BY category HAVING total > 2 ORDER BY total DESC;" );
?>
<p>Out of a total of <?=$total?> completed profiles.</p>
<div id="chartCats_div" style="width: 1000px; height: 200px;"></div>
<br/>

<table class="calc-numbers">
<thead>
<tr>
	<th>Count Members</th>
	<th>Category</th>
</tr>
</thead>
<tbody>
<?
foreach( $cats as $interest ) {
	echo '<tr>';
	foreach($interest as $td) {
		if(is_numeric($td))
			echo "<td class=\"number\">$td</td>";
		else
			echo "<td>$td</td>";
	}
	echo '</tr>';
}
?>
</tbody>
</table>
<br/>

<div id="chart_div" style="width: 1000px; height: 700px;"></div>

<table class="calc-numbers">
<thead>
<tr>
	<th>Count Members</th>
	<th>Interest</th>
	<th>Category</th>
</tr>
</thead>
<tbody>
<?
foreach( $query as $interest ) {
	echo '<tr>';
	foreach($interest as $td) {
		if(is_numeric($td))
			echo "<td class=\"number\">$td</td>";
		else
			echo "<td>$td</td>";
	}
	echo '</tr>';
}
?>
</tbody>
</table>

<? require('../footer.php'); ?>
    <script type="text/javascript" src="https://www.google.com/jsapi"></script>
    <script type="text/javascript">
      google.load("visualization", "1", {packages:["corechart"]});
      google.setOnLoadCallback(drawChart);
      function drawChart() {
        var data = google.visualization.arrayToDataTable([
          ['Interests', 'Members'],
<?
foreach( $query as $interest ) {
	echo "\t[";
	echo '\''.$interest['name'].'\','.((int)$interest['total']/(int)$total*100);
	echo "],\n";
}
?>
        ]);

        var options = {
        	title: 'Profiles by Interest',
        	legend: {position: 'none'},
        	viewWindowMode: 'explicit',
        	viewWindow: {
        		max: 100,
        		min: 0
		    },
		    hAxis: {
		    	title: "Percentage of completed profiles",
		    	format: '#\'%\'',
		    	maxValue: 100,
		    	minValue: 0
		    },
	        gridlines: {
	          count: 20,
	        }
        };

        var chart = new google.visualization.BarChart(document.getElementById('chart_div'));
        chart.draw(data, options);

        var data = google.visualization.arrayToDataTable([
          ['Categories', 'Members'],
<?
foreach( $cats as $interest ) {
	echo "\t[";
	echo '\''.$interest['category'].'\','.((int)$interest['total']/(int)$total*100);
	echo "],\n";
}
?>
        ]);

        options.title = 'Profiles by Interest Category';

        var chartCats = new google.visualization.BarChart(document.getElementById('chartCats_div'));
        chartCats.draw(data, options);
      }
    </script>
</body>
</html>