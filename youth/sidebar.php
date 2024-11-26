<!-- includes/user_sidebar.php -->
<?php
// include '../includes/config.php';
// Check if user_id is set in the session
if (isset($_SESSION['user_id'])) {
  $user_id = $_SESSION['user_id'];

  // Prepare and execute the statement to fetch user details
  $stmt = $conn->prepare("SELECT firstname, lastname FROM users WHERE id = ?");
  $stmt->bind_param("i", $user_id);
  $stmt->execute();
  $stmt->bind_result($first_name, $last_name);
  $stmt->fetch();
  $stmt->close();
} else {
  // Set default values if user_id is not set
  $first_name = "Guest";
  $last_name = "";
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>User Sidebar</title>
  <style>
    p a {
      list-style-type: none;
    }

    p.btn::after {
      content: "-----------------------";
      height: 1px;
      margin-top: 5px;
      margin-bottom: 5px;
    }
  </style>
</head>

<body>
  <div class="container">
    <aside class="bg-dark-subtle position-fixed text-white"
      style="top: 80px; bottom: 0; left: 0; width: 250px; overflow-y: auto;">
      <div class="sidebar-sticky p-0">
        <h5 class="text-dark mt-5 text-cente text-capitalize">Welcome,
          <?php echo htmlspecialchars($first_name . ' ' . $last_name); ?>
        </h5>
        <h5 class="text-center">Navigation</h5>
        <div class="list-unstyled mb-4 p-3 text-center">
          <p class="btn"><a class="list-group-item list-group-item-action" href="resources.php">View Resources</a></p>

          <p class="btn "><a class="list-group-item list-group-item-action" href="encouragement.php">Share
              Encouragements</a></p>

          <p class="btn "><a class="list-group-item list-group-item-action" href="pledge.php">Share
              Pledge a giving</a></p>

        </div>
      </div>
    </aside>
  </div>
</body>

</html>