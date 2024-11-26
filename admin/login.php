<?php
// admin/login.php

require_once '../includes/config.php';
require_once '../includes/password.php';

$errors = [];

if ($_SERVER["REQUEST_METHOD"] == "POST") {
  // Sanitize inputs
  $email = sanitize_input($_POST['email']);
  $password = $_POST['password'];

  // Validate inputs
  if (empty($email) || empty($password)) {
    $errors[] = "Both email and password are required.";
  } else {
    // Server-side strong password validation
    if (!preg_match('/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[\W_]).{8,}$/', $password)) {
      $errors[] = "Password must be at least 8 characters long, contain at least one uppercase letter, one lowercase letter, one number, and one special character.";
    }
  }

  if (empty($errors)) {
    // Prepare a statement to fetch the admin's details
    $stmt = $conn->prepare("SELECT id, password,name, role FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows == 1) {
      // Bind the result (id, hashed password, role)
      $stmt->bind_result($id, $hashed_password, $name, $role);
      $stmt->fetch();

      // Verify the role and password
      if ($role === 'admin') {
        if (password_verify($password, $hashed_password)) {
          // Set admin session
          $_SESSION['admin_id'] = $id;
          $_SESSION['admin_name'] = $name;
          header("Location: dashboard.php");
          exit();
        } else {
          $errors[] = "Incorrect password.";
        }
      } else {
        $errors[] = "Access denied. This account is not an admin.";
      }
    } else {
      $errors[] = "No account found with that email.";
    }

    $stmt->close();
  }
}
?>

<?php include '../includes/header.php'; ?>
<?php include "admin_sidebar.php"; ?>



<?php
// Display errors if any
if (!empty($errors)) {
  echo '<div class="alert alert-danger"><ul>';
  foreach ($errors as $error) {
    echo '<li>' . $error . '</li>';
  }
  echo '</ul></div>';
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Admin Login</title>
  <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
  <link rel="stylesheet" href="https://kit.fontawesome.com/a076d05399.js" crossorigin="anonymous">
  <style>
    .password-container {
      position: relative;
    }

    .toggle-password {
      position: absolute;
      right: 15px;
      top: 50%;
      /* transform: translateY(-50%); */
      cursor: pointer;
      color: #495057;
    }

    @media (max-width: 576px) {
      h2 {
        font-size: 1.5rem;
      }
    }
  </style>
</head>

<body>
  <div class="container mt-5 bg-body-secondary p-3">
    <form action="login.php" method="POST" class="w-75 mx-auto mb-3">
      <h2 class="text-center">Admin Login</h2>
      <div class="mb-3">
        <label for="email" class="form-label">Admin Email*</label>
        <input type="email" class="form-control" id="email" name="email" required placeholder="Enter admin email" value="<?php echo isset($email) ? htmlspecialchars($email) : ''; ?>">
      </div>
      <div class="mb-3 password-container">
        <label for="password" class="form-label">Password*</label>
        <input type="password" class="form-control" id="password" name="password" required placeholder="Enter password">
        <span class="toggle-password" onclick="togglePasswordVisibility()">
          <i class="fa fa-eye" aria-hidden="true"></i>
        </span>
      </div>
      <button type="submit" class="btn btn-primary">Login as Admin</button>
    </form>
  </div>

  <script>
    function togglePasswordVisibility() {
      const passwordInput = document.getElementById('password');
      const eyeIcon = document.querySelector('.toggle-password i');

      if (passwordInput.type === 'password') {
        passwordInput.type = 'text';
        eyeIcon.classList.remove('fa-eye');
        eyeIcon.classList.add('fa-eye-slash');
      } else {
        passwordInput.type = 'password';
        eyeIcon.classList.remove('fa-eye-slash');
        eyeIcon.classList.add('fa-eye');
      }
    }

    // Example strong password validation (client-side)
    document.getElementById('password').addEventListener('input', function() {
      const strongPasswordRegex = /^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[\W_]).{8,}$/;
      this.setCustomValidity(strongPasswordRegex.test(this.value) ? "" : "Password must be at least 8 characters long, contain at least one uppercase letter, one lowercase letter, one number, and one special character.");
    });
  </script>
</body>

</html>

<?php include '../includes/footer.php'; ?>