<?php
require_once '../includes/config.php';

if (!isset($_SESSION['admin_id'])) {
    header('Location: login.php');
    exit();
}

$filters = [
    'pledge_type' => '',
    'status' => '',
    'due_date' => ''
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['filter'])) {
        $filters['pledge_type'] = sanitize_input($_POST['pledge_type'] ?? '');
        $filters['status'] = sanitize_input($_POST['status'] ?? '');
        $filters['due_date'] = sanitize_input($_POST['due_date'] ?? '');
    }

    if (isset($_POST['update'])) {
        $pledgeId = (int) ($_POST['id'] ?? 0);
        $additionalPayment = (float) ($_POST['amount_paid'] ?? 0);
        $status = sanitize_input($_POST['status'] ?? 'Unpaid');

        $stmt = $conn->prepare('SELECT pledge_amount, amount_paid FROM youth_pledges WHERE id = ?');
        $stmt->bind_param('i', $pledgeId);
        $stmt->execute();
        $stmt->bind_result($pledgeAmount, $alreadyPaid);
        if ($stmt->fetch()) {
            $stmt->close();

            $currentPending = max($pledgeAmount - $alreadyPaid, 0);
            if ($additionalPayment > $currentPending) {
                $error_message = 'Amount paid cannot exceed the current pending amount.';
            } else {
                $newAmountPaid = $alreadyPaid + $additionalPayment;
                $calculatedStatus = $newAmountPaid >= $pledgeAmount ? 'Paid' : 'Unpaid';
                $finalStatus = in_array($status, ['Paid', 'Unpaid'], true) ? $status : $calculatedStatus;

                if ($finalStatus === 'Paid' && $newAmountPaid < $pledgeAmount) {
                    $newAmountPaid = $pledgeAmount;
                }

                $update = $conn->prepare('UPDATE youth_pledges SET amount_paid = ?, status = ? WHERE id = ?');
                $update->bind_param('dsi', $newAmountPaid, $finalStatus, $pledgeId);
                $update->execute();
                $update->close();
            }
        } else {
            $stmt->close();
        }
    }
}

$query = "SELECT yp.id, u.firstname, u.lastname, yp.pledge_type, yp.status, yp.due_date, yp.pledge_amount, yp.amount_paid " .
         "FROM youth_pledges yp JOIN users u ON yp.youth_id = u.id WHERE 1=1";
$params = [];
$types = '';

if ($filters['pledge_type'] !== '') {
    $query .= ' AND yp.pledge_type = ?';
    $types .= 's';
    $params[] = $filters['pledge_type'];
}

if ($filters['status'] !== '') {
    $query .= ' AND yp.status = ?';
    $types .= 's';
    $params[] = $filters['status'];
}

if ($filters['due_date'] !== '') {
    $query .= ' AND yp.due_date = ?';
    $types .= 's';
    $params[] = $filters['due_date'];
}

$query .= ' ORDER BY yp.due_date IS NULL, yp.due_date ASC';
$stmt = $conn->prepare($query);
if ($params) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$result = $stmt->get_result();
$pledges = $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
$stmt->close();

$subtotalPledgeAmount = 0;
$subtotalAmountPaid = 0;
$pendingAmount = 0;

$pageTitle = 'Manage Youth Pledges';
$bodyClass = 'has-sidebar';
$showSidebarToggle = true;
$extraScripts = ['https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.9.3/html2pdf.bundle.min.js'];
include '../includes/header.php';
include 'admin_sidebar.php';
?>
<main class="app-main container py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h4 mb-1">Youth Pledge Reports</h1>
            <p class="text-muted mb-0">Filter, update, and export the latest pledge records.</p>
        </div>
        <div class="d-flex gap-2">
            <button type="button" class="btn btn-outline-secondary" onclick="window.print()"><i class="fas fa-print me-1"></i>Print</button>
            <button type="button" class="btn btn-primary" id="downloadPDF"><i class="fas fa-file-pdf me-1"></i>Download PDF</button>
        </div>
    </div>

    <?php if (isset($error_message)): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <?= htmlspecialchars($error_message); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>

    <form method="post" class="row g-3 align-items-end bg-light border rounded p-3 mb-4">
        <div class="col-sm-4">
            <label for="pledge_type" class="form-label">Pledge type</label>
            <select id="pledge_type" name="pledge_type" class="form-select">
                <option value="" <?= $filters['pledge_type'] === '' ? 'selected' : ''; ?>>All pledge types</option>
                <option value="Youth Kit" <?= $filters['pledge_type'] === 'Youth Kit' ? 'selected' : ''; ?>>Youth Kit</option>
                <option value="Christmas Carols" <?= $filters['pledge_type'] === 'Christmas Carols' ? 'selected' : ''; ?>>Christmas Carols</option>
                <option value="Retreat Giving" <?= $filters['pledge_type'] === 'Retreat Giving' ? 'selected' : ''; ?>>Retreat Giving</option>
            </select>
        </div>
        <div class="col-sm-4">
            <label for="status" class="form-label">Status</label>
            <select id="status" name="status" class="form-select">
                <option value="" <?= $filters['status'] === '' ? 'selected' : ''; ?>>All statuses</option>
                <option value="Paid" <?= $filters['status'] === 'Paid' ? 'selected' : ''; ?>>Paid</option>
                <option value="Unpaid" <?= $filters['status'] === 'Unpaid' ? 'selected' : ''; ?>>Unpaid</option>
            </select>
        </div>
        <div class="col-sm-4">
            <label for="due_date" class="form-label">Due date</label>
            <input type="date" id="due_date" name="due_date" class="form-control" value="<?= htmlspecialchars($filters['due_date']); ?>">
        </div>
        <div class="col-12 d-flex gap-2">
            <button type="submit" name="filter" class="btn btn-primary"><i class="fas fa-filter me-1"></i>Apply filters</button>
            <a class="btn btn-outline-secondary" href="manage_pledges.php">Reset</a>
        </div>
    </form>

    <div class="table-responsive printable-table">
        <div class="d-flex align-items-center gap-3 mb-3">
            <img src="<?= htmlspecialchars($assetBase); ?>assets/images/logo.png" alt="Church Logo" width="72" height="72" class="rounded-circle">
            <div>
                <h2 class="h5 mb-0">AIC Baraka Hunters</h2>
                <small class="text-muted" id="currentDateTime"></small>
            </div>
        </div>
        <table class="table table-bordered table-striped align-middle">
            <thead class="table-dark">
                <tr>
                    <th scope="col">First name</th>
                    <th scope="col">Last name</th>
                    <th scope="col">Pledge type</th>
                    <th scope="col">Status</th>
                    <th scope="col">Due date</th>
                    <th scope="col">Pledge amount</th>
                    <th scope="col">Amount paid</th>
                    <th scope="col">Pending amount</th>
                    <th scope="col" class="text-center">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($pledges): ?>
                    <?php foreach ($pledges as $row): ?>
                    <?php
                    $currentPending = max($row['pledge_amount'] - $row['amount_paid'], 0);
                    $subtotalPledgeAmount += $row['pledge_amount'];
                    $subtotalAmountPaid += $row['amount_paid'];
                    $pendingAmount += $currentPending;
                    ?>
                    <tr class="<?= $row['status'] === 'Paid' ? 'table-success' : 'table-warning'; ?>">
                        <td><?= htmlspecialchars($row['firstname']); ?></td>
                        <td><?= htmlspecialchars($row['lastname']); ?></td>
                        <td><?= htmlspecialchars($row['pledge_type']); ?></td>
                        <td><?= htmlspecialchars($row['status']); ?></td>
                        <td><?= htmlspecialchars($row['due_date']); ?></td>
                        <td><?= htmlspecialchars(number_format($row['pledge_amount'], 2)); ?></td>
                        <td><?= htmlspecialchars(number_format($row['amount_paid'], 2)); ?></td>
                        <td><?= htmlspecialchars(number_format($currentPending, 2)); ?></td>
                        <td>
                            <form method="post" class="row g-2 align-items-center">
                                <input type="hidden" name="id" value="<?= $row['id']; ?>">
                                <div class="col-md-5">
                                    <input type="number" step="0.01" min="0" class="form-control" name="amount_paid" value="0" placeholder="Amount">
                                </div>
                                <div class="col-md-3">
                                    <select name="status" class="form-select">
                                        <option value="Unpaid" <?= $row['status'] === 'Unpaid' ? 'selected' : ''; ?>>Unpaid</option>
                                        <option value="Paid" <?= $row['status'] === 'Paid' ? 'selected' : ''; ?>>Paid</option>
                                    </select>
                                </div>
                                <div class="col-md-4">
                                    <button type="submit" name="update" class="btn btn-success w-100">Update</button>
                                </div>
                            </form>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="9" class="text-center">No pledges matched your current filters.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <div class="row g-3 mt-3">
        <div class="col-md-4">
            <div class="card border-primary h-100">
                <div class="card-body">
                    <h2 class="h6 text-uppercase text-muted">Total pledged</h2>
                    <p class="display-6 fw-bold mb-0"><?= htmlspecialchars(number_format($subtotalPledgeAmount, 2)); ?></p>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border-success h-100">
                <div class="card-body">
                    <h2 class="h6 text-uppercase text-muted">Amount received</h2>
                    <p class="display-6 fw-bold mb-0"><?= htmlspecialchars(number_format($subtotalAmountPaid, 2)); ?></p>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border-warning h-100">
                <div class="card-body">
                    <h2 class="h6 text-uppercase text-muted">Pending amount</h2>
                    <p class="display-6 fw-bold mb-0"><?= htmlspecialchars(number_format($pendingAmount, 2)); ?></p>
                </div>
            </div>
        </div>
    </div>
</main>
<script>
    document.addEventListener('DOMContentLoaded', () => {
        const dateTimeElement = document.getElementById('currentDateTime');
        if (dateTimeElement) {
            dateTimeElement.textContent = new Date().toLocaleString();
        }

        const pdfButton = document.getElementById('downloadPDF');
        if (pdfButton) {
            pdfButton.addEventListener('click', () => {
                const element = document.querySelector('.printable-table');
                if (!element) {
                    return;
                }
                html2pdf().set({ filename: 'youth_pledge_report.pdf', margin: 10 }).from(element).save();
            });
        }
    });
</script>
<?php include '../includes/footer.php'; ?>
