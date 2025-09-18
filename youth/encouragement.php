<?php
require_once '../includes/config.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

$errors = [];
$success = '';
$content = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $content = sanitize_input($_POST['content'] ?? '');
    $userId = $_SESSION['user_id'];

    if ($content === '') {
        $errors[] = 'Encouragement content cannot be empty.';
    }

    if (!$errors) {
        $stmt = $conn->prepare('INSERT INTO encouragements (user_id, content) VALUES (?, ?)');
        $stmt->bind_param('is', $userId, $content);
        if ($stmt->execute()) {
            $stmt->close();
            $_SESSION['success_message'] = 'Encouragement shared successfully and is pending approval.';
            header('Location: encouragement.php');
            exit();
        }
        $errors[] = 'Failed to share encouragement.';
        $stmt->close();
    }
}

$encouragements = [];
$stmt = $conn->prepare('SELECT e.id, COALESCE(u.name, CONCAT(u.firstname, " ", u.lastname)) AS name, e.content, e.date_shared FROM encouragements e JOIN users u ON e.user_id = u.id WHERE e.approved = 1 ORDER BY e.date_shared DESC');
$stmt->execute();
$result = $stmt->get_result();
if ($result) {
    $encouragements = $result->fetch_all(MYSQLI_ASSOC);
}
$stmt->close();

if (isset($_SESSION['success_message'])) {
    $success = $_SESSION['success_message'];
    unset($_SESSION['success_message']);
}

$pageTitle = 'Share Encouragement';
$bodyClass = 'has-sidebar';
$showSidebarToggle = true;
include '../includes/header.php';
include 'sidebar.php';
?>
<main class="app-main container py-4">
    <h1 class="h4 mb-4">Share Encouragement</h1>

    <?php if ($errors): ?>
        <div class="alert alert-danger">
            <ul class="mb-0">
                <?php foreach ($errors as $error): ?>
                    <li><?= htmlspecialchars($error); ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>

    <?php if ($success): ?>
        <div class="alert alert-success"><?= htmlspecialchars($success); ?></div>
    <?php endif; ?>

    <form method="post" class="bg-white rounded shadow-sm p-4 mb-4">
        <div class="mb-3">
            <label for="content" class="form-label">Your encouragement</label>
            <textarea class="form-control" id="content" name="content" rows="4" placeholder="Share something uplifting" required><?= htmlspecialchars($content); ?></textarea>
        </div>
        <button type="submit" class="btn btn-primary">Share</button>
    </form>

    <h2 class="h5 mb-3">Encouragements from the community</h2>
    <?php if ($encouragements): ?>
        <?php foreach ($encouragements as $enc): ?>
            <article class="card mb-3">
                <div class="card-body">
                    <h3 class="card-title h6 mb-2"><?= htmlspecialchars($enc['name']); ?></h3>
                    <p class="card-text mb-2"><?= nl2br(htmlspecialchars($enc['content'])); ?></p>
                    <p class="card-text"><small class="text-muted">Shared on <?= htmlspecialchars($enc['date_shared']); ?></small></p>
                </div>
            </article>
        <?php endforeach; ?>
    <?php else: ?>
        <p class="text-muted">No encouragements have been approved yet.</p>
    <?php endif; ?>
</main>
<?php include '../includes/footer.php'; ?>
