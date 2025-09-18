<?php
require_once '../includes/config.php';

if (!isset($_SESSION['admin_id'])) {
    header('Location: login.php');
    exit();
}

$errors = [];
$success = '';
$title = $type = $description = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = sanitize_input($_POST['title'] ?? '');
    $type = sanitize_input($_POST['type'] ?? '');
    $description = sanitize_input($_POST['description'] ?? '');

    if ($title === '' || $type === '') {
        $errors[] = 'Title and resource type are required.';
    }

    $filePath = null;
    if (!$errors && isset($_FILES['resource_file']) && $_FILES['resource_file']['error'] === UPLOAD_ERR_OK) {
        $filename = $_FILES['resource_file']['name'];
        $fileTmp = $_FILES['resource_file']['tmp_name'];
        $fileExt = strtolower(pathinfo($filename, PATHINFO_EXTENSION));

        $allowed = [];
        if (in_array($type, ['Bible Verse', 'Announcement'], true)) {
            $allowed = ['pdf', 'doc', 'docx', 'txt'];
        } elseif ($type === 'Picture') {
            $allowed = ['jpg', 'jpeg', 'png', 'gif'];
        }

        if (!in_array($fileExt, $allowed, true)) {
            $errors[] = 'The selected file type is not allowed for this resource.';
        } else {
            $newFilename = uniqid('resource_', true) . '.' . $fileExt;
            $storageDirectory = dirname(__DIR__) . '/assets/resources/';
            if (!is_dir($storageDirectory)) {
                mkdir($storageDirectory, 0755, true);
            }
            if (!move_uploaded_file($fileTmp, $storageDirectory . $newFilename)) {
                $errors[] = 'Failed to store the uploaded file. Please try again.';
            } else {
                $filePath = 'assets/resources/' . $newFilename;
            }
        }
    } else {
        $errors[] = 'Resource file is required.';
    }

    if (!$errors && $filePath) {
        $stmt = $conn->prepare('INSERT INTO resources (title, type, description, file_path, uploaded_by) VALUES (?, ?, ?, ?, ?)');
        $uploadedBy = $_SESSION['admin_id'];
        $stmt->bind_param('ssssi', $title, $type, $description, $filePath, $uploadedBy);

        if ($stmt->execute()) {
            $_SESSION['success_message'] = 'Resource uploaded successfully.';
            header('Location: resources.php');
            exit();
        }

        $errors[] = 'Failed to record the resource in the database.';
        $stmt->close();
    }
}

$pageTitle = 'Upload Resource';
$bodyClass = 'has-sidebar';
$showSidebarToggle = true;
include '../includes/header.php';
include 'admin_sidebar.php';
?>
<main class="app-main container py-4">
    <h1 class="h4 mb-4 text-center">Upload Resource</h1>

    <?php if ($errors): ?>
        <div class="alert alert-danger">
            <ul class="mb-0">
                <?php foreach ($errors as $error): ?>
                    <li><?= htmlspecialchars($error); ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>

    <form method="post" enctype="multipart/form-data" class="form-container bg-white rounded shadow-sm p-4">
        <div class="mb-3">
            <label for="title" class="form-label">Resource title</label>
            <input type="text" class="form-control" id="title" name="title" value="<?= htmlspecialchars($title); ?>" required>
        </div>
        <div class="mb-3">
            <label for="type" class="form-label">Resource type</label>
            <select class="form-select" id="type" name="type" required>
                <option value="" <?= $type === '' ? 'selected' : ''; ?>>Select type</option>
                <option value="Bible Verse" <?= $type === 'Bible Verse' ? 'selected' : ''; ?>>Bible Verse</option>
                <option value="Picture" <?= $type === 'Picture' ? 'selected' : ''; ?>>Picture</option>
                <option value="Announcement" <?= $type === 'Announcement' ? 'selected' : ''; ?>>Announcement</option>
            </select>
        </div>
        <div class="mb-3">
            <label for="description" class="form-label">Description</label>
            <textarea class="form-control" id="description" name="description" rows="3" placeholder="Add a short description (optional)"><?= htmlspecialchars($description); ?></textarea>
        </div>
        <div class="mb-3">
            <label for="resource_file" class="form-label">Resource file</label>
            <input type="file" class="form-control" id="resource_file" name="resource_file" required>
            <div class="form-text">Allowed formats depend on the resource type selected above.</div>
        </div>
        <div class="d-grid">
            <button type="submit" class="btn btn-primary">Upload</button>
        </div>
    </form>
</main>
<?php include '../includes/footer.php'; ?>
