<?php
// admin/resources.php

require_once '../includes/config.php';

// Check if the admin is logged in
if (!isset($_SESSION['admin_id'])) {
  header("Location: login.php");
  exit();
}

// Fetch all resources from the database
$query = "SELECT id, title, type, file_path,description, upload_date FROM resources";
$result = $conn->query($query);

include '../includes/header.php';
include "admin_sidebar.php";
?>

<div class="container mt-4">
  <h2>Manage Resources</h2>

  <?php
  // Display success or error messages
  if (isset($_SESSION['success_message'])) {
    echo '<div class="alert alert-success">' . $_SESSION['success_message'] . '</div>';
    unset($_SESSION['success_message']);
  }

  if (isset($_SESSION['error_message'])) {
    echo '<div class="alert alert-danger">' . $_SESSION['error_message'] . '</div>';
    unset($_SESSION['error_message']);
  }
  ?>

  <table class="table table-striped">
    <thead>
      <tr>
        <th>Title</th>
        <th>Type</th>
        <th>File</th>
        <th>Description</th>
        <th>Uploaded At</th>
        <th>Actions</th>
      </tr>
    </thead>
    <tbody>
      <?php while ($row = $result->fetch_assoc()): ?>
        <tr>
          <td><?php echo htmlspecialchars($row['title']); ?></td>
          <td><?php echo htmlspecialchars($row['type']); ?></td>
          <td>
            <?php if (in_array($row['type'], ['image/jpeg', 'image/png', 'image/gif'])): ?>
              <img src="../assets/resources/<?php echo htmlspecialchars($row['file_path']); ?>" alt="Resource Image" width="50" height="50">
            <?php else: ?>
              <a href="../assets/resources/<?php echo htmlspecialchars($row['file_path']); ?>" target="_blank">
                <?php echo htmlspecialchars(basename($row['file_path'])); ?>
              </a>
            <?php endif; ?>
          </td>
          <td><?php echo htmlspecialchars($row['description']); ?></td>
          <td><?php echo htmlspecialchars($row['upload_date']); ?></td>
          <td>
            <a href="edit_resource.php?id=<?php echo $row['id']; ?>" class="btn btn-warning btn-sm">Edit</a>

            <!-- view the resource -->
            <!-- <a href="../assets/resources/<?php echo htmlspecialchars($row['file_path']); ?>" target="_blank" class="btn btn-info btn-sm">View</a> -->

            <!-- delete the resource -->
            <a href="javascript:void(0);" class="btn btn-danger btn-sm" onclick="confirmDelete(<?php echo $row['id']; ?>)">Delete</a>
          </td>
        </tr>
      <?php endwhile; ?>
    </tbody>
  </table>
</div>

<!-- JavaScript to confirm deletion -->
<script>
  function confirmDelete(resourceId) {
    if (confirm("Are you sure you want to delete this resource? This action cannot be undone.")) {
      // If confirmed, redirect to delete_resource.php with the resource ID
      window.location.href = "delete_resource.php?id=" + resourceId;
    }
  }
</script>

<?php include '../includes/footer.php'; ?>