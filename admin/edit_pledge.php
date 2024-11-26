<?php
include '../includes/config.php';

$id = $_GET['id'];
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $amount_paid = $_POST['amount_paid'];
  $status = $_POST['status'];

  // Fetch the original pledge amount
  $query = "SELECT pledge_amount FROM youth_pledges WHERE id = ?";
  $stmt = $conn->prepare($query);
  $stmt->bind_param("i", $id);
  $stmt->execute();
  $stmt->bind_result($pledge_amount);
  $stmt->fetch();
  $stmt->close();

  // Ensure amount paid does not exceed the pledge amount
  if ($amount_paid > $pledge_amount) {
    $errors[] = "Amount paid cannot exceed the total pledge amount.";
  } else {
    // Update the pledge record
    $status = ($amount_paid == $pledge_amount) ? 'Paid' : 'Unpaid';
    $update_query = "UPDATE youth_pledges SET amount_paid = ?, status = ? WHERE id = ?";
    $stmt = $conn->prepare($update_query);
    $stmt->bind_param("dsi", $amount_paid, $status, $id);
    if ($stmt->execute()) {
      header("Location: manage_pledges.php");
      exit();
    } else {
      $errors[] = "Failed to update the pledge record.";
    }
  }
}

// Fetch the current pledge data
$query = "SELECT first_name, last_name, pledge_type, pledge_amount, amount_paid, status FROM youth_pledges WHERE id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $id);
$stmt->execute();
$stmt->bind_result($first_name, $last_name, $pledge_type, $pledge_amount, $amount_paid, $status);
$stmt->fetch();
$stmt->close();
?>

<form action="edit_pledge.php?id=<?= $id ?>" method="POST">
  <div class="form-group">
    <label>Pledge Type: <?= htmlspecialchars($pledge_type) ?></label>
  </div>
  <div class="form-group">
    <label>Pledge Amount: <?= htmlspecialchars($pledge_amount) ?></label>
  </div>
  <div class="form-group">
    <label for="amount_paid">Amount Paid</label>
    <input type="number" class="form-control" id="amount_paid" name="amount_paid" value="<?= htmlspecialchars($amount_paid) ?>" required>
  </div>
  <div class="form-group">
    <label for="status">Status</label>
    <select class="form-control" id="status" name="status">
      <option value="Unpaid" <?= $status === 'Unpaid' ? 'selected' : '' ?>>Unpaid</option>
      <option value="Paid" <?= $status === 'Paid' ? 'selected' : '' ?>>Paid</option>
    </select>
  </div>
  <?php if (!empty($errors)): ?>
    <div class="alert alert-danger">
      <ul>
        <?php foreach ($errors as $error): ?>
          <li><?= htmlspecialchars($error) ?></li>
        <?php endforeach; ?>
      </ul>
    </div>
  <?php endif; ?>
  <button type="submit" class="btn btn-success">Update</button>
</form>