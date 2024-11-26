<?php
// profile.php

require_once '../includes/config.php';

// Check if user is logged in
if (!isset($_SESSION['user_id']))
{
  header("Location: login.php");
  exit();
}

$user_id = $_SESSION['user_id'];
$errors = [];
$success = "";

// Fetch user details
$stmt = $conn->prepare("SELECT firstname, lastname, email, phone, profile_picture FROM users WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$stmt->bind_result($firstname, $lastname, $email, $phone, $profile_picture);
$stmt->fetch();
$stmt->close();

if ($_SERVER["REQUEST_METHOD"] == "POST")
{
  // Sanitize inputs
  $phone = sanitize_input($_POST['phone']);

  // Handle profile picture upload
  if (isset($_FILES['profile_picture']) && $_FILES['profile_picture']['error'] == 0)
  {
    $allowed = ['jpg', 'jpeg', 'png', 'gif'];
    $filename = $_FILES['profile_picture']['name'];
    $file_tmp = $_FILES['profile_picture']['tmp_name'];
    $file_ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));

    if (in_array($file_ext, $allowed))
    {
      $new_filename = uniqid() . "." . $file_ext;
      $upload_dir = "assets/images/profiles/";
      if (!is_dir($upload_dir))
      {
        mkdir($upload_dir, 0755, true);
      }
      move_uploaded_file($file_tmp, $upload_dir . $new_filename);
      // Delete old profile picture if exists
      if ($profile_picture && file_exists($profile_picture))
      {
        unlink($profile_picture);
      }
      $profile_picture = $upload_dir . $new_filename;
    } else
    {
      $errors[] = "Invalid file type for profile picture.";
    }
  }

  if (empty($errors))
  {
    $stmt = $conn->prepare("UPDATE users SET phone = ?, profile_picture = ? WHERE id = ?");
    $stmt->bind_param("ssi", $phone, $profile_picture, $user_id);

    if ($stmt->execute())
    {
      $success = "Profile updated successfully.";
    } else
    {
      $errors[] = "Failed to update profile.";
    }

    $stmt->close();
  }
}
?>

<?php include '../includes/header.php'; ?>
<?php include 'sidebar.php' ?>

<h2>My Profile</h2>

<?php
if (!empty($errors))
{
  echo '<div class="alert alert-danger"><ul>';
  foreach ($errors as $error)
  {
    echo '<li>' . $error . '</li>';
  }
  echo '</ul></div>';
}

if ($success)
{
  echo '<div class="alert alert-success">' . $success . '</div>';
}
?>

<div class="row">
  <div class="col-md-4">
    <?php if ($profile_picture): ?>
      <img src="<?php echo htmlspecialchars($profile_picture); ?>" alt="Profile Picture" class="img-fluid rounded">
    <?php else: ?>
      <img src="../assets/images/default_profile.png" alt="Default Profile Picture" class="img-fluid rounded">
    <?php endif; ?>
  </div>
  <div class="col-md-8">
    <form action="profile.php" method="POST" enctype="multipart/form-data">
      <div class="mb-3">
        <label class="form-label">First Name</label>
        <input type="text" class="form-control" value="<?php echo htmlspecialchars($firstname); ?>" disabled>
      </div>
      <div class="mb-3">
        <label class="form-label">Last Name</label>
        <input type="text" class="form-control" value="<?php echo htmlspecialchars($lastname); ?>" disabled>
      </div>
      <div class="mb-3">
        <label class="form-label">Email</label>
        <input type="email" class="form-control" value="<?php echo htmlspecialchars($email); ?>" disabled>
      </div>
      <div class="mb-3">
        <label for="phone" class="form-label">Phone*</label>
        <input type="text" class="form-control" id="phone" name="phone" required
          value="<?php echo htmlspecialchars($phone); ?>">
      </div>
      <div class="mb-3">
        <label for="profile_picture" class="form-label">Change Profile Picture</label>
        <input type="file" class="form-control" id="profile_picture" name="profile_picture" accept="image/*">
      </div>
      <button type="submit" class="btn btn-primary">Update Profile</button>
    </form>
  </div>
</div>

<?php include '../includes/footer.php'; ?>