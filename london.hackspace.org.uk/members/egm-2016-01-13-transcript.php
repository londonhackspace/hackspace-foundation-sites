<?php require_once( $_SERVER['DOCUMENT_ROOT'] . '/../lib/init.php');

if (!isset($user)) {
    fURL::redirect('/login.php?forward=/members/egm-2016-01-13-transcript.php');
}

if ($user->isMember() && isset($_GET['dl']) && $_GET['dl'] == 'pdf') {
  header('Content-Type: application/pdf');
  header('Content-Disposition: inline');
  echo file_get_contents('../../var/LondonHackspaceEGM2016-v1.0.pdf');

} else if ($user->isMember() && isset($_GET['dl']) && $_GET['dl'] == 'html') {
  header('Content-Type: text/html');
  echo file_get_contents('../../var/LondonHackspaceEGM2016-v1.0.html');

} else {

if ($user->isMember()) {

$page = 'transcript';
$title = "Transcript";
$desc = '';
require('../header.php');

?>
<h2>Transcript of 2016 EGM</h2>

<p>Please treat this as confidential and do not pass it on to non-members, if in doubt please direct them to this page.</p>

<p>Select a format:

<ul>
  <li><a href="?dl=html">HTML</a></li>
  <li><a href="?dl=pdf">PDF</a></li>
</ul>

<? } else { ?>
   <p>You must be a member to use this page.</p>
<? }

require('../footer.php'); ?>
</body>
</html><?php

}
