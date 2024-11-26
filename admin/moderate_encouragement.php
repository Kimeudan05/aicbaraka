<?php
// admin/moderate_encouragement.php

require_once '../includes/config.php';

// Check if admin is logged in
if (!isset($_SESSION['admin_id']))
{
  header("Location: login.php");
  exit();
}

// Handle approval or deletion
if (isset($_GET['action']) && isset($_GET['id']))
{
  $action = $_GET['action'];
  $enc_id = intval($_GET['id']);

  if ($action == 'approve')
  {
    $stmt = $conn->prepare("UPDATE encouragements SET approved = 1 WHERE id = ?");
    $stmt->bind_param("i", $enc_id);
    if ($stmt->execute())
    {
      $message = "Encouragement approved.";
    } else
    {
      $message = "Failed to approve.";
    }
    $stmt->close();
  } elseif ($action == 'delete')
  {
    // Optionally, fetch to delete related data if needed
    $stmt = $conn->prepare("DELETE FROM encouragements WHERE id = ?");
    $stmt->bind_param("i", $enc_id);
    if ($stmt->execute())
    {
      $message = "Encouragement deleted.";
    } else
    {
      $message = "Failed to delete.";
    }
    $stmt->close();
  }
}

// Fetch pending encouragements
$encouragements = [];
$stmt = $conn->prepare("SELECT e.id, u.name, e.content, e.date_shared FROM encouragements e JOIN users u ON e.user_id = u.id WHERE e.approved = 0");
$stmt->execute();
$result = $stmt->get_result();
while ($row = $result->fetch_assoc())
{
  $encouragements[] = $row;
}
$stmt->close();
?>

<?php include '../includes/header.php'; ?>
<?php include "admin_sidebar.php" ?>

<h2>Moderate Encouragements</h2>

<?php
if (isset($message))
{
  echo '<div class="alert alert-info">' . $message . '</div>';
}
?>

<?php if (count($encouragements) > 0): ?>
  <table class="table table-bordered">
    <thead>
      <tr>
        <th>ID</th>
        <th>Youth</th>
        <th>Content</th>
        <th>Date Shared</th>
        <th>Actions</th>
      </tr>
    </thead>
    <tbody>
      <?php foreach ($encouragements as $enc): ?>
        <tr>
          <td><?php echo htmlspecialchars($enc['id']); ?></td>
          <td><?php echo htmlspecialchars($enc['name']); ?></td>
          <td><?php echo htmlspecialchars($enc['content']); ?></td>
          <td><?php echo htmlspecialchars($enc['date_shared']); ?></td>
          <td>
            <a href="moderate_encouragement.php?action=approve&id=<?php echo $enc['id']; ?>"
              class="btn btn-sm btn-success">Approve</a>
            <a href="moderate_encouragement.php?action=delete&id=<?php echo $enc['id']; ?>" class="btn btn-sm btn-danger"
              onclick="return confirm('Are you sure you want to delete this encouragement?');">Delete</a>
          </td>
        </tr>
      <?php endforeach; ?>
    </tbody>
  </table>
<?php else: ?>
  <p>No pending encouragements.</p>
<?php endif; ?>

<?php include '../includes/footer.php'; ?>