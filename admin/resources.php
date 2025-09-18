<?php
require_once '../includes/config.php';

if (!isset($_SESSION['admin_id'])) {
    header('Location: login.php');
    exit();
}

$result = $conn->query('SELECT id, title, type, file_path, description, upload_date FROM resources ORDER BY upload_date DESC');
$resources = $result ? $result->fetch_all(MYSQLI_ASSOC) : [];

$pageTitle = 'Resource Library';
$bodyClass = 'has-sidebar';
$showSidebarToggle = true;
include '../includes/header.php';
include 'admin_sidebar.php';
?>
<main class="app-main container py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h4 mb-1">Manage Resources</h1>
            <p class="text-muted mb-0">Review, edit, or remove uploaded materials.</p>
        </div>
        <a class="btn btn-primary" href="upload_resources.php"><i class="fas fa-plus me-1"></i>Upload Resource</a>
    </div>

    <?php if (isset($_SESSION['success_message'])): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <?= htmlspecialchars($_SESSION['success_message']); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
        <?php unset($_SESSION['success_message']); ?>
    <?php endif; ?>

    <?php if (isset($_SESSION['error_message'])): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <?= htmlspecialchars($_SESSION['error_message']); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
        <?php unset($_SESSION['error_message']); ?>
    <?php endif; ?>

    <div class="table-responsive">
        <table class="table table-striped table-hover align-middle">
            <thead class="table-dark">
                <tr>
                    <th scope="col">Title</th>
                    <th scope="col">Type</th>
                    <th scope="col">Preview</th>
                    <th scope="col">Description</th>
                    <th scope="col">Uploaded</th>
                    <th scope="col" class="text-center">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($resources): ?>
                    <?php foreach ($resources as $resource): ?>
                        <tr>
                            <td><?= htmlspecialchars($resource['title']); ?></td>
                            <td><?= htmlspecialchars($resource['type']); ?></td>
                            <td>
                                <?php $fileUrl = $assetBase . $resource['file_path']; ?>
                                <?php if (str_starts_with($resource['type'], 'Picture') || preg_match('/image\//', $resource['type'])): ?>
                                    <img src="<?= htmlspecialchars($fileUrl); ?>" alt="Resource preview" width="56" height="56" class="rounded" style="width:56px;height:56px;object-fit:cover;">
                                <?php else: ?>
                                    <a href="<?= htmlspecialchars($fileUrl); ?>" target="_blank" rel="noopener">View file</a>
                                <?php endif; ?>
                            </td>
                            <td><?= nl2br(htmlspecialchars($resource['description'] ?? '')); ?></td>
                            <td><?= htmlspecialchars($resource['upload_date']); ?></td>
                            <td class="text-center">
                                <div class="btn-group btn-group-sm" role="group">
                                    <a class="btn btn-outline-secondary" href="edit_resource.php?id=<?= $resource['id']; ?>">
                                        <i class="fas fa-pen"></i> Edit
                                    </a>
                                    <button class="btn btn-outline-danger" type="button" onclick="confirmDelete(<?= $resource['id']; ?>)">
                                        <i class="fas fa-trash-alt"></i> Delete
                                    </button>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="6" class="text-center">No resources uploaded yet.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</main>
<script>
    function confirmDelete(resourceId) {
        if (confirm('Are you sure you want to delete this resource? This action cannot be undone.')) {
            window.location.href = 'delete_resource.php?id=' + resourceId;
        }
    }
</script>
<?php include '../includes/footer.php'; ?>
