<?php
// login.php
ob_start();
include '../includes/config.php';
include '../includes/header.php';
include 'sidebar.php';
$errors = [];


if ($_SERVER["REQUEST_METHOD"] == "POST") {
  // Sanitize inputs
  $email = ($_POST['email']);
  $password = $_POST['password'];

  // Validate inputs
  if (empty($email) || empty($password)) {
    $errors[] = "Both email and password are required.";
  }

  if (empty($errors)) {
    $stmt = $conn->prepare("SELECT id, password, role FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows == 1) {
      $stmt->bind_result($id, $hashed_password, $role);
      $stmt->fetch();
      if (password_verify($password, $hashed_password)) {
        // Set session variables
        if ($role == 'admin') {
          $_SESSION['admin_id'] = $id;
          $errors[] = "A youth with such email does not exist.";
          exit();
        } else {
          $_SESSION['user_id'] = $id;
          header("Location: dashboard.php");
          exit();
        }
      } else {
        $errors[] = "Incorrect password.";
      }
    } else {
      $errors[] = "No account found with that email.";
    }

    $stmt->close();
  }
}
ob_end_flush();
?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
  <style>
    body {
      background-color: green;
      font-family: Arial, sans-serif;
      padding: 20px;
    }

    .login-container {
      max-width: 900px;
      margin: 0 auto;
      display: flex;
      flex-wrap: wrap;
      background: #ffffff;
      border-radius: 10px;
      box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
      overflow: hidden;
    }

    .left-section,
    .right-section {
      padding: 40px;
      flex: 1;
      display: flex;
      flex-direction: column;
      justify-content: center;
    }

    .left-section {
      background-color: #343a40;
      color: #ffffff;
    }

    .left-section h2 {
      font-size: 2rem;
      margin-bottom: 20px;
    }

    .left-section p {
      font-size: 1rem;
      line-height: 1.5;
    }

    .form-control {
      border-radius: 5px;
      position: relative;
    }

    .password-wrapper {
      position: relative;
    }

    .hide {
      position: absolute;
      right: 15px;
      top: 50%;
      /* transform: translateY(-50%); */
      cursor: pointer;
    }

    .btn-primary {
      background-color: #343a40;
      border-color: #343a40;
      border-radius: 5px;
      transition: background-color 0.3s ease;
    }

    .btn-primary:hover {
      background-color: #23272b;
    }

    @media (max-width: 1080px) {
      .login-container {
        flex-direction: column;
        max-width: 70%;
      }

      body {
        padding-left: 200px;
      }

      .left-section,
      .right-section {
        padding: 30px;
      }

      .left-section {
        text-align: center;
      }
    }
  </style>
  <title>Login Page</title>
</head>

<body>
  <div class="login-container">
    <div class="left-section">
      <h2>Welcome Back</h2>
      <p>Sign in to your account and continue your journey with our community. We are glad to have you back and
        hope to make your experience meaningful.</p>
      <p>Don't have an account? <a href="register.php" class="btn btn-primary">Register now</a></p>
    </div>
    <div class="right-section">
      <h3>Login</h3>
      <?php
      if (!empty($errors)) {
        echo '<div class="alert alert-danger"><ul>';
        foreach ($errors as $error) {
          echo '<li>' . htmlspecialchars($error) . '</li>';
        }
        echo '</ul></div>';
      }
      ?>
      <form action="login.php" method="POST">
        <div class="form-group">
          <label for="email">Email*</label>
          <input type="email" class="form-control" id="email" name="email" required placeholder="abc@mail.com"
            value="<?php echo isset($email) ? htmlspecialchars($email) : ''; ?>">
        </div>
        <div class="form-group password-wrapper">
          <label for="password">Password*</label>
          <input type="password" class="form-control" id="password" name="password" required
            placeholder="Enter password">
          <span class="hide"><i class="fa fa-eye" aria-hidden="true"></i></span>
        </div>
        <button type="submit" class="btn btn-primary btn-block">Login</button>
        <p><a href="forgot_password.php">Forgot Password?</a></p>
      </form>
    </div>
  </div>

  <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.3/dist/umd/popper.min.js"></script>
  <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
  <script>
    document.querySelector('.hide').addEventListener('click', function() {
      const passwordField = document.getElementById('password');
      const icon = this.querySelector('i');
      if (passwordField.type === 'password') {
        passwordField.type = 'text';
        icon.classList.remove('fa-eye');
        icon.classList.add('fa-eye-slash');
      } else {
        passwordField.type = 'password';
        icon.classList.remove('fa-eye-slash');
        icon.classList.add('fa-eye');
      }
    });
  </script>
</body>

</html>