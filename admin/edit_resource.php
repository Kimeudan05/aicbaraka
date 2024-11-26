<?php
// admin/edit_resource.php

require_once '../includes/config.php';

// Check if the admin is logged in
if (!isset($_SESSION['admin_id'])) {
  header("Location: login.php");
  exit();
}

// Get the resource ID from the query parameter
$resource_id = intval($_GET['id']);

// Fetch the current resource details from the database
$stmt = $conn->prepare("SELECT title, type, file_path, description FROM resources WHERE id = ?");
$stmt->bind_param("i", $resource_id);
$stmt->execute();
$stmt->bind_result($title, $type, $file_path, $description);
$stmt->fetch();
$stmt->close();

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $new_title = trim($_POST['title']);
  $new_description = trim($_POST['description']);
  $new_type = $_POST['type']; // Get the new type from the form

  // Default to the existing file path
  $new_file_path = $file_path;

  // Check if a new file was uploaded
  if (isset($_FILES['file']) && $_FILES['file']['error'] === UPLOAD_ERR_OK) {
    $file = $_FILES['file'];
    $upload_dir = '../assets/resources/';
    $new_file_name = basename($file['name']);
    $new_file_path = $upload_dir . $new_file_name;

    // Get the MIME type of the new file
    $new_type = mime_content_type($file['tmp_name']);

    // Move the uploaded file to the resources directory
    if (move_uploaded_file($file['tmp_name'], $new_file_path)) {
      // Delete the old file if a new one is successfully uploaded
      if (file_exists($file_path) && $file_path !== $new_file_path) {
        unlink($file_path); // Delete the old file
      }
    } else {
      $_SESSION['error_message'] = "Failed to upload the new file.";
      header("Location: resources.php");
      exit();
    }
  }

  // Update the resource details in the database
  $stmt = $conn->prepare("UPDATE resources SET title = ?, type = ?, file_path = ?, description = ? WHERE id = ?");
  $stmt->bind_param("ssssi", $new_title, $new_type, $new_file_path, $new_description, $resource_id);

  if ($stmt->execute()) {
    $_SESSION['success_message'] = "Resource updated successfully.";
  } else {
    $_SESSION['error_message'] = "Failed to update the resource.";
  }

  $stmt->close();
  header("Location: resources.php");
  exit();
}

include '../includes/header.php';
include "admin_sidebar.php";
?>

<div class="container mt-4 w-75">
  <h2>Edit Resource</h2>

  <form action="edit_resource.php?id=<?php echo $resource_id; ?>" method="post" enctype="multipart/form-data">
    <div class="form-group">
      <label for="title">Title</label>
      <input type="text" id="title" name="title" class="form-control" value="<?php echo htmlspecialchars($title); ?>" required>
    </div>

    <div class="form-group mb-2">
      <label for="file">Replace File (Optional)</label>
      <input type="file" id="file" name="file" class="form-control-file">
      <p>Current file: <a href="<?php echo htmlspecialchars($file_path); ?>" target="_blank"><?php echo htmlspecialchars(basename($file_path)); ?></a></p>
    </div>

    <div class="form-group mb-2">
      <label for="type" class="mb-2 form-label">Type</label>
      <select id="type" name="type" class="form-control form-select" required>
        <option value="Bible Verse" <?php if ($type == 'Bible Verse') echo 'selected'; ?>>Bible Verse</option>
        <option value="Picture" <?php if ($type == 'Picture') echo 'selected'; ?>>Picture</option>
        <option value="Announcement" <?php if ($type == 'Announcement') echo 'selected'; ?>>Announcement</option>
      </select>
    </div>

    <div class="form-group mb-4">
      <label for="description">Description</label>
      <textarea id="description" name="description" class="form-control" required><?php echo htmlspecialchars($description); ?></textarea>
    </div>

    <button type="submit" class="btn btn-primary">Update Resource</button>
    <a href="resources.php" class="btn btn-secondary">Cancel</a>
  </form>
</div>

<?php include '../includes/footer.php'; ?>