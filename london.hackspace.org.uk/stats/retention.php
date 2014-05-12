<? 
$page = 'retention';
require( '../header.php' );

if (!isset($user)) {
    fURL::redirect('/login.php?forward=/stats/retention.php');
}
if (!$user->isMember()) {
    fURL::redirect('/members/index.php');
}
?>
<div class="stats-page">
<h2>Member Retention</h2>
<?
$length = 3;

// get last quarter
$month = floor(date('n')/3)*3;
$year = date('Y');
if($month == 0) {
	$month = 12;
	$year--;
}

// get previous quarter
$pastMonth = $month-$length;
$pastYear = $year;
if($pastMonth == 0) {
	$pastMonth = 12;
	$pastYear--;
}

$graph = Array();

// iterate for enough quarters to show back to q1 2011
$gofor = (((int)date('Y')-2011)*4) + floor(date('n')/3);
for($x=0;$x<$gofor;$x++) {
	$period_start = $year.'-'.str_pad($month-($length-1), 2, '0', STR_PAD_LEFT);
	$period_end = $year.'-'.str_pad($month, 2, '0', STR_PAD_LEFT);
	$prev_start = $pastYear.'-'.str_pad($pastMonth-($length-1), 2, '0', STR_PAD_LEFT);
	$prev_end = $pastYear.'-'.str_pad($pastMonth, 2, '0', STR_PAD_LEFT);

	// get the number of members for last period
	$csQuery = $db->translatedQuery( "SELECT COUNT(DISTINCT user_id) FROM transactions WHERE timestamp >= '$prev_start' AND timestamp <= '$prev_end'" );
	$cs = (int)$csQuery->fetchScalar();

	// get the number of members for this period
	$ceQuery = $db->translatedQuery( "SELECT COUNT(DISTINCT user_id) FROM transactions WHERE timestamp >= '$period_start' AND timestamp <= '$period_end'" );
	$ce = (int)$ceQuery->fetchScalar();

	// get the number of members who's first payment was this period
	$cnQuery = $db->translatedQuery( "SELECT DISTINCT user_id, (SELECT timestamp FROM transactions AS t2 WHERE t1.user_id = t2.user_id ORDER BY t2.timestamp ASC LIMIT 1) AS first FROM transactions AS t1 WHERE first >= '$period_start' AND first <= '$period_end' ORDER BY user_id DESC;" );
	$cn = $cnQuery->countReturnedRows();

	// get the number of members who's first payment was before last period, they didn't pay last period but are paying this period
	$crQuery = $db->translatedQuery( "SELECT user_id, (SELECT min(timestamp) FROM transactions AS t2 WHERE t1.user_id = t2.user_id) AS start 
			FROM transactions AS t1 
			WHERE timestamp >= '$period_start' AND timestamp <= '$period_end' AND start < '$period_start' AND user_id NOT IN (SELECT user_id FROM transactions AS t4 WHERE timestamp >= '$prev_start' AND timestamp <= '$prev_end') 
			GROUP BY user_id ORDER BY user_id" );
	$cr = $crQuery->countReturnedRows();

	// get the number of members who paid last period but haven't paid this period
	$cdQuery = $db->translatedQuery( "SELECT user_id FROM transactions WHERE timestamp >= '$prev_start' AND timestamp <= '$prev_end' AND user_id NOT IN (SELECT user_id FROM transactions WHERE timestamp >= '$period_start' AND timestamp <= '$period_end') GROUP BY user_id ORDER BY user_id" );
	$cd = $cdQuery->countReturnedRows();

	$graph[$period_start] = Array(
		'period'=>$period_start.' to '.$period_end,
		'cs'=>$cs,
		'ce'=>$ce,
		'cn'=>$cn,
		'cr'=>$cr,
		'cd'=>$cd,
		'retention'=>round(((($ce-$cn-$cr)/$cs) * 100),2), 
		'acquisition'=>round((($cn/$cs) * 100),2), 
		'return'=>round((($cr/$cs) * 100),2), 
		'attrition'=>round((($cd/$cs) * 100),2)
	);

	// cross checking
	if((($cn + $cr) - $cd) != ($ce - $cs)) {
		echo "<div class=\"alert alert-warning\">Numbers don't add up for period $period</div>";
	}

	$month = $month-$length;
	$pastMonth = $pastMonth-$length;
	if($month == 0) {
		$month = 12;
		$year--;

		$pastMonth = 12-$length;
	} 
	if ($month == $length) {
		$pastMonth = 12;
		$pastYear--;
	}
}
?>
<div id="chart_div" style="width: 1000px; height: 500px;"></div>

<table class="calc-numbers">
<thead>
<tr>
	<th>Period</th>
	<th>CS</th>
	<th>CE</th>
	<th>CN</th>
	<th>CR</th>
	<th>CD</th>
	<th>Retention %</th>
	<th>Acquisition %</th>
	<th>Return %</th>
	<th>Attrition/Churn %</th>
</tr>
</thead>
<tbody>
<?
foreach($graph as $period) {
	echo '<tr>';
	foreach($period as $td) {
		if(is_int($td))
			echo "<td class=\"number\">$td</td>";
		else if(is_float($td))
			echo '<td class="number">'.number_format((float)$td, 2, '.', '').'</td>';
		else
			echo "<th>$td</th>";
	}
	echo '</tr>';
}
?>
</tbody>
</table>

<h3>Legend</h3>
<p><strong>CS =</strong> number of members at the end of last period</p>
<p><strong>CE =</strong> number of members at the end of this period</p>
<p><strong>CN =</strong> number of new members acquired during this period</p>
<p><strong>CR =</strong> number of previous members returned during this period</p>
<p><strong>CD =</strong> number of old members departed during this period</p>

<h3>Equations</h3>
<p><a href="http://www.inc.com/jeff-haden/best-way-to-calculate-customer-retention-rate.html">Reference</a></p>
<p>Retention percentage formula</p>
<pre>((CE-CN-CR)/CS) X 100</pre>
<p>Acquisition percentage formula</p>
<pre>(CN/CS) X 100</pre>
<p>Return percentage formula</p>
<pre>(CR/CS) X 100</pre>
<p>Attrition percentage formula</p>
<pre>(CD/CS) X 100</pre>
</div>

<? require('../footer.php'); ?>
    <script type="text/javascript" src="https://www.google.com/jsapi"></script>
    <script type="text/javascript">
      google.load("visualization", "1", {packages:["corechart"]});
      google.setOnLoadCallback(drawChart);
      function drawChart() {
        var data = google.visualization.arrayToDataTable([
          ['Period', 'Retention', 'Acquisition', 'Return', 'Attrition/Churn'],
<?
$greverse = array_reverse($graph);
foreach($greverse as $period) {
	echo "\t[";
	foreach($period as $td) {
		if(is_int($td)) {}
		else if(is_float($td))
			echo number_format((float)$td/100, 2, '.', '').',';
		else {
			$y = (int)substr($td,0,4);
			$m = (int)substr($td,5,2);
			echo "new Date($y,$m,0,0,0),";
		}
	}
	echo "],\n";
}
?>
        ]);

        var options = {
          colors:['#3366CC','#109618', '#FF9900', '#DC3912'],
          vAxis: { minValue: 0, maxValue: 1, format:'#%' },
          hAxis: { 
          	format: "MM/yy", 
          	gridlines: {color: 'white'},
          	ticks: [
<?
foreach($greverse as $period) {
	$y = (int)substr($period['period'],0,4);
	$m = (int)substr($period['period'],5,2);
	echo "\tnew Date($y,$m,0,0,0),\n";
}
?>
          	]
      	  },
      	  /*
          trendlines:{ 
          	0: { labelInLegend: 'Trend line', visibleInLegend: true },
          	1: { labelInLegend: 'Trend line', visibleInLegend: true },
          	2: { labelInLegend: 'Trend line', visibleInLegend: true },
          	3: { labelInLegend: 'Trend line', visibleInLegend: true },
          }
          */
        };

        var chart = new google.visualization.LineChart(document.getElementById('chart_div'));
        chart.draw(data, options);
      }
    </script>
</body>
</html>