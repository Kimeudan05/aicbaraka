<?php
require_once '../includes/config.php';

if (!isset($_SESSION['admin_id'])) {
    header('Location: login.php');
    exit();
}

$resourceId = isset($_GET['id']) ? (int) $_GET['id'] : 0;
if ($resourceId <= 0) {
    $_SESSION['error_message'] = 'Invalid resource identifier.';
    header('Location: resources.php');
    exit();
}

$stmt = $conn->prepare('SELECT title, type, file_path, description FROM resources WHERE id = ?');
$stmt->bind_param('i', $resourceId);
$stmt->execute();
$stmt->bind_result($title, $type, $filePath, $description);
if (!$stmt->fetch()) {
    $stmt->close();
    $_SESSION['error_message'] = 'Resource not found.';
    header('Location: resources.php');
    exit();
}
$stmt->close();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $newTitle = sanitize_input($_POST['title'] ?? '');
    $newDescription = sanitize_input($_POST['description'] ?? '');
    $newType = sanitize_input($_POST['type'] ?? '');

    if ($newTitle === '' || $newType === '') {
        $_SESSION['error_message'] = 'Title and type are required.';
        header("Location: edit_resource.php?id={$resourceId}");
        exit();
    }

    $updatedFilePath = $filePath;

    if (isset($_FILES['file']) && $_FILES['file']['error'] === UPLOAD_ERR_OK) {
        $filename = $_FILES['file']['name'];
        $fileTmp = $_FILES['file']['tmp_name'];
        $fileExt = strtolower(pathinfo($filename, PATHINFO_EXTENSION));

        $allowed = [];
        if (in_array($newType, ['Bible Verse', 'Announcement'], true)) {
            $allowed = ['pdf', 'doc', 'docx', 'txt'];
        } elseif ($newType === 'Picture') {
            $allowed = ['jpg', 'jpeg', 'png', 'gif'];
        }

        if (!in_array($fileExt, $allowed, true)) {
            $_SESSION['error_message'] = 'Uploaded file type is not permitted for the selected resource type.';
            header("Location: edit_resource.php?id={$resourceId}");
            exit();
        }

        $newFilename = uniqid('resource_', true) . '.' . $fileExt;
        $storageDirectory = dirname(__DIR__) . '/assets/resources/';
        if (!is_dir($storageDirectory)) {
            mkdir($storageDirectory, 0755, true);
        }

        if (!move_uploaded_file($fileTmp, $storageDirectory . $newFilename)) {
            $_SESSION['error_message'] = 'Failed to upload the new file.';
            header("Location: edit_resource.php?id={$resourceId}");
            exit();
        }

        $absoluteOldPath = dirname(__DIR__) . '/' . ltrim($filePath, '/');
        if ($filePath && file_exists($absoluteOldPath)) {
            unlink($absoluteOldPath);
        }

        $updatedFilePath = 'assets/resources/' . $newFilename;
    }

    $stmt = $conn->prepare('UPDATE resources SET title = ?, type = ?, file_path = ?, description = ? WHERE id = ?');
    $stmt->bind_param('ssssi', $newTitle, $newType, $updatedFilePath, $newDescription, $resourceId);
    if ($stmt->execute()) {
        $_SESSION['success_message'] = 'Resource updated successfully.';
    } else {
        $_SESSION['error_message'] = 'Failed to update the resource.';
    }
    $stmt->close();

    header('Location: resources.php');
    exit();
}

$pageTitle = 'Edit Resource';
$bodyClass = 'has-sidebar';
$showSidebarToggle = true;
include '../includes/header.php';
include 'admin_sidebar.php';
?>
<main class="app-main container py-4">
    <h1 class="h4 mb-4">Edit Resource</h1>
    <form method="post" enctype="multipart/form-data" class="form-container bg-white rounded shadow-sm p-4">
        <div class="mb-3">
            <label for="title" class="form-label">Title</label>
            <input type="text" id="title" name="title" class="form-control" value="<?= htmlspecialchars($title); ?>" required>
        </div>
        <div class="mb-3">
            <label for="type" class="form-label">Type</label>
            <select id="type" name="type" class="form-select" required>
                <option value="Bible Verse" <?= $type === 'Bible Verse' ? 'selected' : ''; ?>>Bible Verse</option>
                <option value="Picture" <?= $type === 'Picture' ? 'selected' : ''; ?>>Picture</option>
                <option value="Announcement" <?= $type === 'Announcement' ? 'selected' : ''; ?>>Announcement</option>
            </select>
        </div>
        <div class="mb-3">
            <label for="description" class="form-label">Description</label>
            <textarea id="description" name="description" class="form-control" rows="4" required><?= htmlspecialchars($description); ?></textarea>
        </div>
        <div class="mb-3">
            <label for="file" class="form-label">Replace file (optional)</label>
            <input type="file" id="file" name="file" class="form-control">
            <div class="form-text">Current file: <a href="<?= htmlspecialchars($assetBase . $filePath); ?>" target="_blank" rel="noopener"><?= htmlspecialchars(basename($filePath)); ?></a></div>
        </div>
        <div class="d-flex gap-2">
            <button type="submit" class="btn btn-primary">Update Resource</button>
            <a href="resources.php" class="btn btn-outline-secondary">Cancel</a>
        </div>
    </form>
</main>
<?php include '../includes/footer.php'; ?>
