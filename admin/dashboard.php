<?php
// admin/dashboard.php

require_once '../includes/config.php';

// Check if admin is logged in
if (!isset($_SESSION['admin_id']))
{
  header("Location: login.php");
  exit();
}

// Fetch some statistics (optional)
$youth_count = 0;
$resource_count = 0;
$encouragement_count = 0;

$stmt = $conn->prepare("SELECT COUNT(*) FROM users WHERE role = 'youth'");
$stmt->execute();
$stmt->bind_result($youth_count);
$stmt->fetch();
$stmt->close();

$stmt = $conn->prepare("SELECT COUNT(*) FROM resources");
$stmt->execute();
$stmt->bind_result($resource_count);
$stmt->fetch();
$stmt->close();

$stmt = $conn->prepare("SELECT COUNT(*) FROM encouragements WHERE approved = 0");
$stmt->execute();
$stmt->bind_result($encouragement_count);
$stmt->fetch();
$stmt->close();
?>

<?php include '../includes/header.php'; ?>
<?php include 'admin_sidebar.php'; ?>


<h2>Admin Dashboard</h2>

<div class="row">
  <div class="col-md-4">
    <div class="card text-white bg-primary mb-3">
      <div class="card-header">Total Youths</div>
      <div class="card-body">
        <h5 class="card-title text-center"><?php echo $youth_count; ?></h5>
      </div>
      <div class="card-footer">
        <a class="text-white " href="manage_youth.php">see all youths</a>
      </div>
    </div>
  </div>
  <div class="col-md-4">
    <div class="card text-white bg-success mb-3">
      <div class="card-header">Total Resources</div>
      <div class="card-body">
        <h5 class="card-title text-center"><?php echo $resource_count; ?></h5>
      </div>
      <div class="card-footer">
        <a class="text-white" href="resources.php">View all resources added</a>
      </div>
    </div>
  </div>
  <div class="col-md-4">
    <div class="card text-white bg-warning mb-3">
      <div class="card-header">Pending Encouragements</div>
      <div class="card-body">
        <h5 class="card-title text-center"><?php echo $encouragement_count; ?></h5>
      </div>
      <div class="card-footer">
        <a class="text-white" href="moderate_encouragement.php">pending encouragements</a>
      </div>
    </div>
  </div>
</div>

<?php include '../includes/footer.php'; ?>