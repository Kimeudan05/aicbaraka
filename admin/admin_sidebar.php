<!-- includes/youth_sidebar.php -->
<?php
// Check if admin_id is set in the session
if (isset($_SESSION['admin_id'])) {
  $user_id = $_SESSION['admin_id'];

  // Prepare and execute the statement to fetch admin details
  $stmt = $conn->prepare("SELECT firstname, lastname FROM users WHERE id = ?");
  $stmt->bind_param("i", $user_id);
  $stmt->execute();
  $stmt->bind_result($first_name, $last_name);
  $stmt->fetch();
  $stmt->close();
} else {
  // Set default values if admin_id is not set
  $first_name = "Guest";
  $last_name = "";
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Admin Sidebar</title>
  <style>
    p a {
      list-style-type: none;
      font-size: 1.3rem;
    }

    p.btn::after {
      content: "---------------------";
      height: 2px;
      width: 0;
      /* background: transparent; */
      transition: width .5s ease, background-color .5s ease;
    }

    p.btn:hover {
      background-color: #343a40;
      color: #ffffff;

    }
  </style>
</head>

<body>
  <div class="container">
    <aside class="bg-dark-subtle position-fixed text-dark me-5"
      style="top: 80px; bottom: 0; left: 0; width: 250px; overflow-y: auto; ">
      <div class="sidebar-sticky p-0">
        <h5 class="text-dark mt-3 text-center">Welcome,
          <?php echo htmlspecialchars($first_name . ' ' . $last_name); ?>
        </h5>
        <h5 class="text-center">Admin Navigation</h5>
        <div class="list-unstyled mb-4 p-3 text-center navigator">
          <p class="btn"><a href="add_youth.php" class="list-group-item list-group-item-action">Add Youth</a>
          </p>
          <p class="btn"><a href="manage_youth.php" class="list-group-item list-group-item-action">Manage Youths</a>

          <p class="btn"><a href="manage_pledges.php" class="list-group-item list-group-item-action">
              Manage pledges</a></p>
          </p>
          <p class="btn"><a href="upload_resources.php" class="list-group-item list-group-item-action">Upload
              Resources</a></p>
          <p class="btn"><a href="resources.php" class="list-group-item list-group-item-action">Manage resources</a></p>
          <p class="btn"><a href="moderate_encouragement.php" class="list-group-item list-group-item-action">Moderate
              Encouragements</a></p>
          <p class="btn"><a href="view_encouragements.php" class="list-group-item list-group-item-action">View
              Approved Encouragements</a></p>
        </div>
      </div>
    </aside>
  </div>
</body>

</html>