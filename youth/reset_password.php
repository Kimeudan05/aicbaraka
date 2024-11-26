<?php
include '../includes/header.php';
include '../includes/config.php';
$errors = [];
$success = false;

if ($_SERVER["REQUEST_METHOD"] == "POST") {
  $email = trim($_POST['email']);
  $new_password = trim($_POST['new_password']);
  $confirm_password = trim($_POST['confirm_password']);

  // Validate input
  if (empty($email) || empty($new_password) || empty($confirm_password)) {
    $errors[] = "All fields are required.";
  } elseif ($new_password !== $confirm_password) {
    $errors[] = "Passwords do not match.";
  } else {
    // Check if the email exists in the database
    $stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows == 1) {
      // Hash the new password
      $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);

      // Update the password in the database
      $stmt = $conn->prepare("UPDATE users SET password = ? WHERE email = ?");
      $stmt->bind_param("ss", $hashed_password, $email);
      if ($stmt->execute()) {
        $success = true;
      } else {
        $errors[] = "Failed to update password. Please try again.";
      }
    } else {
      $errors[] = "No account found with that email.";
    }
    $stmt->close();
  }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
  <title>Change Password</title>
</head>

<body>
  <div class="container mt-5">
    <h2>Change Password</h2>
    <?php
    if ($success) {
      echo "<div class='alert alert-success'>Your password has been changed successfully.</div>";

      echo '<a href="login.php" class="btn btn-primary">Login</a>';
      header("Location: login.php");
    }
    if (!empty($errors)) {
      echo '<div class="alert alert-danger"><ul>';
      foreach ($errors as $error) {
        echo '<li>' . htmlspecialchars($error) . '</li>';
      }
      echo '</ul></div>';
    }
    ?>
    <form action="reset_password.php" method="POST">
      <div class="form-group">
        <label for="email">Email*</label>
        <input type="email" class="form-control" id="email" name="email" required placeholder="abc@mail.com"
          value="<?php echo isset($email) ? htmlspecialchars($email) : ''; ?>">
      </div>
      <div class="form-group">
        <label for="new_password">New Password*</label>
        <input type="password" class="form-control" id="new_password" name="new_password" required>
      </div>
      <div class="form-group">
        <label for="confirm_password">Confirm New Password*</label>
        <input type="password" class="form-control" id="confirm_password" name="confirm_password" required>
      </div>
      <button type="submit" class="btn btn-primary">Change Password</button>
    </form>
  </div>
</body>

</html>