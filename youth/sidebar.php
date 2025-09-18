<?php
$youthDisplayName = 'Guest';

if (isset($_SESSION['user_id'])) {
    $youthId = $_SESSION['user_id'];
    $stmt = $conn->prepare('SELECT firstname, lastname FROM users WHERE id = ?');
    $stmt->bind_param('i', $youthId);
    $stmt->execute();
    $stmt->bind_result($firstName, $lastName);
    if ($stmt->fetch()) {
        $youthDisplayName = trim($firstName . ' ' . $lastName);
    }
    $stmt->close();
}
?>
<aside id="appSidebar" class="app-sidebar bg-light">
    <div class="text-center mb-4">
        <p class="fw-semibold mb-1">Welcome, <?= htmlspecialchars($youthDisplayName); ?></p>
        <p class="text-muted small mb-0">Quick Actions</p>
    </div>
    <nav class="nav flex-column gap-2">
        <a class="nav-link" href="dashboard.php"><i class="fas fa-gauge me-2"></i>Dashboard</a>
        <a class="nav-link" href="resources.php"><i class="fas fa-book-open me-2"></i>View Resources</a>
        <a class="nav-link" href="encouragement.php"><i class="fas fa-heart me-2"></i>Share Encouragement</a>
        <a class="nav-link" href="pledge.php"><i class="fas fa-hand-holding-usd me-2"></i>Make a Pledge</a>
        <a class="nav-link" href="profile.php"><i class="fas fa-id-card me-2"></i>My Profile</a>
    </nav>
</aside>
