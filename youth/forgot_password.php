<?php
include '../includes/header.php';
include '../includes/config.php';
$errors = [];
$success = false;

if ($_SERVER["REQUEST_METHOD"] == "POST") {
  $email = trim($_POST['email']);

  // Validate input
  if (empty($email)) {
    $errors[] = "Email is required.";
  } else {
    // Check if the email exists in the database
    $stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows == 1) {
      // Email exists, prompt to change password
      // You might want to store the email in a session to use it later in the reset password form
      $_SESSION['reset_email'] = $email;
      header("Location: reset_password.php"); // Redirect to password reset form
      exit();
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
  <title>Forgot Password</title>
</head>

<body>
  <div class="container mt-5">
    <h2>Forgot Password</h2>
    <?php
    if (!empty($errors)) {
      echo '<div class="alert alert-danger"><ul>';
      foreach ($errors as $error) {
        echo '<li>' . htmlspecialchars($error) . '</li>';
      }
      echo '</ul></div>';
    }
    ?>
    <form action="forgot_password.php" method="POST">
      <div class="form-group">
        <label for="email">Email*</label>
        <input type="email" class="form-control" id="email" name="email" required placeholder="abc@mail.com"
          value="<?php echo isset($email) ? htmlspecialchars($email) : ''; ?>">
      </div>
      <button type="submit" class="btn btn-primary">Submit</button>
    </form>
  </div>
</body>

</html>