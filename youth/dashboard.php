<?php
require_once '../includes/config.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

$youthId = $_SESSION['user_id'];

$stmt = $conn->prepare('SELECT firstname, lastname FROM users WHERE id = ?');
$stmt->bind_param('i', $youthId);
$stmt->execute();
$stmt->bind_result($firstName, $lastName);
$stmt->fetch();
$stmt->close();

$fullName = trim(($firstName ?? '') . ' ' . ($lastName ?? '')) ?: 'Youth Member';

$totalPledges = $totalPledgedAmount = $totalPaidAmount = 0.0;
$stmt = $conn->prepare('SELECT COUNT(*), COALESCE(SUM(pledge_amount), 0), COALESCE(SUM(amount_paid), 0) FROM youth_pledges WHERE youth_id = ?');
$stmt->bind_param('i', $youthId);
$stmt->execute();
$stmt->bind_result($totalPledges, $totalPledgedAmount, $totalPaidAmount);
$stmt->fetch();
$stmt->close();

$resources = [];
$result = $conn->query('SELECT id, title, type, file_path, upload_date FROM resources ORDER BY upload_date DESC LIMIT 3');
if ($result) {
    $resources = $result->fetch_all(MYSQLI_ASSOC);
}

$recentEncouragements = [];
$stmt = $conn->prepare('SELECT content, date_shared FROM encouragements WHERE user_id = ? ORDER BY date_shared DESC LIMIT 3');
$stmt->bind_param('i', $youthId);
$stmt->execute();
$result = $stmt->get_result();
if ($result) {
    $recentEncouragements = $result->fetch_all(MYSQLI_ASSOC);
}
$stmt->close();

$pageTitle = 'Youth Dashboard';
$bodyClass = 'has-sidebar';
$showSidebarToggle = true;
include '../includes/header.php';
include 'sidebar.php';
?>
<main class="app-main container py-4">
    <div class="row g-4">
        <div class="col-12">
            <div class="card bg-gradient text-white">
                <div class="card-body d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center">
                    <div>
                        <h1 class="h4 mb-2">Welcome back, <?= htmlspecialchars($fullName); ?>!</h1>
                        <p class="mb-0">Here’s a quick glance at what’s happening across the youth ministry.</p>
                    </div>
                    <div class="d-flex gap-2 mt-3 mt-md-0">
                        <a class="btn btn-outline-light" href="resources.php"><i class="fas fa-book me-2"></i>Explore Resources</a>
                        <a class="btn btn-light" href="pledge.php"><i class="fas fa-hand-holding-heart me-2"></i>Make a Pledge</a>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card h-100 shadow-sm">
                <div class="card-body">
                    <h2 class="h6 text-uppercase text-muted">Total pledges</h2>
                    <p class="display-6 fw-bold mb-1"><?= htmlspecialchars((string) $totalPledges); ?></p>
                    <p class="text-muted mb-0">Pledges you have submitted.</p>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card h-100 shadow-sm">
                <div class="card-body">
                    <h2 class="h6 text-uppercase text-muted">Amount pledged</h2>
                    <p class="display-6 fw-bold mb-1">KES <?= htmlspecialchars(number_format((float) $totalPledgedAmount, 2)); ?></p>
                    <p class="text-muted mb-0">Your total giving commitments.</p>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card h-100 shadow-sm">
                <div class="card-body">
                    <h2 class="h6 text-uppercase text-muted">Amount paid</h2>
                    <p class="display-6 fw-bold mb-1">KES <?= htmlspecialchars(number_format((float) $totalPaidAmount, 2)); ?></p>
                    <p class="text-muted mb-0">Received towards your pledges.</p>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-4 mt-2">
        <div class="col-lg-7">
            <div class="card h-100 shadow-sm">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h2 class="h5 mb-0">Latest resources</h2>
                        <a class="btn btn-sm btn-outline-primary" href="resources.php">View all</a>
                    </div>
                    <?php if ($resources): ?>
                        <div class="row g-3">
                            <?php foreach ($resources as $resource): ?>
                                <?php $fileUrl = $assetBase . $resource['file_path']; ?>
                                <div class="col-md-6">
                                    <article class="card h-100 border-0 bg-light">
                                        <?php if (strtolower($resource['type']) === 'picture'): ?>
                                            <img src="<?= htmlspecialchars($fileUrl); ?>" class="card-img-top" alt="<?= htmlspecialchars($resource['title']); ?>" style="height: 160px; object-fit: cover;">
                                        <?php else: ?>
                                            <div class="card-img-top d-flex align-items-center justify-content-center text-primary" style="height: 160px; background: rgba(13,110,253,0.1);">
                                                <i class="fas fa-file-alt fa-3x"></i>
                                            </div>
                                        <?php endif; ?>
                                        <div class="card-body">
                                            <h3 class="card-title h6 mb-1"><?= htmlspecialchars($resource['title']); ?></h3>
                                            <p class="card-text text-muted mb-2"><?= htmlspecialchars($resource['type']); ?> &middot; <?= htmlspecialchars(date('M j, Y', strtotime($resource['upload_date']))); ?></p>
                                            <a class="stretched-link" href="view_resource.php?id=<?= $resource['id']; ?>">View details</a>
                                        </div>
                                    </article>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php else: ?>
                        <p class="text-muted mb-0">No resources available yet. Check back soon!</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        <div class="col-lg-5">
            <div class="card h-100 shadow-sm">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h2 class="h5 mb-0">Your encouragements</h2>
                        <a class="btn btn-sm btn-outline-secondary" href="encouragement.php">Share more</a>
                    </div>
                    <?php if ($recentEncouragements): ?>
                        <ul class="list-group list-group-flush">
                            <?php foreach ($recentEncouragements as $encouragement): ?>
                                <li class="list-group-item">
                                    <p class="mb-1"><?= nl2br(htmlspecialchars($encouragement['content'])); ?></p>
                                    <small class="text-muted">Shared on <?= htmlspecialchars(date('M j, Y', strtotime($encouragement['date_shared']))); ?></small>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    <?php else: ?>
                        <p class="text-muted mb-0">You haven’t shared any encouragements yet.</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</main>
<?php include '../includes/footer.php'; ?>
