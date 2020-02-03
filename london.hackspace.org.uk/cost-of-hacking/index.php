<?php
require_once( $_SERVER['DOCUMENT_ROOT'] . '/../lib/init.php');
$income = $db->translatedQuery("SELECT sum(amount) FROM transactions
                                WHERE timestamp > date_trunc('month', now() - INTERVAL '1 month')
                                AND timestamp < date_trunc('month', now())")->fetchScalar();

function get_budget() {
  $started = false;
  $budget = Array(
    'Rent + Service Charge' => '3004',
    'Business Rates' => '850',
    'Reserve' => '500',
    'Supplies' => '800',
    'Basic Cleaning' => '200',
    'Rubbish' => '200'
  );
  //commented out while wiki is down
  /* 
  $dynamic = file_get_contents('http://wiki.london.hackspace.org.uk/view/Budget?action=raw');
  foreach(explode("\n", $dynamic) as $row) {
    if ($row == '{|') {
      $started = true;
    } else if ($started && $row == '|}') {
      return $budget;
    } else if ($started && preg_match('/^\|\s*(.*)\s*\|\|\s*£([0-9]+)/', $row, $matches)) {
      $budget[trim($matches[1])] = $matches[2];
    }
  }
   */
  return $budget;
}

$budget = get_budget();

$expenses = 0;
foreach($budget as $line) {
  $expenses += $line;
}
$expenses = round($expenses);

if(isset($_GET['format']) && $_GET['format'] == "json") {
  ?>
{
    "income": <?php echo $income; ?>,
    "budget": <?php echo $expenses; ?>
}
  <?php
}
else {
  require_once('template.php');
}
