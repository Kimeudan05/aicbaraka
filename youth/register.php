<?php
// Include necessary PHP setup here (e.g., session management, form processing)
// register.php

require_once '../includes/config.php';
// require_once '../includes/password.php';

$errors = [];
$success = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
  // Sanitize inputs
  $firstname = sanitize_input($_POST['firstname']);
  $lastname = sanitize_input($_POST['lastname']);
  $email = sanitize_input($_POST['email']);
  $phone = sanitize_input($_POST['phone']);
  $password = $_POST['password'];
  $confirm_password = $_POST['confirm_password'];

  // Validate inputs
  if (empty($firstname) || empty($lastname) || empty($email) || empty($phone) || empty($password) || empty($confirm_password)) {
    $errors[] = "All fields except profile picture are required.";
  }

  if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $errors[] = "Invalid email format.";
  }

  if ($password !== $confirm_password) {
    $errors[] = "Passwords do not match.";
  }

  // Check if email already exists
  $stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
  $stmt->bind_param("s", $email);
  $stmt->execute();
  $stmt->store_result();
  if ($stmt->num_rows > 0) {
    $errors[] = "Email is already registered.";
  }
  $stmt->close();

  // Handle profile picture upload
  $profile_picture = null;
  if (isset($_FILES['profile_picture']) && $_FILES['profile_picture']['error'] == 0) {
    $allowed = ['jpg', 'jpeg', 'png', 'gif'];
    $filename = $_FILES['profile_picture']['name'];
    $file_tmp = $_FILES['profile_picture']['tmp_name'];
    $file_ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));

    if (in_array($file_ext, $allowed)) {
      $new_filename = uniqid() . "." . $file_ext;
      $upload_dir = "assets/images/profiles/";
      if (!is_dir($upload_dir)) {
        mkdir($upload_dir, 0755, true);
      }
      move_uploaded_file($file_tmp, $upload_dir . $new_filename);

      $profile_picture = $upload_dir . $new_filename;
    } else {
      $errors[] = "Invalid file type for profile picture.";
    }
  }

  // If no errors, proceed to insert into database
  if (empty($errors)) {
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);
    $name = $firstname . " " . $lastname;

    $stmt = $conn->prepare("INSERT INTO users (firstname, lastname, name, email, phone, password, profile_picture) VALUES (?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("sssssss", $firstname, $lastname, $name, $email, $phone, $hashed_password, $profile_picture);

    if ($stmt->execute()) {
      $success = "Registration successful! You can now login.";
      header("Location: login.php");
    } else {
      $errors[] = "Registration failed. Please try again.";
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
  <style>
    body {
      background: url('path_to_your_background_image.jpg') no-repeat center center fixed;
      background-size: cover;
      font-family: Arial, sans-serif;
      padding: 20px;
    }

    .signup-container {
      max-width: 1000px;
      margin: 0 auto;
      display: flex;
      flex-wrap: wrap;
      background: rgba(255, 255, 255, 0.8);
      border-radius: 10px;
      box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
      overflow: hidden;
    }

    .left-section {
      background-color: #007bff;
      color: #ffffff;
      padding: 40px;
      flex: 1;
      display: flex;
      flex-direction: column;
      justify-content: center;
      min-height: 400px;
    }

    .left-section h2 {
      font-size: 2rem;
      margin-bottom: 20px;
    }

    .left-section p {
      font-size: 1rem;
      line-height: 1.5;
    }

    .right-section {
      padding: 40px;
      flex: 1;
    }

    .form-control {
      border-radius: 5px;
    }

    .btn-primary {
      background-color: #007bff;
      border-color: #007bff;
      border-radius: 5px;
      transition: background-color 0.3s ease;
    }

    .btn-primary:hover {
      background-color: #0056b3;
    }

    .password-container {
      position: relative;
    }

    .toggle-password {
      position: absolute;
      right: 10px;
      top: 51%;
      /* transform: translateY(-50%); */
      cursor: pointer;
    }

    @media (max-width: 768px) {
      .signup-container {
        flex-direction: column;
      }

      .left-section,
      .right-section {
        flex: 1 100%;
        min-height: auto;
      }
    }
  </style>
  <title>Signup Page</title>
</head>

<body>
  <div class="signup-container">
    <div class="left-section">
      <h2>Welcome to Our Community</h2>
      <p>Connect with fellow members, access exclusive content, and embark on a spiritual journey like no other. Join us and make a difference today!</p>
      <p>Already have an account? <a class="btn btn-info" href="login.php">Login</a></p>
    </div>
    <div class="right-section">
      <h3>Create Your Account</h3>

      <?php
      include '../includes/header.php';
      // Display errors or success messages
      if (!empty($errors)) {
        echo '<div class="alert alert-danger"><ul>';
        foreach ($errors as $error) {
          echo '<li>' . htmlspecialchars($error) . '</li>';
        }
        echo '</ul></div>';
      }
      if (!empty($success)) {
        echo '<div class="alert alert-success">' . htmlspecialchars($success) . '</div>';
      }

      include 'sidebar.php';
      ?>
      <form action="register.php" method="POST" enctype="multipart/form-data">
        <div class="form-group">
          <label for="firstname">First Name*</label>
          <input type="text" class="form-control" id="firstname" name="firstname" required placeholder="John">
        </div>
        <div class="form-group">
          <label for="lastname">Last Name*</label>
          <input type="text" class="form-control" id="lastname" name="lastname" required placeholder="Doe">
        </div>
        <div class="form-group">
          <label for="email">Email*</label>
          <input type="email" class="form-control" id="email" name="email" required placeholder="you@example.com">
        </div>
        <div class="form-group">
          <label for="phone">Phone*</label>
          <input type="tel" class="form-control" id="phone" name="phone" required placeholder="0712345678" maxlength="10" pattern="\d{10}">
        </div>
        <div class="form-group password-container">
          <label for="password">Password*</label>
          <input type="password" class="form-control" id="password" name="password" required placeholder="Create a password">
          <span class="toggle-password" onclick="togglePassword('password')"><i class="fa fa-eye"></i></span>
        </div>
        <div class="form-group password-container">
          <label for="confirm_password">Confirm Password*</label>
          <input type="password" class="form-control" id="confirm_password" name="confirm_password" required placeholder="Confirm your password">
          <span class="toggle-password" onclick="togglePassword('confirm_password')"><i class="fa fa-eye"></i></span>
        </div>
        <div class="form-group">
          <label for="profile_picture">Profile Picture (Optional)</label>
          <input type="file" class="form-control-file" id="profile_picture" name="profile_picture" accept="image/*">
        </div>
        <button type="submit" class="btn btn-primary btn-block">Sign Up</button>
      </form>
    </div>
  </div>

  <script>
    function togglePassword(id) {
      const input = document.getElementById(id);
      const icon = input.nextElementSibling.querySelector('i');
      if (input.type === 'password') {
        input.type = 'text';
        icon.classList.replace('fa-eye', 'fa-eye-slash');
      } else {
        input.type = 'password';
        icon.classList.replace('fa-eye-slash', 'fa-eye');
      }
    }

    // Example strong password validation (client-side)
    passwordInput.addEventListener('input', function() {
      const strongPasswordRegex = /^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[\W_]).{8,}$/;
      if (!strongPasswordRegex.test(passwordInput.value)) {
        passwordInput.setCustomValidity("Password must be at least 8 characters long, contain at least one uppercase letter, one lowercase letter, one number, and one special character.");
      } else {
        passwordInput.setCustomValidity("");
      }
    });

    // Restrict input to only numbers
    document.getElementById('phone').addEventListener('input', function(e) {
      e.target.value = e.target.value.replace(/\D/g, ''); // Remove non-digit characters
    });
  </script>
  <script src="https://kit.fontawesome.com/a076d05399.js" crossorigin="anonymous"></script>
</body>

</html>