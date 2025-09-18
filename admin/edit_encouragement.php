<?php
require_once '../includes/config.php';

if (!isset($_SESSION['admin_id'])) {
    header('Location: login.php');
    exit();
}

if (!isset($_GET['id'])) {
    $_SESSION['error_message'] = 'Invalid encouragement ID.';
    header('Location: view_encouragements.php');
    exit();
}

$id = (int) $_GET['id'];
$stmt = $conn->prepare('SELECT e.content, e.user_id, COALESCE(u.name, CONCAT(u.firstname, " ", u.lastname)) AS author_name FROM encouragements e LEFT JOIN users u ON e.user_id = u.id WHERE e.id = ?');
$stmt->bind_param('i', $id);
$stmt->execute();
$stmt->bind_result($content, $userId, $username);
if (!$stmt->fetch()) {
    $stmt->close();
    $_SESSION['error_message'] = 'Encouragement not found.';
    header('Location: view_encouragements.php');
    exit();
}
$stmt->close();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $updatedContent = sanitize_input($_POST['content'] ?? '');

    if ($updatedContent === '') {
        $_SESSION['error_message'] = 'Content cannot be empty.';
        header("Location: edit_encouragement.php?id={$id}");
        exit();
    }

    $stmt = $conn->prepare('UPDATE encouragements SET content = ? WHERE id = ?');
    $stmt->bind_param('si', $updatedContent, $id);
    if ($stmt->execute()) {
        $_SESSION['success_message'] = 'Encouragement updated successfully.';
    } else {
        $_SESSION['error_message'] = 'Failed to update encouragement.';
    }
    $stmt->close();

    header('Location: view_encouragements.php');
    exit();
}

$pageTitle = 'Edit Encouragement';
$bodyClass = 'has-sidebar';
$showSidebarToggle = true;
include '../includes/header.php';
include 'admin_sidebar.php';
?>
<main class="app-main container py-4">
    <h1 class="h4 mb-4">Edit Encouragement</h1>
    <form method="post" class="form-container bg-white rounded shadow-sm p-4">
        <div class="mb-3">
            <label for="content" class="form-label">Encouragement content</label>
            <textarea class="form-control" id="content" name="content" rows="6" required><?= htmlspecialchars($content); ?></textarea>
        </div>
        <div class="mb-3">
            <label class="form-label">Shared by</label>
            <input type="text" class="form-control" value="<?= htmlspecialchars($username ?: $userId); ?>" readonly>
        </div>
        <div class="d-flex gap-2">
            <button type="submit" class="btn btn-primary">Save changes</button>
            <a href="view_encouragements.php" class="btn btn-outline-secondary">Cancel</a>
        </div>
    </form>
</main>
<?php include '../includes/footer.php'; ?>
