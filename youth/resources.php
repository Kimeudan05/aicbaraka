<?php
require_once '../includes/config.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

$resources = [];
$stmt = $conn->prepare('SELECT id, title, type, description, file_path, upload_date FROM resources ORDER BY upload_date DESC');
$stmt->execute();
$result = $stmt->get_result();
if ($result) {
    $resources = $result->fetch_all(MYSQLI_ASSOC);
}
$stmt->close();

$errorMessage = '';
if (isset($_SESSION['error_message'])) {
    $errorMessage = $_SESSION['error_message'];
    unset($_SESSION['error_message']);
}

$pageTitle = 'Resource Library';
$bodyClass = 'has-sidebar';
$showSidebarToggle = true;
include '../includes/header.php';
include 'sidebar.php';
?>
<main class="app-main container py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h4 mb-1">Youth Resources</h1>
            <p class="text-muted mb-0">Download devotionals, announcements, and other helpful materials.</p>
        </div>
        <form class="d-flex" method="get" role="search">
            <label class="visually-hidden" for="resource-search">Search resources</label>
            <input class="form-control" id="resource-search" name="q" type="search" placeholder="Search by title" value="<?= htmlspecialchars($_GET['q'] ?? ''); ?>" disabled>
        </form>
    </div>

    <?php if ($errorMessage): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <?= htmlspecialchars($errorMessage); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>

    <div class="row g-4">
        <?php if ($resources): ?>
            <?php foreach ($resources as $resource): ?>
                <?php
                $fileUrl = $assetBase . $resource['file_path'];
                $isImage = strtolower($resource['type']) === 'picture';
                ?>
                <div class="col-md-6 col-xl-4">
                    <article class="card h-100 shadow-sm">
                        <?php if ($isImage): ?>
                            <img src="<?= htmlspecialchars($fileUrl); ?>" class="card-img-top" alt="<?= htmlspecialchars($resource['title']); ?>" style="height: 200px; object-fit: cover;">
                        <?php else: ?>
                            <div class="card-img-top d-flex align-items-center justify-content-center bg-light" style="height: 200px;">
                                <i class="fas fa-file-lines fa-4x text-primary"></i>
                            </div>
                        <?php endif; ?>
                        <div class="card-body d-flex flex-column">
                            <h2 class="card-title h5"><?= htmlspecialchars($resource['title']); ?></h2>
                            <p class="text-muted small mb-2">Type: <?= htmlspecialchars($resource['type']); ?> &middot; Uploaded <?= htmlspecialchars(date('M j, Y', strtotime($resource['upload_date']))); ?></p>
                            <?php if (!empty($resource['description'])): ?>
                                <p class="card-text flex-grow-1"><?= nl2br(htmlspecialchars($resource['description'])); ?></p>
                            <?php else: ?>
                                <p class="card-text text-muted flex-grow-1">No description provided.</p>
                            <?php endif; ?>
                            <div class="d-flex gap-2 mt-3">
                                <a class="btn btn-outline-primary" href="view_resource.php?id=<?= $resource['id']; ?>">View details</a>
                                <a class="btn btn-primary" href="<?= htmlspecialchars($fileUrl); ?>" download>Download</a>
                            </div>
                        </div>
                    </article>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <div class="col-12">
                <div class="alert alert-info mb-0">No resources have been uploaded yet. Please check again soon.</div>
            </div>
        <?php endif; ?>
    </div>
</main>
<?php include '../includes/footer.php'; ?>
