<?php require_once( $_SERVER['DOCUMENT_ROOT'] . '/../lib/init.php');

# Persist closed alert boxes in the user's session. See also the event handler for
# '.alert .close[data-persist]' in main.js.

if (isset($_POST['suppress_profile_notification'])) {
  fSession::set('suppress_profile_notification', true);
}

?>
