<?php
// resources.php

require_once '../includes/config.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
  header("Location: login.php");
  exit();
}

// Fetch user details if logged in
if (isset($_SESSION['user_id'])) {
  $user_id = $_SESSION['user_id'];
  $stmt = $conn->prepare("SELECT firstname, lastname FROM users WHERE id = ?");
  $stmt->bind_param("i", $user_id);
  $stmt->execute();
  $stmt->bind_result($first_name, $last_name);
  $stmt->fetch();
  $stmt->close();
}

// Fetch all resources
$resources = [];
$stmt = $conn->prepare("SELECT id, title, type, file_path, upload_date FROM resources ORDER BY upload_date DESC");
$stmt->execute();
$result = $stmt->get_result();
while ($row = $result->fetch_assoc()) {
  $resources[] = $row;
}
$stmt->close();
?>

<?php include '../includes/header.php'; ?>
<?php include 'sidebar.php'; ?>

<h2>Resources</h2>

<div class="row">
  <?php foreach ($resources as $resource): ?>
    <div class="col-md-4 mb-4">
      <div class="card h-100">
        <?php if ($resource['type'] == 'Picture'): ?>
          <img src="<?php echo htmlspecialchars($resource['file_path']); ?>" class="card-img-top" alt="<?php echo htmlspecialchars($resource['title']); ?>">


        <?php else: ?>
          <img src="assets/images/profiles/6725a2b3d59ff.jpg" class="card-img-top" alt="<?php echo htmlspecialchars($resource['title']); ?>">
        <?php endif; ?>
        <div class="card-body">

          <h5 class="card-title"><?php echo htmlspecialchars($resource['title']); ?></h5>
          <p class="card-text">Type: <?php echo htmlspecialchars($resource['type']); ?></p>
          <p class="card-text">Uploaded on: <?php echo htmlspecialchars($resource['upload_date']); ?></p>
          <a href="view_resource.php?id=<?php echo $resource['id']; ?>" class="btn btn-primary">View</a>
          <a href="<?php echo htmlspecialchars($resource['file_path']); ?>" class="btn btn-secondary" download>Download</a>
        </div>
      </div>
    </div>
  <?php endforeach; ?>
</div>

<?php include '../includes/footer.php'; ?>