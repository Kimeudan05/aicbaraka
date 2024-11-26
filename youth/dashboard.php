<?php
// index.php

// session_start();

// Check if the user is logged in
// if (!isset($_SESSION['user_id'])) {
//     header("Location: login.php");
//     exit();
// }

require_once '../includes/config.php'; // Include the database connection

// Fetch user details
$user_id = $_SESSION['user_id'];
$stmt = $conn->prepare("SELECT firstname, lastname FROM users WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$stmt->bind_result($first_name, $last_name);
$stmt->fetch();
$stmt->close();
?>

<?php include '../includes/header.php'; ?>

<div class="container-fluid">
  <div class="row">
    <!-- sidebar -->
    <?php include 'sidebar.php' ?>
  </div>
  <main class="ms-5">
    <div class="col-md-8 col-lg-9">
      <div class="text-center">
        <h2 class="mb-5">Welcome, <?php echo htmlspecialchars($first_name . ' ' . $last_name); ?>!</h2>
        <p>This is your youth dashboard. Here you can view resources, encouragements, and more.</p>
      </div>
    </div>
  </main>
</div>

<?php include '../includes/footer.php'; ?>