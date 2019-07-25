<?php
$page = 'tools';
$title = 'Tool access';
$desc = '';
require '../header.php';
require '../../etc/config.php';

function get_tools_json($url) {
  global $ACSERVER_ADDRESS;
  global $ACSERVER_KEY;
  $opts = array(
    'http'=>array(
      'method'=>"GET",
      'header'=>"API-KEY: ".$ACSERVER_KEY."\r\n"
    )
  );
  $context = stream_context_create($opts);

  $result = file_get_contents($ACSERVER_ADDRESS . $url, false, $context);
  return $result;
}

if (isset($_GET['summary'])){
    require_once( $_SERVER['DOCUMENT_ROOT'] . '/../lib/init.php');

    if (isset($_GET['anonymous'])) {
        $url = "/api/get_tools_status";
        header("Access-Control-Allow-Origin: *"); // Ensure we can use this API from anywhere
    } else {
        ensureMember();
        $url = "/api/get_tools_summary_for_user/" . $user->getId();
    }

    $result = get_tools_json($url);

    header('Content-Type: application/json');

    if ($result === FALSE) {
        echo '{"error": "Failed to fetch data"}';
    } else {
        echo $result;
    }

    //print_r();
    //echo "\nJSON status: " . json_last_error();
    die();
}

ensureMember();

$url = "/api/get_tools_summary_for_user/" . $user->getId();
$result = get_tools_json($url);
$result = json_decode($result);

?>

<h2>Tool access</h2>

<div>

    <table class="table table-bordered table-striped tool-summary">
        <thead>
          <tr>
            <th>Tool</th>
            <th>Status<small>Status and availability</small></th>
            <th>Status message <small>Any extra info</small></th>
            <th>Access? <small>Your access level</small></th>
            <th>Type</th>

          </tr>
        </thead>
        <tbody>

<?php
$start = '<tr class="well well-small">';
$end = '</tr>';

foreach ($result as $tool)
{
  // skip doorbots
  if ($tool->type == "Unrestricted Door") {
    continue;
  }

  echo $start."\n";

  echo "<th>".$tool->name."</th>";
  $class = "";

  if ($tool->status == 'Operational') {
    $class = 'is-visible';
  }

  if ($tool->status == 'Out of service') {
    $class = 'is-bad';
  }

  if ($tool->status == 'In use') {
    $class = 'is-special';
  }
  echo "<td class=\"".$class."\">".$tool->status."</td>";

  $class = "";

  if ($tool->status_message == 'OK') {
    $class = 'is-hidden';
  }

  if ($tool->status_message != 'OK') {
    $class = 'is-bad';
  }

  echo "<td class=\"".$class."\">".$tool->status_message."</td>";
  $class = "";

  if ($tool->permission =='user') {
    $class = 'is-visible';
  }
  if ($tool->permission == 'unauthorised') {
    $class = 'is-bad';
  }
  if ($tool->permission == 'maintainer') {
    $class = 'is-special';
  }

  echo "<td class=\"".$class."\">".$tool->permission."</td>";
  echo "<td>".$tool->type."</td>";

  echo "\n".$end."\n";

}

?>
        </tbody>
 </table>
</div>
<div>
  Note there are
  <a href="https://wiki.london.hackspace.org.uk/view/Category:Equipment">
    many, many more non AC node (Access Controlled) tools on the wiki.
  </a>
</div>

<?php require('../footer.php'); ?>
</body>

</html>
