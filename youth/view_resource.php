<?php
require_once '../includes/config.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

$resource = null;
if (isset($_GET['id']) && ctype_digit($_GET['id'])) {
    $resourceId = (int) $_GET['id'];
    $stmt = $conn->prepare('SELECT title, type, description, file_path, upload_date FROM resources WHERE id = ?');
    $stmt->bind_param('i', $resourceId);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result) {
        $resource = $result->fetch_assoc();
    }
    $stmt->close();
}

if (!$resource) {
    $_SESSION['error_message'] = 'The requested resource could not be found.';
    header('Location: resources.php');
    exit();
}

$fileUrl = $assetBase . $resource['file_path'];
$isImage = strtolower($resource['type']) === 'picture';

$pageTitle = 'View Resource';
$bodyClass = 'has-sidebar';
$showSidebarToggle = true;
include '../includes/header.php';
include 'sidebar.php';
?>
<main class="app-main container py-4">
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="dashboard.php">Dashboard</a></li>
            <li class="breadcrumb-item"><a href="resources.php">Resources</a></li>
            <li class="breadcrumb-item active" aria-current="page"><?= htmlspecialchars($resource['title']); ?></li>
        </ol>
    </nav>

    <article class="card shadow-sm">
        <?php if ($isImage): ?>
            <img src="<?= htmlspecialchars($fileUrl); ?>" class="card-img-top" alt="<?= htmlspecialchars($resource['title']); ?>" style="max-height: 320px; object-fit: cover;">
        <?php else: ?>
            <div class="card-img-top d-flex align-items-center justify-content-center bg-light" style="height: 240px;">
                <i class="fas fa-file-lines fa-4x text-primary"></i>
            </div>
        <?php endif; ?>
        <div class="card-body">
            <h1 class="card-title h4 mb-2"><?= htmlspecialchars($resource['title']); ?></h1>
            <p class="text-muted mb-3">Type: <?= htmlspecialchars($resource['type']); ?> &middot; Uploaded <?= htmlspecialchars(date('M j, Y', strtotime($resource['upload_date']))); ?></p>
            <?php if (!empty($resource['description'])): ?>
                <p class="card-text mb-4"><?= nl2br(htmlspecialchars($resource['description'])); ?></p>
            <?php else: ?>
                <p class="card-text text-muted mb-4">No additional description was provided for this resource.</p>
            <?php endif; ?>
            <div class="d-flex gap-2">
                <a class="btn btn-primary" href="<?= htmlspecialchars($fileUrl); ?>" download><i class="fas fa-download me-2"></i>Download file</a>
                <?php if (!$isImage): ?>
                    <a class="btn btn-outline-secondary" href="<?= htmlspecialchars($fileUrl); ?>" target="_blank" rel="noopener"><i class="fas fa-up-right-from-square me-2"></i>Open in new tab</a>
                <?php endif; ?>
            </div>
        </div>
    </article>
</main>
<?php include '../includes/footer.php'; ?>
