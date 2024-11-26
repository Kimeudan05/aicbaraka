<?php
// admin/edit_encouragement.php

require_once '../includes/config.php';
// session_start();

// Check if admin is logged in
if (!isset($_SESSION['admin_id']))
{
  header("Location: login.php");
  exit();
}

// Check if the 'id' parameter is set in the URL
if (isset($_GET['id']))
{
  $id = intval($_GET['id']);

  // Fetch the encouragement data and user who shared it
  $stmt = $conn->prepare("
        SELECT e.content, e.user_id, u.name 
        FROM encouragements e 
        LEFT JOIN users u ON e.user_id = u.id 
        WHERE e.id = ?
    ");
  $stmt->bind_param("i", $id);
  $stmt->execute();
  $stmt->bind_result($content, $user_id, $username);
  $stmt->fetch();
  $stmt->close();
} else
{
  $_SESSION['error_message'] = "Invalid encouragement ID.";
  header("Location: view_encouragements.php");
  exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST')
{
  $updated_content = sanitize_input($_POST['content']);

  // Update the content in the database
  $stmt = $conn->prepare("UPDATE encouragements SET content = ? WHERE id = ?");
  $stmt->bind_param("si", $updated_content, $id);

  if ($stmt->execute())
  {
    $_SESSION['success_message'] = "Encouragement updated successfully.";
  } else
  {
    $_SESSION['error_message'] = "Failed to update encouragement.";
  }

  $stmt->close();

  header("Location: view_encouragements.php");
  exit();
}
?>

<?php include '../includes/header.php'; ?>
<?php include "admin_sidebar.php" ?>

<h2>Edit Encouragement</h2>

<form action="edit_encouragement.php?id=<?php echo $id; ?>" method="POST">
  <div class="mb-3">
    <label for="content" class="form-label">Encouragement Content</label>
    <textarea class="form-control" id="content" name="content"
      required><?php echo htmlspecialchars($content); ?></textarea>
  </div>

  <div class="mb-3">
    <label for="shared_by" class="form-label">Shared by</label>
    <input type="text" class="form-control" id="shared_by" name="shared_by"
      value="<?php echo htmlspecialchars($username ? $username : $user_id); ?>" readonly>
  </div>

  <button type="submit" class="btn btn-primary">Save Changes</button>
</form>

<?php include '../includes/footer.php'; ?>