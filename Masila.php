<?php
// admin/login.php

require_once '../includes/config.php';

$errors = [];

if ($_SERVER["REQUEST_METHOD"] == "POST") {
  // Sanitize inputs
  $email = sanitize_input($_POST['email']);
  $password = $_POST['password'];

  // Validate inputs
  if (empty($email) || empty($password)) {
    $errors[] = "Both email and password are required.";
  }
  if ($stmt->num_rows == 1) {
    $stmt->bind_result($id, $is_admin);
    $stmt->fetch();

    if ($is_admin) {
      // Set admin session
      $_SESSION['admin_id'] = $id;
      header("Location: dashboard.php");
      exit();
    } else {
      // Handle regular user login (if applicable)
      // You can add your user password verification logic here
      $errors[] = "Access denied. This account is not an admin.";
    }
  } else {
    $errors[] = "No account found with that email.";
  }


  // if(empty($errors)){
  //     $stmt = $conn->prepare("SELECT id, password FROM users WHERE email = ? AND role = 'admin'");
  //     $stmt->bind_param("s", $email);
  //     $stmt->execute();
  //     $stmt->store_result();

  //     if($stmt->num_rows == 1){
  //         $stmt->bind_result($id, $hashed_password);
  //         $stmt->fetch();
  //         if(password_verify($password, $hashed_password)){
  //             // Set admin session
  //             $_SESSION['admin_id'] = $id;
  //             header("Location: dashboard.php");
  //             exit();
  //         } else {
  //             $errors[] = "Incorrect password.";
  //         }
  //     } else {
  //         $errors[] = "No admin account found with that email.";
  //     }

  //     $stmt->close();
  // }
}
?>

<?php include '../includes/header.php'; ?>

<h2>Admin Login</h2>

<?php
if (!empty($errors)) {
  echo '<div class="alert alert-danger"><ul>';
  foreach ($errors as $error) {
    echo '<li>' . $error . '</li>';
  }
  echo '</ul></div>';
}
?>

<form action="login.php" method="POST">
  <div class="mb-3">
    <label for="email" class="form-label">Admin Email*</label>
    <input type="email" class="form-control" id="email" name="email" required value="<?php echo isset($email) ? htmlspecialchars($email) : ''; ?>">
  </div>
  <div class="mb-3">
    <label for="password" class="form-label">Password*</label>
    <input type="password" class="form-control" id="password" name="password" required>
  </div>
  <button type="submit" class="btn btn-primary">Login as Admin</button>
</form>

<?php include '../includes/footer.php'; ?>
<script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.3/dist/umd/popper.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>