<?php
// admin/add_youth.php

require_once '../includes/config.php';

// Check if admin is logged in
if (!isset($_SESSION['admin_id'])) {
  header("Location: login.php");
  exit();
}

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

  // Validate password
  if (!validatePassword($password)) {
    $errors[] = "Password must be at least 8 characters long, contain at least one number, and one special character.";
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
      $upload_dir = "../assets/images/profiles/";
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
      $success = "Youth added successfully.";
    } else {
      $errors[] = "Failed to add youth.";
    }

    $stmt->close();
  }
}

// Function to validate password
function validatePassword($password)
{
  if (strlen($password) < 8) {
    return false;
  }
  if (!preg_match('/\d/', $password)) {
    return false;
  }
  if (!preg_match('/[!@#$%^&*()_+\-=\[\]{};":\\|,.<>\/?]/', $password)) {
    return false;
  }
  return true;
}

?>

<?php include '../includes/header.php'; ?>
<?php include "admin_sidebar.php"; ?>

<h2 class="text-center">Add New Youth</h2>

<?php
if (!empty($errors)) {
  echo '<div class="alert alert-danger"><ul>';
  foreach ($errors as $error) {
    echo '<li>' . $error . '</li>';
  }
  echo '</ul></div>';
}

if ($success) {
  echo '<div class="alert alert-success">' . $success . '</div>';
}
?>

<form action="add_youth.php" method="POST" enctype="multipart/form-data" class="form-container bg-body-secondary p-3">
  <div class="mb-3">
    <label for="firstname" class="form-label">First Name*</label>
    <input type="text" class="form-control" id="firstname" name="firstname" required placeholder="John"
      value="<?php echo isset($firstname) ? htmlspecialchars($firstname) : ''; ?>">
  </div>
  <div class="mb-3">
    <label for="lastname" class="form-label">Last Name*</label>
    <input type="text" class="form-control" id="lastname" name="lastname" required placeholder="Doe"
      value="<?php echo isset($lastname) ? htmlspecialchars($lastname) : ''; ?>">
  </div>
  <div class="mb-3">
    <label for="email" class="form-label">Email*</label>
    <input type="email" class="form-control" id="email" name="email" required placeholder="example@mail.com"
      value="<?php echo isset($email) ? htmlspecialchars($email) : ''; ?>">
  </div>
  <div class="mb-3">
    <label for="phone" class="form-label">Phone*</label>
    <input type="text" class="form-control" id="phone" name="phone" maxlength="10" required placeholder="0723456789"
      value="<?php echo isset($phone) ? htmlspecialchars($phone) : ''; ?>">
  </div>
  <div class="mb-3">
    <label for="password" class="form-label">Password*</label>
    <div class="input-group">
      <input type="password" class="form-control" id="password" name="password" required placeholder="Enter password">
      <span class="input-group-text" id="togglePassword" style="cursor: pointer;">
        <i class="fas fa-eye" id="eyeIcon"></i>
      </span>
    </div>
  </div>
  <div class="mb-3">
    <label for="confirm_password" class="form-label">Confirm Password*</label>
    <div class="input-group">
      <input type="password" class="form-control" id="confirm_password" name="confirm_password" required placeholder="Confirm password">
      <span class="input-group-text" id="toggleConfirmPassword" style="cursor: pointer;">
        <i class="fas fa-eye" id="confirmEyeIcon"></i>
      </span>
    </div>
  </div>
  <div class="mb-3">
    <label for="profile_picture" class="form-label">Profile Picture (Optional)</label>
    <input type="file" class="form-control" id="profile_picture" name="profile_picture" accept="image/*">
  </div>
  <button type="submit" class="btn btn-primary">Add Youth</button>
</form>

<script>
  document.getElementById('togglePassword').onclick = function() {
    const passwordField = document.getElementById('password');
    const eyeIcon = document.getElementById('eyeIcon');
    if (passwordField.type === "password") {
      passwordField.type = "text";
      eyeIcon.classList.remove('fa-eye');
      eyeIcon.classList.add('fa-eye-slash');
    } else {
      passwordField.type = "password";
      eyeIcon.classList.remove('fa-eye-slash');
      eyeIcon.classList.add('fa-eye');
    }
  };

  document.getElementById('toggleConfirmPassword').onclick = function() {
    const confirmPasswordField = document.getElementById('confirm_password');
    const confirmEyeIcon = document.getElementById('confirmEyeIcon');
    if (confirmPasswordField.type === "password") {
      confirmPasswordField.type = "text";
      confirmEyeIcon.classList.remove('fa-eye');
      confirmEyeIcon.classList.add('fa-eye-slash');
    } else {
      confirmPasswordField.type = "password";
      confirmEyeIcon.classList.remove('fa-eye-slash');
      confirmEyeIcon.classList.add('fa-eye');
    }
  };
</script>

<style>
  .form-container {
    max-width: 700px;
    /* Limit width of the form */
    margin: auto;
    /* Center the form */
  }

  .input-group-text {
    background-color: #ffffff;
    /* Match with input field */
    border-left: none;
    /* Remove border between input and icon */
  }
</style>

<?php include '../includes/footer.php'; ?>