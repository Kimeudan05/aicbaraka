<?php
require_once '../includes/config.php';

if (!isset($_SESSION['admin_id'])) {
    header('Location: login.php');
    exit();
}

if (isset($_GET['delete_id'])) {
    $deleteId = (int) $_GET['delete_id'];

    $stmt = $conn->prepare('SELECT profile_picture FROM users WHERE id = ?');
    $stmt->bind_param('i', $deleteId);
    $stmt->execute();
    $stmt->bind_result($profilePicture);
    $stmt->fetch();
    $stmt->close();

    $stmt = $conn->prepare('DELETE FROM users WHERE id = ?');
    $stmt->bind_param('i', $deleteId);
    if ($stmt->execute()) {
        if ($profilePicture) {
            $profilePath = dirname(__DIR__) . '/' . ltrim($profilePicture, '/');
            if (file_exists($profilePath)) {
                unlink($profilePath);
            }
        }
        $message = 'Youth deleted successfully.';
    } else {
        $message = 'Failed to delete youth.';
    }
    $stmt->close();
}

$youths = [];
$stmt = $conn->prepare("SELECT id, firstname, lastname, email, phone, profile_picture FROM users WHERE role = 'youth' ORDER BY lastname, firstname");
$stmt->execute();
$result = $stmt->get_result();
while ($row = $result->fetch_assoc()) {
    $youths[] = $row;
}
$stmt->close();

$pageTitle = 'Manage Youth';
$bodyClass = 'has-sidebar';
$showSidebarToggle = true;
$extraScripts = [
    'https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js',
    'https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js'
];
include '../includes/header.php';
include 'admin_sidebar.php';
?>
<main class="app-main container py-4">
    <div class="d-flex align-items-center justify-content-between bg-light border rounded p-3 mb-4">
        <div class="d-flex align-items-center gap-3">
            <img src="<?= htmlspecialchars($assetBase); ?>assets/images/logo.png" alt="Church Logo" width="72" height="72" class="rounded-circle">
            <div>
                <h1 class="h4 mb-0">Registered Youths</h1>
                <small id="currentDateTime" class="text-muted"></small>
            </div>
        </div>
        <div class="text-end">
            <button class="btn btn-outline-primary me-2" type="button" onclick="printTable()"><i class="fas fa-print me-1"></i>Print</button>
            <button class="btn btn-primary" type="button" onclick="downloadPDF()"><i class="fas fa-file-pdf me-1"></i>Download PDF</button>
        </div>
    </div>

    <?php if (isset($message)): ?>
        <div class="alert alert-info alert-dismissible fade show" role="alert">
            <?= htmlspecialchars($message); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>

    <div class="table-responsive table-container">
        <table class="table table-striped table-bordered align-middle">
            <thead class="table-dark">
                <tr>
                    <th scope="col">ID</th>
                    <th scope="col">Profile</th>
                    <th scope="col">Name</th>
                    <th scope="col">Email</th>
                    <th scope="col">Phone</th>
                    <th scope="col" class="text-center">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($youths): ?>
                    <?php foreach ($youths as $youth): ?>
                        <tr>
                            <td><?= htmlspecialchars($youth['id']); ?></td>
                            <td>
                                <?php if (!empty($youth['profile_picture'])): ?>
                                    <img src="<?= htmlspecialchars($assetBase . $youth['profile_picture']); ?>" alt="Profile picture" class="rounded" width="72" height="72">
                                <?php else: ?>
                                    <img src="<?= htmlspecialchars($assetBase); ?>assets/images/boy.png" alt="Default profile" class="rounded" width="72" height="72">
                                <?php endif; ?>
                            </td>
                            <td><?= htmlspecialchars($youth['firstname'] . ' ' . $youth['lastname']); ?></td>
                            <td><?= htmlspecialchars($youth['email']); ?></td>
                            <td><?= htmlspecialchars($youth['phone']); ?></td>
                            <td class="text-center">
                                <a href="manage_youth.php?delete_id=<?= $youth['id']; ?>" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure you want to delete this youth?');">
                                    <i class="fas fa-trash-alt"></i> Delete
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="6" class="text-center">No youth records found.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</main>
<script>
    document.addEventListener('DOMContentLoaded', () => {
        const dateTimeElement = document.getElementById('currentDateTime');
        if (dateTimeElement) {
            const now = new Date();
            dateTimeElement.textContent = now.toLocaleString();
        }
    });

    function printTable() {
        const tableContent = document.querySelector('.table-container').innerHTML;
        const printWindow = window.open('', '_blank');
        if (!printWindow) {
            return;
        }
        printWindow.document.write('<html><head><title>Youth Records</title>');
        printWindow.document.write('<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">');
        printWindow.document.write('</head><body>');
        printWindow.document.write(tableContent);
        printWindow.document.write('</body></html>');
        printWindow.document.close();
        printWindow.print();
    }

    async function downloadPDF() {
        const { jsPDF } = window.jspdf;
        const table = document.querySelector('.table-container');
        if (!jsPDF || !table) {
            return;
        }

        const canvas = await html2canvas(table);
        const imageData = canvas.toDataURL('image/png');

        const pdf = new jsPDF('p', 'mm', 'a4');
        const pageWidth = pdf.internal.pageSize.getWidth();
        const pageHeight = pdf.internal.pageSize.getHeight();
        const imageHeight = (canvas.height * pageWidth) / canvas.width;

        pdf.text('Youth Ministry Roster', 14, 16);
        pdf.addImage(imageData, 'PNG', 10, 24, pageWidth - 20, imageHeight);
        pdf.save('youth_roster.pdf');
    }
</script>
<?php include '../includes/footer.php'; ?>
