<?php
require_once '../includes/config.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
  header("Location: login.php");
  exit();
}

$errors = [];
$success = "";
$content = ""; // Initialize content variable

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
  $content = sanitize_input($_POST['content']);
  $user_id = $_SESSION['user_id'];

  if (empty($content)) {
    $errors[] = "Encouragement content cannot be empty.";
  }

  if (empty($errors)) {
    // Insert the encouragement into the database
    $stmt = $conn->prepare("INSERT INTO encouragements (user_id, content) VALUES (?, ?)");
    $stmt->bind_param("is", $user_id, $content);

    if ($stmt->execute()) {
      // Set success message
      $success = "Encouragement shared successfully and is pending approval.";
      $content = ""; // Clear content after successful submission

      // Redirect to prevent form resubmission on refresh (Post/Redirect/Get pattern)
      header("Location: encouragement.php");
      exit();
    } else {
      $errors[] = "Failed to share encouragement.";
    }

    $stmt->close();
  }
}

// Fetch approved encouragements
$encouragements = [];
$stmt = $conn->prepare("SELECT e.id, u.name, e.content, e.date_shared FROM encouragements e JOIN users u ON e.user_id = u.id WHERE e.approved = 1 ORDER BY e.date_shared DESC");
$stmt->execute();
$result = $stmt->get_result();
while ($row = $result->fetch_assoc()) {
  $encouragements[] = $row;
}
$stmt->close();
?>

<?php include '../includes/header.php'; ?>
<?php include 'sidebar.php'; ?>

<h2>Share Encouragement</h2>

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

<form action="encouragement.php" method="POST">
  <div class="mb-3">
    <label for="content" class="form-label">Your Encouragement*</label>
    <textarea class="form-control" id="content" name="content" rows="3" placeholder="start typing ...."
      required><?php echo htmlspecialchars($content); ?></textarea>
  </div>
  <button type="submit" class="btn btn-primary">Share</button>
</form>

<hr>

<h3>Encouragements from Others</h3>

<?php if (count($encouragements) > 0): ?>
  <?php foreach ($encouragements as $enc): ?>
    <div class="card mb-3">
      <div class="card-body">
        <h5 class="card-title"><?php echo htmlspecialchars($enc['name']); ?></h5>
        <p class="card-text"><?php echo nl2br(htmlspecialchars($enc['content'])); ?></p>
        <p class="card-text"><small class="text-muted"><?php echo htmlspecialchars($enc['date_shared']); ?></small></p>
      </div>
    </div>
  <?php endforeach; ?>
<?php else: ?>
  <p>No encouragements yet.</p>
<?php endif; ?>

<?php include '../includes/footer.php'; ?>