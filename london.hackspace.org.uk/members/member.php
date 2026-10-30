<?php
$page = 'memberdetail';
require('../header.php');

if (!isset($user)) {
  fURL::redirect('/login.php?forward=/members/members.php');
}

try {
  $this_user = new User($_GET['id']);
} catch (fNotFoundException $e) {
  header('HTTP/1.1 404 Not Found');
  echo "User not found";
  require('../footer.php');
  echo "</body></html>";
  exit;
}

?>
<script src="https://unpkg.com/@popperjs/core@2"></script>
<script src="https://unpkg.com/tippy.js@6"></script>

<?php
if (($user->isMember() && $this_user->isMember()) || $user->isAdmin()) {
?>
  <h2>Member Info</h2>
  <table>
    <tr>
      <th>Name</th>
      <td><?= htmlspecialchars($this_user->getFullName()) ?></td>
    </tr>
    <tr>
      <th>ID</th>
      <td><?= $this_user->getMemberNumber() ?></td>
    </tr>
    <?php if ($this_user->getHasProfile()) { ?>
      <tr>
        <th>Profile</th>
        <td><a href="/members/profile.php?id=<?= $this_user->getId() ?>">Visit member's profile</a></td>
      </tr>
    <?php } ?>
    <?php if ($user->isAdmin()) { ?>
      <tr>
        <th>Member?</th>
        <td><?= ($this_user->isMember()) ? "Yes" : "No" ?></td>
      </tr>
      <tr>
        <th>Admin?</th>
        <td><?= ($this_user->isAdmin()) ? "Yes" : "No" ?></td>
      </tr>
      <tr id="gcstatus">
        <th>Gocardless Status</th>
        <td><?= $this_user->getGocardlessStatus() ?></td>
      </tr>
      <tr>
        <th>Email</th>
        <td><?= $this_user->getEmail() ?></td>
      </tr>
      <tr>
        <th>Address</th>
        <td><?= nl2br($this_user->getAddress() ?? '') ?></td>
      </tr>
      <tr>
        <th>Emergency Contact</th>
        <td><?= htmlspecialchars($this_user->getEmergencyName()) ?><br /><?= htmlspecialchars($this_user->getEmergencyPhone() ?? '') ?></td>
      </tr>
    <?php } ?>
  </table>

  <script>
    fetch('/gocardless/mandatelog?user=<?= $this_user->getId() ?>&count')
      .then(response => response.text())
      .then(data => {
        if (data != 0) {
          tippy('#gcstatus', {
            content: 'Loading...',
            allowHTML: true,
            maxWidth: 'none',
            delay: [500, 0],
            placement: 'right',
            onShow(instance) {
              // Code here is executed every time the tippy shows
              fetch('/gocardless/mandatelog?user=<?= $this_user->getId() ?>')
                .then(response => response.text())
                .then(data => {
                  instance.setContent(data);
                });
            }
          });
        }
      });
  </script>


  <?php if ($user->isAdmin()) { ?>
    <h3>Access Cards</h3>
    <table class="table">
      <thead>
        <tr>
          <th>Date added</th>
          <th>Card ID</th>
          <th style="text-align: center">Permission</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($this_user->buildCards() as $card) { ?>
          <tr class="<?= $card->getActive() ? 'allowed' : 'blocked' ?>">
            <td><?= $card->getAddedDate() ?></td>
            <td><?= implode(':', str_split($card->getUid(), 2)) ?></td>
            <?php if ($card->getActive()) { ?>
              <td class="rfidcard  rfidcard-active">Active</td>
            <?php } else { ?>
              <td class="rfidcard rfidcard-blocked">Blocked</td>
            <?php } ?>
          </tr>
        <?php 
        }
         ?>
      </tbody>
    </table>
  <?php } ?>

  <?php if ($user->isAdmin()) { ?>
    <h3>Recent Payments</h3>
    <table class="table">
      <thead>
        <tr>
          <th>Date</th>
          <th>Amount</th>
          <th>Type</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($this_user->buildPayments() as $payment) { ?>
          <tr id="payment-<?= $payment->getId() ?>" <?php if ($payment->getPaymentState() == 3) { ?>style="background-color: lightgrey" <?php } ?>>

            <td><?= $payment->getTimestamp() ?></td>
            <td>£<?= $payment->getAmount() ?></td>
            <td>
              <?php if ($payment->getPaymentType() == 1) { 
                      ?>Bank<?php 
                    } else { 
                      ?> GoCardless <?php
                    }
                    if ($payment->getPaymentState() == 3) { 
                      ?> (Failed) <?php 
                    } else if ($payment->getPaymentState() == 1) { 
                      ?> (Pending) <?php 
                    } else if ($payment->getPaymentState() == 4) { 
                      ?> (Retrying) <?php 
                    } 
              ?>
            </td>
            <?php if ($payment->getPaymentType() == 2) { ?>
              <script>
                tippy('#payment-<?= $payment->getId() ?>', {
                  content: 'Loading...',
                  allowHTML: true,
                  maxWidth: 'none',
                  delay: [500, 0],
                  placement: 'bottom',
                  arrow: false,
                  onShow(instance) {
                  // Code here is executed every time the tippy shows
                  fetch('/gocardless/paymentlog?user=<?= $this_user->getId() ?>&payment=<?= $payment->getId() ?>')
                    .then(response => response.text())
                    .then(data => {
                      instance.setContent(data);
                    });
                  }
                });
              </script>
            <?php } ?>          
          </tr>
        <?php } ?>
      </tbody>
    </table>
  <?php } ?>
<?php } else { ?>
  <p>You don't have access to this page.</p>
<?php }
require('../footer.php');
?>
</body>

</html>