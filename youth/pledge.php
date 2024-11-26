<?php
// Include necessary files


include '../includes/config.php'; // Database connection
include 'sidebar.php'; // Sidebar for youth

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
  header("Location: login.php");
  exit();
}

// Initialize an array to hold error messages
$errors = [];
//get the user data of the user who is loged in
$user_id = $_SESSION['user_id'];
$stmt = $conn->prepare("SELECT firstname, lastname, email FROM users WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$stmt->bind_result($first_name, $last_name, $email);
$stmt->fetch();
$stmt->close();


// Handle form submission

// if ($_SERVER["REQUEST_METHOD"] == "POST") {
//   // Sanitize and validate inputs
//   // $first_name = trim($_POST['first_name']);
//   // $last_name = trim($_POST['last_name']);
//   // $email = trim($_POST['email']);
//   $user_id = $_SESSION['user_id'];
//   $pledge_type = $_POST['pledge_type'];
//   $pledge_amount = trim($_POST['pledge_amount']);
//   $due_date = $_POST['due_date'];

// ... (Previous code)

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
  // Sanitize and validate inputs
  $user_id = $_SESSION['user_id'];
  $pledge_type = $_POST['pledge_type'];
  $pledge_amount = trim($_POST['pledge_amount']);
  $due_date = $_POST['due_date'];

  // Validate required fields
  if (empty($pledge_type) || empty($pledge_amount) || empty($due_date)) {
    $errors[] = "All fields are required.";
  }
  if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $errors[] = "Invalid email format.";
  }
  if (!is_numeric($pledge_amount) || $pledge_amount <= 0) {
    $errors[] = "Pledge amount must be a positive number.";
  }

  // If no errors, save the pledge in the database
  if (empty($errors)) {
    $stmt = $conn->prepare("INSERT INTO youth_pledges (youth_id, first_name, last_name, email, pledge_type, pledge_amount, due_date, status) VALUES (?, ?, ?, ?, ?, ?, ?, 'Unpaid')");
    $stmt->bind_param("issssis", $user_id, $first_name, $last_name, $email, $pledge_type, $pledge_amount, $due_date);

    if ($stmt->execute()) {
      // Redirect to the same page to prevent duplicate submission
      header("Location: pledge.php?status=success");
      header("Location: dashboard.php");
      exit();
    } else {
      $errors[] = "Failed to submit pledge. Please try again.";
    }
    $stmt->close();
  }
}

// Check for success message
$success_message = isset($_GET['status']) && $_GET['status'] === 'success';

include '../includes/header.php'; // Header file
?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
  <title>Pledge Form</title>
</head>

<body>
  <div class="container mt-5">
    <h2>Pledge Form</h2>
    <?php
    if ($success_message) {
      echo "<div class='alert alert-success'>Pledge submitted successfully!</div>";
    }
    if (!empty($errors)) {
      echo '<div class="alert alert-danger"><ul>';
      foreach ($errors as $error) {
        echo '<li>' . htmlspecialchars($error) . '</li>';
      }
      echo '</ul></div>';
    }
    ?>
    <form action="pledge.php" method="POST">
      <input type="hidden" name="user_id" value="<?php echo $_SESSION['user_id'] ?>">
      <div class="form-group">
        <label for="first_name">First Name*</label>
        <input type="text" readonly class="form-control" id="first_name" name="first_name" value="<?php echo htmlspecialchars($first_name); ?>" required>
      </div>
      <div class="form-group">
        <label for="last_name">Last Name*</label>
        <input type="text" readonly class="form-control" id="last_name" name="last_name" value="<?php echo htmlspecialchars($last_name); ?>" required>
      </div>
      <div class="form-group">
        <label for="email">Email*</label>
        <input type="email" readonly class="form-control" id="email" name="email" value="<?php echo htmlspecialchars($email); ?>" required>
      </div>
      <div class="form-group">
        <label for="pledge_type">Pledge Type*</label>
        <select class="form-control" id="pledge_type" name="pledge_type" required>
          <option value="">-- Select a Pledge Type --</option>
          <option value="Youth Kit">Youth Kit</option>
          <option value="Christmas Carols">Christmas Carols</option>
          <option value="Retreat Giving">Retreat Giving</option>
        </select>
      </div>
      <div class="form-group">
        <label for="pledge_amount">Pledge Amount*</label>
        <input type="number" class="form-control" id="pledge_amount" name="pledge_amount" min="100" value="100" required>
      </div>
      <div class="form-group">
        <label for="due_date">Due Date*</label>
        <input type="date" class="form-control" id="due_date" name="due_date" required>
      </div>
      <button type="submit" class="btn btn-primary">Submit Pledge</button>
    </form>
  </div>
  <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.3/dist/umd/popper.min.js"></script>
  <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>

</html>