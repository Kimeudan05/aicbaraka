<?php
// view_resource.php

require_once '../includes/config.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
  header("Location: login.php");
  exit();
}

// Fetch resource details based on ID
if (isset($_GET['id'])) {
  $resource_id = $_GET['id'];
  $stmt = $conn->prepare("SELECT title, type, file_path, upload_date FROM resources WHERE id = ?");
  $stmt->bind_param("i", $resource_id);
  $stmt->execute();
  $stmt->bind_result($title, $type, $file_path, $upload_date);
  $stmt->fetch();
  $stmt->close();

  if (!$title) {
    // Handle case where resource is not found
    die("Resource not found.");
  }
} else {
  die("Invalid resource ID.");
}
?>

<?php include '../includes/header.php'; ?>
<?php include 'sidebar.php'; ?>

<h2>View Resource: <?php echo htmlspecialchars($title); ?></h2>

<div class="card mb-4">
  <?php if ($type == 'Picture'): ?>
    <img src="<?php echo htmlspecialchars($file_path); ?>" class="card-img-top" alt="<?php echo htmlspecialchars($title); ?>">
  <?php else: ?>
    <img src="assets/images/default_resource.png" class="card-img-top" alt="<?php echo htmlspecialchars($title); ?>">
  <?php endif; ?>
  <div class="card-body">
    <h5 class="card-title"><?php echo htmlspecialchars($title); ?></h5>
    <p class="card-text">Type: <?php echo htmlspecialchars($type); ?></p>
    <p class="card-text">Uploaded on: <?php echo htmlspecialchars($upload_date); ?></p>
    <a href="<?php echo htmlspecialchars($file_path); ?>" class="btn btn-primary" download>Download File</a>
  </div>
</div>

<?php include '../includes/footer.php'; ?>