<?php
require_once '../includes/config.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

$youthId = $_SESSION['user_id'];

$stmt = $conn->prepare('SELECT firstname, lastname, email FROM users WHERE id = ?');
$stmt->bind_param('i', $youthId);
$stmt->execute();
$stmt->bind_result($firstName, $lastName, $email);
$stmt->fetch();
$stmt->close();

$errors = [];
$success = '';
$pledgeType = '';
$pledgeAmount = '';
$dueDate = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $pledgeType = sanitize_input($_POST['pledge_type'] ?? '');
    $pledgeAmount = trim($_POST['pledge_amount'] ?? '');
    $dueDate = trim($_POST['due_date'] ?? '');

    $allowedTypes = ['Youth Kit', 'Christmas Carols', 'Retreat Giving'];
    if (!in_array($pledgeType, $allowedTypes, true)) {
        $errors[] = 'Please select a valid pledge type.';
    }

    if ($pledgeAmount === '' || !is_numeric($pledgeAmount) || (float) $pledgeAmount <= 0) {
        $errors[] = 'Pledge amount must be a positive number.';
    }

    $dueDateValue = null;
    if ($dueDate !== '') {
        $date = DateTime::createFromFormat('Y-m-d', $dueDate);
        if (!$date) {
            $errors[] = 'Please provide a valid due date.';
        } else {
            $dueDateValue = $date->format('Y-m-d');
        }
    }

    if (!$errors) {
        $stmt = $conn->prepare('INSERT INTO youth_pledges (youth_id, pledge_type, pledge_amount, due_date) VALUES (?, ?, ?, ?)');
        $amount = (float) $pledgeAmount;
        $stmt->bind_param('isds', $youthId, $pledgeType, $amount, $dueDateValue);

        if ($stmt->execute()) {
            $stmt->close();
            $_SESSION['success_message'] = 'Thank you! Your pledge has been submitted.';
            header('Location: pledge.php');
            exit();
        }

        $errors[] = 'Failed to submit pledge. Please try again.';
        $stmt->close();
    }
}

if (isset($_SESSION['success_message'])) {
    $success = $_SESSION['success_message'];
    unset($_SESSION['success_message']);
}

$pledges = [];
$stmt = $conn->prepare('SELECT pledge_type, pledge_amount, amount_paid, status, due_date, created_at FROM youth_pledges WHERE youth_id = ? ORDER BY created_at DESC');
$stmt->bind_param('i', $youthId);
$stmt->execute();
$result = $stmt->get_result();
if ($result) {
    $pledges = $result->fetch_all(MYSQLI_ASSOC);
}
$stmt->close();

$pageTitle = 'Make a Pledge';
$bodyClass = 'has-sidebar';
$showSidebarToggle = true;
include '../includes/header.php';
include 'sidebar.php';
?>
<main class="app-main container py-4">
    <h1 class="h4 mb-3">Submit a pledge</h1>
    <p class="text-muted mb-4">Commit to supporting upcoming ministry activities and track your progress below.</p>

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
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <?= htmlspecialchars($success); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>

    <div class="card shadow-sm mb-4">
        <div class="card-body">
            <form method="post" class="row g-3" novalidate>
                <div class="col-md-6">
                    <label for="first_name" class="form-label">First name</label>
                    <input type="text" class="form-control" id="first_name" value="<?= htmlspecialchars($firstName); ?>" disabled>
                </div>
                <div class="col-md-6">
                    <label for="last_name" class="form-label">Last name</label>
                    <input type="text" class="form-control" id="last_name" value="<?= htmlspecialchars($lastName); ?>" disabled>
                </div>
                <div class="col-md-6">
                    <label for="email" class="form-label">Email</label>
                    <input type="email" class="form-control" id="email" value="<?= htmlspecialchars($email); ?>" disabled>
                </div>
                <div class="col-md-6">
                    <label for="pledge_type" class="form-label">Pledge type</label>
                    <select class="form-select" id="pledge_type" name="pledge_type" required>
                        <option value="">Select a pledge type</option>
                        <?php foreach (['Youth Kit', 'Christmas Carols', 'Retreat Giving'] as $type): ?>
                            <option value="<?= htmlspecialchars($type); ?>" <?= $pledgeType === $type ? 'selected' : ''; ?>><?= htmlspecialchars($type); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-6">
                    <label for="pledge_amount" class="form-label">Pledge amount (KES)</label>
                    <input type="number" min="100" step="0.01" class="form-control" id="pledge_amount" name="pledge_amount" value="<?= htmlspecialchars($pledgeAmount); ?>" required>
                </div>
                <div class="col-md-6">
                    <label for="due_date" class="form-label">Due date</label>
                    <input type="date" class="form-control" id="due_date" name="due_date" value="<?= htmlspecialchars($dueDate); ?>">
                </div>
                <div class="col-12">
                    <button type="submit" class="btn btn-primary">Submit pledge</button>
                </div>
            </form>
        </div>
    </div>

    <div class="card shadow-sm">
        <div class="card-body">
            <h2 class="h5 mb-3">Your pledge history</h2>
            <?php if ($pledges): ?>
                <div class="table-responsive">
                    <table class="table table-striped align-middle">
                        <thead class="table-dark">
                            <tr>
                                <th scope="col">Type</th>
                                <th scope="col">Due date</th>
                                <th scope="col">Pledge amount</th>
                                <th scope="col">Amount paid</th>
                                <th scope="col">Status</th>
                                <th scope="col">Created</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($pledges as $pledge): ?>
                                <tr>
                                    <td><?= htmlspecialchars($pledge['pledge_type']); ?></td>
                                    <td><?= $pledge['due_date'] ? htmlspecialchars(date('M j, Y', strtotime($pledge['due_date']))) : '—'; ?></td>
                                    <td><?= htmlspecialchars(number_format((float) $pledge['pledge_amount'], 2)); ?></td>
                                    <td><?= htmlspecialchars(number_format((float) $pledge['amount_paid'], 2)); ?></td>
                                    <td>
                                        <span class="badge <?= $pledge['status'] === 'Paid' ? 'bg-success' : 'bg-warning text-dark'; ?>">
                                            <?= htmlspecialchars($pledge['status']); ?>
                                        </span>
                                    </td>
                                    <td><?= htmlspecialchars(date('M j, Y', strtotime($pledge['created_at']))); ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <p class="text-muted mb-0">You have not submitted any pledges yet.</p>
            <?php endif; ?>
        </div>
    </div>
</main>
<?php include '../includes/footer.php'; ?>
