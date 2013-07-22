<!DOCTYPE html>
<html>
<head>
	<meta charset="utf-8">
  <title>The Cost of Hacking – London Hackspace</title>
  <link href='//fonts.googleapis.com/css?family=Open+Sans:400,700' rel='stylesheet' type='text/css'>
  <link rel="stylesheet" href="css/base.css" type="text/css">
	<script type="text/javascript" src="https://www.google.com/jsapi"></script>

	<script type="text/javascript">
	google.load("visualization", "1", {packages:["corechart"]});
	google.setOnLoadCallback(drawCharts);
	
	function drawCharts() {
		
		// ===========
		// = Balance =
		// ===========
		var data = new google.visualization.DataTable();
		data.addColumn('string', 'Month');
    data.addColumn('number', 'Subscriptions £<?=number_format($income)?>');
		data.addColumn('number', 'Expenses £11,040');
		data.addRows([
      ['Current', <?=$income?>, 11040.00],
		]);

		var options = {
			axisTitlesPosition: 'none',
			legend: { position: 'top' },
			hAxis: { viewWindow: { min: 0 } },
			vAxis: { textPosition: 'none' },
			chartArea: { left: 10, top: 30, width: "100%", height: "60%"},
			fontSize: 16
		};

		var chart = new google.visualization.BarChart(document.getElementById('balance_div'));
		chart.draw(data, options);

		// ============
		// = Expenses =
		// ============
		var data = new google.visualization.DataTable();
		data.addColumn('string', 'Item');
		data.addColumn('number', 'Cost');
    data.addRows([
<? foreach ($budget as $name => $amount) { ?>
      ['<?=$name?> £<?=number_format($amount)?>', <?=$amount?>],
<? } ?>
      ]);


		var options = {
		  // title: 'London Hackspace Budget',
			chartArea: { left: 0, top: 5, width: "100%", height: "95%"},
			fontSize: 16
		};

		var chart = new google.visualization.PieChart(document.getElementById('expenses_div'));
		chart.draw(data, options);
	}
	</script>
</head>
<body>
	<div id="container">
		<header role="banner" class="text">
			<h1><img src="//london.hackspace.org.uk/images/london.png" width="60" height="60" alt="London Hackspace" /> The Cost of Hacking</h1>
      <p><strong>The London Hackspace is entirely funded by your membership subscriptions &amp; donations.</strong></p>
      <p>We've taken a massive leap in moving to and fitting out our new space on Hackney Road, and we're currently
      making a monthly loss and burning through our cash reserves.
      We can't spend more money on making the space more awesome until we're making a monthly surplus.</p>
		</header>
		
		<div id="main" role="main" class="text">
			<article>
				<header>
					<h2>Our Monthly Balance</h2>
				</header>
				<div id="balance_div" class="graph" style="width: 600px; height: 150px;"></div>
			</article>
			<article>
				<header>
					<h2>Our Monthly Expenses</h2>
				</header>
				<div id="expenses_div" class="graph" style="width: 600px; height: 300px;"></div>
			</article>

			<article>
				<header>
					<h2>How You Can Help</h2>
				</header>
				<ul>
					<li><strong class="large"><a href="https://london.hackspace.org.uk/signup.php">Join London Hackspace today!</a></strong> You'll get 24/7 access, a storage box for your projects, and warm fuzzy feelings for supporting us. We need members to pay an average £20/month for the space to survive.</li>
					<li>If you're already a member, <strong class="large">increase your monthly subscription payment</strong> by a few pounds.</li>
					<li>Volunteer to run a workshop on your area of expertise, or help us keep our infrastructure in shape.</li>
					<li>Let us know if there's something you'd like added to or changed in the Hackspace that would convince you to pay more each month.</li>
				</ul>
			</article>
		</div>
		<footer id="credits" role="contentinfo" class="text">
			<hr />
			<p>Updated in real time. Source: London Hackspace "<a href="https://wiki.london.hackspace.org.uk/view/Budget">Budget</a>" wiki page and the membership DB.</p>
		</footer>
	</div>
</body>
</html>
