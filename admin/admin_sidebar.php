<?php
$adminDisplayName = 'Guest';

if (isset($_SESSION['admin_id'])) {
    $adminId = $_SESSION['admin_id'];
    $stmt = $conn->prepare('SELECT firstname, lastname FROM users WHERE id = ?');
    $stmt->bind_param('i', $adminId);
    $stmt->execute();
    $stmt->bind_result($firstName, $lastName);
    if ($stmt->fetch()) {
        $adminDisplayName = trim($firstName . ' ' . $lastName);
    }
    $stmt->close();
}
?>
<aside id="appSidebar" class="app-sidebar bg-light">
    <div class="text-center mb-4">
        <p class="fw-semibold mb-1">Welcome, <?= htmlspecialchars($adminDisplayName); ?></p>
        <p class="text-muted small mb-0">Admin Navigation</p>
    </div>
    <nav class="nav flex-column gap-2">
        <a class="nav-link" href="dashboard.php"><i class="fas fa-gauge me-2"></i>Dashboard</a>
        <a class="nav-link" href="add_youth.php"><i class="fas fa-user-plus me-2"></i>Add Youth</a>
        <a class="nav-link" href="manage_youth.php"><i class="fas fa-users me-2"></i>Manage Youth</a>
        <a class="nav-link" href="manage_pledges.php"><i class="fas fa-hand-holding-heart me-2"></i>Manage Pledges</a>
        <a class="nav-link" href="upload_resources.php"><i class="fas fa-cloud-upload-alt me-2"></i>Upload Resources</a>
        <a class="nav-link" href="resources.php"><i class="fas fa-folder-open me-2"></i>Resource Library</a>
        <a class="nav-link" href="moderate_encouragement.php"><i class="fas fa-comments me-2"></i>Moderate Encouragements</a>
        <a class="nav-link" href="view_encouragements.php"><i class="fas fa-eye me-2"></i>Approved Encouragements</a>
        <a class="nav-link" href="profile.php"><i class="fas fa-id-badge me-2"></i>My Profile</a>
    </nav>
</aside>
