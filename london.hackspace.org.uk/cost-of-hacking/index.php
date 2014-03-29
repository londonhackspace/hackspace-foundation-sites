<?php
require_once( $_SERVER['DOCUMENT_ROOT'] . '/../lib/init.php');
$income = $db->translatedQuery("SELECT sum(amount) FROM transactions WHERE timestamp > date('now', 'start of month', '-1 month') AND timestamp < date('now', 'start of month')")->fetchScalar();

function get_budget() {
  $started = false;
  $budget = Array();
  foreach(explode("\n", file_get_contents('http://wiki.london.hackspace.org.uk/view/Budget?action=raw')) as $row) {
    if ($row == '{|') {
      $started = true;
    } else if ($started && $row == '|}') {
      return $budget;
    } else if ($started && preg_match('/^\|\s*(.*)\s*\|\|\s*£([0-9]+)/', $row, $matches)) {
      $budget[trim($matches[1])] = $matches[2];
    }
  }
}

$budget = get_budget();

$expenses = 0;
foreach($budget as $line) {
  $expenses += $line;
}
$expenses = round($expenses);

require_once('template.php');
