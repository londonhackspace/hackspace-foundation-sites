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
$length = 1;

// get the last period
$month = floor(date('n')/$length)*$length;
if($length == 1)
	$month--;

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
$gofor = (((int)date('Y')-2011)*12) + floor(date('n')/$length);
for($x=0;$x<$gofor;$x++) {
	$period_start =      $year.'-'.str_pad($month-($length-1), 2, '0', STR_PAD_LEFT);
	$period_start_date = $year.'-'.str_pad($month-($length-1), 2, '0', STR_PAD_LEFT).'-01';
	$end_year = $year;
	$end_month = $month + 1;
	if ($end_month == 13) {
		$end_year++;
		$end_month = 1;
	}
	$period_end = $year.'-'.str_pad($month, 2, '0', STR_PAD_LEFT);
	$period_end_date = $end_year.'-'.str_pad($end_month, 2, '0', STR_PAD_LEFT).'-01';

	$prev_start = $pastYear.'-'.str_pad($pastMonth-($length-1), 2, '0', STR_PAD_LEFT);
	$prev_start_date = $pastYear.'-'.str_pad($pastMonth-($length-1), 2, '0', STR_PAD_LEFT).'-01';
	$past_end_year = $pastYear;
	$past_end_month = $pastMonth + 1;
	if ($past_end_month == 13) {
		$past_end_year++;
		$past_end_month = 1;
	}
	$prev_end = $pastYear.'-'.str_pad($pastMonth, 2, '0', STR_PAD_LEFT);
	$prev_end_date = $past_end_year.'-'.str_pad($past_end_month, 2, '0', STR_PAD_LEFT).'-01';

	// get the number of members for last period
	$csQuery = $db->translatedQuery( "SELECT COUNT(DISTINCT user_id) FROM transactions WHERE timestamp >= '$prev_start_date' AND timestamp < '$prev_end_date'" );
	$cs = (int)$csQuery->fetchScalar();

	// get the number of members for this period
	$ceQuery = $db->translatedQuery( "SELECT COUNT(DISTINCT user_id) FROM transactions WHERE timestamp >= '$period_start_date' AND timestamp < '$period_end_date'" );
	$ce = (int)$ceQuery->fetchScalar();

	// get the number of members who's first payment was this period
        $cnQuery = $db->translatedQuery("SELECT COUNT(*) FROM (SELECT user_id, MIN(timestamp) AS first FROM transactions GROUP BY user_id) x
                        WHERE first >= '$period_start_date' and first < '$period_end_date'");
	$cn = (int)$cnQuery->fetchScalar();

	// get the number of members who's first payment was before last period, they didn't pay last period but are paying this period
	$crQuery = $db->translatedQuery( "SELECT user_id
				FROM (SELECT user_id, MIN(timestamp) AS start FROM transactions GROUP BY user_id) as t2 natural join transactions as t1
				WHERE timestamp >= '$period_start_date' AND timestamp < '$period_end_date' AND start < '$period_start_date' AND user_id NOT IN (SELECT user_id FROM transactions AS t4 WHERE timestamp >= '$prev_start_date' AND timestamp < '$prev_end_date') 
				GROUP BY user_id ORDER BY user_id" );


	$cr = $crQuery->countReturnedRows();

	// get the number of members who paid last period but haven't paid this period
	$cdQuery = $db->translatedQuery( "SELECT user_id FROM transactions WHERE timestamp >= '$prev_start_date' AND timestamp < '$prev_end_date' AND user_id NOT IN (SELECT user_id FROM transactions WHERE timestamp >= '$period_start_date' AND timestamp < '$period_end_date') GROUP BY user_id ORDER BY user_id" );
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
		echo "<div class=\"alert alert-warning\">Numbers don't add up for period $period_start_date - $period_end_date<br/>
		(cn($cn) + cr($cr)) - cd($cd) != ce($ce) - cs($cs)</div>";
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
