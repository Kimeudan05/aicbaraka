<?php
// admin/view_encouragements.php

require_once '../includes/config.php';
// session_start();

// Check if admin is logged in
if (!isset($_SESSION['admin_id']))
{
  header("Location: login.php");
  exit();
}

// Fetch all approved encouragements along with user info
$query = "
    SELECT e.id, e.content, e.date_shared, u.name
    FROM encouragements e
    LEFT JOIN users u ON e.user_id = u.id 
    WHERE e.approved = 1 
    ORDER BY e.date_shared DESC
";
$result = $conn->query($query);

include '../includes/header.php';
include "admin_sidebar.php";
?>

<h2>Approved Encouragements</h2>

<table class="table table-striped">
  <thead>
    <tr>
      <th>Encouragement</th>
      <th>Shared by</th>
      <th>Date Shared</th>
      <th>Actions</th>
    </tr>
  </thead>
  <tbody>
    <?php while ($row = $result->fetch_assoc()): ?>
      <tr>
        <td><?php echo htmlspecialchars($row['content']); ?></td>
        <td><?php echo htmlspecialchars($row['name']); ?></td>
        <td><?php echo htmlspecialchars($row['date_shared']); ?></td>
        <td>
          <a href="edit_encouragement.php?id=<?php echo $row['id']; ?>" class="btn btn-warning btn-sm">Edit</a>
          <a href="javascript:void(0);" class="btn btn-danger btn-sm"
            onclick="confirmDelete(<?php echo $row['id']; ?>)">Delete</a>
        </td>
      </tr>
    <?php endwhile; ?>
  </tbody>
</table>

<script>
  function confirmDelete(encouragementId) {
    if (confirm("Are you sure you want to delete this encouragement? This action cannot be undone.")) {
      window.location.href = "delete_encouragement.php?id=" + encouragementId;
    }
  }
</script>

<?php include '../includes/footer.php'; ?>