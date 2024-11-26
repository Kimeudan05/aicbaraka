<?php
// admin/upload_resources.php

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
  $title = sanitize_input($_POST['title']);
  $type = sanitize_input($_POST['type']);
  $description = sanitize_input($_POST['description']);

  // Validate inputs
  if (empty($title) || empty($type)) {
    $errors[] = "Title and type are required.";
  }

  // Handle file upload
  if (isset($_FILES['resource_file']) && $_FILES['resource_file']['error'] == 0) {
    // Define allowed file types based on resource type
    $allowed = [];
    if ($type == 'Bible Verse' || $type == 'Announcement') {
      $allowed = ['pdf', 'docx', 'txt'];
    } elseif ($type == 'Picture') {
      $allowed = ['jpg', 'jpeg', 'png', 'gif'];
    }

    $filename = $_FILES['resource_file']['name'];
    $file_tmp = $_FILES['resource_file']['tmp_name'];
    $file_ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));

    if (in_array($file_ext, $allowed)) {
      $new_filename = uniqid() . "." . $file_ext;
      $upload_dir = "../assets/resources/";
      if (!is_dir($upload_dir)) {
        mkdir($upload_dir, 0755, true);
      }
      move_uploaded_file($file_tmp, $upload_dir . $new_filename);
      $file_path = $upload_dir . $new_filename;
    } else {
      $errors[] = "Invalid file type for the selected resource type.";
    }
  } else {
    $errors[] = "Resource file is required.";
  }

  // If no errors, insert into database
  if (empty($errors)) {
    $stmt = $conn->prepare("INSERT INTO resources (title, type, description, file_path) VALUES (?, ?, ?,?)");
    $stmt->bind_param("ssss", $title, $type, $description, $file_path);

    if ($stmt->execute()) {
      $success = "Resource uploaded successfully.";
    } else {
      $errors[] = "Failed to upload resource.";
    }

    $stmt->close();
  }
  header("Location:resources.php");
}
?>

<?php include '../includes/header.php'; ?>
<?php include "admin_sidebar.php" ?>

<h2 class="text-center">Upload Resources</h2>

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

<form action="upload_resources.php" method="POST" enctype="multipart/form-data" class="form-container bg-body-secondary p-3 mx-auto w-50 mb-3">
  <div class="mb-3">
    <label for="title" class="form-label">Resource Title*</label>
    <input type="text" class="form-control" id="title" name="title" placeholder="Enter resource title" required
      value="<?php echo isset($title) ? htmlspecialchars($title) : ''; ?>">
  </div>
  <div class="mb-3">
    <label for="type" class="form-label">Resource Type*</label>
    <select class="form-select" id="type" name="type" required>
      <option value="">Select Type</option>
      <option value="Bible Verse" <?php if (isset($type) && $type == 'Bible Verse')
                                    echo 'selected'; ?>>Bible Verse
      </option>
      <option value="Picture" <?php if (isset($type) && $type == 'Picture')
                                echo 'selected'; ?>>Picture</option>
      <option value="Announcement" <?php if (isset($type) && $type == 'Announcement')
                                      echo 'selected'; ?>>Announcement
      </option>
    </select>
  </div>
  <div class="mb-3">
    <textarea name="description" id="description" class="form-control"
      placeholder="Enter resource description"></textarea>
  </div>

  <div class="mb-3">
    <label for="resource_file" class="form-label">Resource File*</label>
    <input type="file" class="form-control" id="resource_file" name="resource_file" required accept="<?php
                                                                                                      if (isset($type)) {
                                                                                                        if ($type == 'Bible Verse' || $type == 'Announcement') {
                                                                                                          echo '.pdf,.docx,.txt';
                                                                                                        } elseif ($type == 'Picture') {
                                                                                                          echo 'image/*';
                                                                                                        }
                                                                                                      } else {
                                                                                                        echo '*/*';
                                                                                                      }
                                                                                                      ?>">
  </div>
  <button type="submit" class="btn btn-primary ">Upload Resource</button>
</form>

<?php include '../includes/footer.php'; ?>