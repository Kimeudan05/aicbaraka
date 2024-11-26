<?php
include '../includes/config.php';

// Fetch all pledges from the database
$query = "SELECT id, first_name, last_name, pledge_type, pledge_amount, amount_paid, status, due_date FROM youth_pledges";
$result = $conn->query($query);
?>

<table class="table">
  <thead>
    <tr>
      <th>Name</th>
      <th>Pledge Type</th>
      <th>Pledge Amount</th>
      <th>Amount Paid</th>
      <th>Status</th>
      <th>Due Date</th>
      <th>Actions</th>
    </tr>
  </thead>
  <tbody>
    <?php while ($row = $result->fetch_assoc()): ?>
      <tr>
        <td><?= htmlspecialchars($row['first_name'] . ' ' . $row['last_name']) ?></td>
        <td><?= htmlspecialchars($row['pledge_type']) ?></td>
        <td><?= htmlspecialchars($row['pledge_amount']) ?></td>
        <td><?= htmlspecialchars($row['amount_paid']) ?></td>
        <td><?= htmlspecialchars($row['status']) ?></td>
        <td><?= htmlspecialchars($row['due_date']) ?></td>
        <td>
          <a href="edit_pledge.php?id=<?= $row['id'] ?>" class="btn btn-primary">Edit</a>
        </td>
      </tr>
    <?php endwhile; ?>
  </tbody>
</table>