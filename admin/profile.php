<?php
// admin/profile.php

require_once '../includes/config.php';
// session_start();

// Check if admin is logged in
// if (!isset($_SESSION['admin_id']))
// {
//   header("Location: login.php");
//   exit();
// }

$admin_id = $_SESSION['admin_id'];
$success_message = "";
$error_message = "";

// Fetch admin details
$stmt = $conn->prepare("SELECT name, profile_picture FROM users WHERE id = ?");
$stmt->bind_param("i", $admin_id);
$stmt->execute();
$stmt->bind_result($username, $profile_picture);
$stmt->fetch();
$stmt->close();

// Handle profile picture upload
if (isset($_POST['upload_picture']))
{
  $target_dir = "../assets/profile_pictures/";
  $target_file = $target_dir . basename($_FILES["profile_picture"]["name"]);
  $imageFileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));

  // Check if the file is an image
  $check = getimagesize($_FILES["profile_picture"]["tmp_name"]);
  if ($check !== false)
  {
    // Save the file and update the database
    if (move_uploaded_file($_FILES["profile_picture"]["tmp_name"], $target_file))
    {
      $stmt = $conn->prepare("UPDATE users SET profile_picture = ? WHERE id = ?");
      $stmt->bind_param("si", $_FILES["profile_picture"]["name"], $admin_id);
      $stmt->execute();
      $stmt->close();
      $success_message = "Profile picture uploaded successfully.";
    } else
    {
      $error_message = "Failed to upload profile picture.";
    }
  } else
  {
    $error_message = "File is not an image.";
  }
}

// Handle password update
if (isset($_POST['update_password']))
{
  $new_password = $_POST['new_password'];
  $confirm_password = $_POST['confirm_password'];

  if ($new_password === $confirm_password)
  {
    // Hash the password and update
    $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
    $stmt = $conn->prepare("UPDATE users SET password = ? WHERE id = ?");
    $stmt->bind_param("si", $hashed_password, $admin_id);
    if ($stmt->execute())
    {
      $success_message = "Password updated successfully.";
    } else
    {
      $error_message = "Failed to update password.";
    }
    $stmt->close();
  } else
  {
    $error_message = "Passwords do not match.";
  }
}

include '../includes/header.php';
include "admin_sidebar.php"
  ?>

<div class="row">
  <div class="col-md-3">
    <!-- Sidebar with profile picture -->
    <aside class="text-center">
      <h4>Admin Profile</h4>
      <?php if ($profile_picture): ?>
        <img src="../assets/profile_pictures/<?php echo $profile_picture; ?>" class="img-thumbnail" alt="Profile Picture"
          width="150" height="150">
      <?php else: ?>
        <img src="../assets/default_profile.png" class="img-thumbnail" alt="Default Profile Picture" width="150"
          height="150">
      <?php endif; ?>
      <p><?php echo htmlspecialchars($username); ?></p>
    </aside>
  </div>

  <div class="col-md-9">
    <h2>Update Profile</h2>

    <?php if ($success_message): ?>
      <div class="alert alert-success"><?php echo $success_message; ?></div>
    <?php endif; ?>

    <?php if ($error_message): ?>
      <div class="alert alert-danger"><?php echo $error_message; ?></div>
    <?php endif; ?>

    <!-- Upload profile picture form -->
    <form action="profile.php" method="POST" enctype="multipart/form-data">
      <div class="mb-3">
        <label for="profile_picture" class="form-label">Upload Profile Picture</label>
        <input type="file" class="form-control" id="profile_picture" name="profile_picture" required>
      </div>
      <button type="submit" name="upload_picture" class="btn btn-primary">Upload Picture</button>
    </form>

    <hr>

    <!-- Update password form -->
    <form action="profile.php" method="POST">
      <div class="mb-3">
        <label for="new_password" class="form-label">New Password</label>
        <input type="password" class="form-control" id="new_password" name="new_password" required>
      </div>
      <div class="mb-3">
        <label for="confirm_password" class="form-label">Confirm Password</label>
        <input type="password" class="form-control" id="confirm_password" name="confirm_password" required>
      </div>
      <button type="submit" name="update_password" class="btn btn-success">Update Password</button>
    </form>
  </div>
</div>

<?php include '../includes/footer.php'; ?>