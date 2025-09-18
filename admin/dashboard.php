<?php
require_once '../includes/config.php';

if (!isset($_SESSION['admin_id'])) {
    header('Location: login.php');
    exit();
}

$youthCount = $resourceCount = $encouragementCount = 0;

$stmt = $conn->prepare("SELECT COUNT(*) FROM users WHERE role = 'youth'");
$stmt->execute();
$stmt->bind_result($youthCount);
$stmt->fetch();
$stmt->close();

$stmt = $conn->prepare('SELECT COUNT(*) FROM resources');
$stmt->execute();
$stmt->bind_result($resourceCount);
$stmt->fetch();
$stmt->close();

$stmt = $conn->prepare('SELECT COUNT(*) FROM encouragements WHERE approved = 0');
$stmt->execute();
$stmt->bind_result($encouragementCount);
$stmt->fetch();
$stmt->close();

$pageTitle = 'Admin Dashboard';
$bodyClass = 'has-sidebar';
$showSidebarToggle = true;
include '../includes/header.php';
include 'admin_sidebar.php';
?>
<main class="app-main container py-4">
    <h1 class="h3 mb-4">Dashboard</h1>
    <div class="row g-4">
        <div class="col-md-4">
            <div class="card text-bg-primary h-100">
                <div class="card-body">
                    <h2 class="card-title h5">Total Youths</h2>
                    <p class="display-6 fw-bold text-center mb-0"><?= htmlspecialchars($youthCount); ?></p>
                </div>
                <div class="card-footer text-center">
                    <a class="text-white text-decoration-none" href="manage_youth.php">View all youths</a>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card text-bg-success h-100">
                <div class="card-body">
                    <h2 class="card-title h5">Total Resources</h2>
                    <p class="display-6 fw-bold text-center mb-0"><?= htmlspecialchars($resourceCount); ?></p>
                </div>
                <div class="card-footer text-center">
                    <a class="text-white text-decoration-none" href="resources.php">Manage resources</a>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card text-bg-warning h-100">
                <div class="card-body">
                    <h2 class="card-title h5">Pending Encouragements</h2>
                    <p class="display-6 fw-bold text-center mb-0"><?= htmlspecialchars($encouragementCount); ?></p>
                </div>
                <div class="card-footer text-center">
                    <a class="text-white text-decoration-none" href="moderate_encouragement.php">Review submissions</a>
                </div>
            </div>
        </div>
    </div>
</main>
<?php include '../includes/footer.php'; ?>
