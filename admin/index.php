<?php
// Start session
// session_start();

// Check if admin is logged in
if (!isset($_SESSION['admin_id']))
{
  header("Location: login.php");  // Redirect to login if not logged in
  exit();
}

// Include the database configuration file
include('../includes/config.php');

// Fetch statistics (optional): you can fetch data like number of youths, resources, etc.

// Example fetch for total youths
$result = $conn->query("SELECT COUNT(*) as total_youths FROM youths");
$row = $result->fetch_assoc();
$total_youths = $row['total_youths'];
?>

<!DOCTYPE html>
<html lang="en">

  <head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard</title>
    <link rel="stylesheet" href="../css/bootstrap.min.css"> <!-- Assuming Bootstrap is linked -->
  </head>

  <body>
    <div class="container mt-5">
      <h1>Welcome, Admin</h1>
      <p>Total Youths Registered: <?php echo $total_youths; ?></p>

      <div class="mt-4">
        <a href="add_youth.php" class="btn btn-primary">Add Youth</a>
        <a href="view_resources.php" class="btn btn-secondary">Manage Resources</a>
        <a href="view_youths.php" class="btn btn-warning">View/Delete Youths</a>
        <a href="logout.php" class="btn btn-danger">Logout</a>
      </div>
    </div>

    <!-- Include Bootstrap JS -->
    <script src="../js/bootstrap.bundle.min.js"></script>
  </body>

</html>