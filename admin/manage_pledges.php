<?php

ob_start();
session_start();

// admin/manage_pledges.php
require_once '../includes/config.php';
include '../includes/header.php';
include 'admin_sidebar.php';

// Check if admin is logged in
if (!isset($_SESSION['admin_id']) || !isset($_SESSION['admin_name'])) {
  // Redirect to login if not logged in
  header("Location: login.php");
  exit();
}

// Get admin name
$admin_name = isset($_SESSION['admin_name']) ? $_SESSION['admin_name'] : 'Admin';

// Handling filter inputs
$filters = [
  'pledge_type' => '',
  'status' => '',
  'due_date' => ''
];

// Handling filter submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['filter'])) {
  $filters['pledge_type'] = $_POST['pledge_type'];
  $filters['status'] = $_POST['status'];
  $filters['due_date'] = $_POST['due_date'];
}
// Handling updates (mark as paid or unpaid)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update'])) {
  $id = $_POST['id'];
  $amount_paid = $_POST['amount_paid'];
  $status = $_POST['status'];

  // Fetch the original pledge amount and the already paid amount
  $query = "SELECT pledge_amount, amount_paid FROM youth_pledges WHERE id = ?";
  $stmt = $conn->prepare($query);
  $stmt->bind_param("i", $id);
  $stmt->execute();
  $stmt->bind_result($pledge_amount, $previous_amount_paid);
  $stmt->fetch();
  $stmt->close();

  // Calculate the current pending amount (original pledge - previously paid amount)
  $current_pending = $pledge_amount - $previous_amount_paid;

  // Ensure amount paid does not exceed the current pending amount
  if ($amount_paid > $current_pending) {
    $error_message = "Amount paid cannot exceed the current pending amount.";
  } else {
    // Update the pledge record with the new amount paid
    $new_amount_paid = $previous_amount_paid + $amount_paid;
    $new_status = ($new_amount_paid >= $pledge_amount) ? 'Paid' : 'Unpaid';

    // Update the record with the new amount paid and status
    $update_query = "UPDATE youth_pledges SET amount_paid = ?, status = ? WHERE id = ?";
    $stmt = $conn->prepare($update_query);
    $stmt->bind_param("dsi", $new_amount_paid, $new_status, $id);
    $stmt->execute();
    $stmt->close();
  }
}

// Query to fetch filtered pledge records
$query = "SELECT youth_pledges.id, users.firstname, users.lastname, youth_pledges.pledge_type,
          youth_pledges.status, youth_pledges.due_date, youth_pledges.pledge_amount,
          youth_pledges.amount_paid
          FROM youth_pledges
          JOIN users ON youth_pledges.youth_id = users.id
          WHERE 1=1";

if (!empty($filters['pledge_type'])) {
  $query .= " AND youth_pledges.pledge_type = '" . $conn->real_escape_string($filters['pledge_type']) . "'";
}

if (!empty($filters['status'])) {
  $query .= " AND youth_pledges.status = '" . $conn->real_escape_string($filters['status']) . "'";
}

if (!empty($filters['due_date'])) {
  $query .= " AND youth_pledges.due_date = '" . $conn->real_escape_string($filters['due_date']) . "'";
}

$result = $conn->query($query);

// Initialize subtotals
$subtotal_pledge_amount = 0;
$subtotal_amount_paid = 0;
$pending_amount = 0;

ob_end_flush();
?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <title>Manage Youth Pledges</title>
  <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
  <style>
    @media print {
      body * {
        visibility: hidden;
      }

      .printable-table,
      .printable-table * {
        visibility: visible;
      }

      .printable-table {
        position: absolute;
        left: 0;
        top: 0;
        width: 100%;
      }
    }

    .paid-row {
      background-color: #d4edda;
    }

    .unpaid-row {
      background-color: #f8d7da;
    }

    .status-checkbox {
      width: 30px;
      height: 30px;
      cursor: pointer;
    }

    .table th,
    .table td {
      text-align: center;
    }

    .table-striped tbody tr:nth-of-type(odd) {
      background-color: #f9f9f9;
    }

    .table-bordered th,
    .table-bordered td {
      border: 1px solid #ddd;
    }

    .subtotal-row {
      font-weight: bold;
      background-color: #f1f1f1;
    }

    .btn-update {
      background-color: #28a745;
      color: white;
    }

    .btn-update:hover {
      background-color: #218838;
    }

    .table th {
      background-color: #17a2b8;
      color: white;
    }
  </style>
</head>

<body>
  <div class="container mt-2">
    <h2>Youth Pledge Reports</h2>

    <?php if (isset($error_message)): ?>
      <div class="alert alert-danger"><?= htmlspecialchars($error_message) ?></div>
    <?php endif; ?>

    <!-- Filter Form -->
    <form method="POST" class="form-inline mb-3 p-3 bg-success w-75">
      <select name="pledge_type" class="form-control mr-2">
        <option value="">All Pledge Types</option>
        <option value="Youth Kit" <?= $filters['pledge_type'] == 'Youth Kit' ? 'selected' : '' ?>>Youth Kit</option>
        <option value="Christmas Carols" <?= $filters['pledge_type'] == 'Christmas Carols' ? 'selected' : '' ?>>Christmas Carols</option>
        <option value="Retreat Giving" <?= $filters['pledge_type'] == 'Retreat Giving' ? 'selected' : '' ?>>Retreat Giving</option>
      </select>

      <select name="status" class="form-control mr-2">
        <option value="">All Statuses</option>
        <option value="Paid" <?= $filters['status'] == 'Paid' ? 'selected' : '' ?>>Paid</option>
        <option value="Unpaid" <?= $filters['status'] == 'Unpaid' ? 'selected' : '' ?>>Unpaid</option>
      </select>

      <input type="date" name="due_date" class="form-control mr-2" value="<?= htmlspecialchars($filters['due_date']) ?>">
      <button type="submit" name="filter" class="btn btn-primary">Filter</button>
      <button type="button" class="btn btn-secondary ml-2" onclick="window.print()">Print</button>
      <button type="button" class="btn btn-info ml-2" id="downloadPDF">Download as PDF</button>
    </form>

    <!-- Printable Table -->
    <div class="printable-table">
      <div class="d-flex align-items-center bg-secondary w-100 justify-content-around p-2">
        <img src="../assets/images/logo.png" alt="Church Logo" style="height: 100px;width: 100px; margin-right: 10px;">
        <h3 class="mb-0">AIC BARAKA HUNTERS</h3>
        <div id="currentDateTime" class="font-weight-bold"></div>
      </div>

      <table class="table table-bordered table-striped mt-3 table-secondary">
        <thead>
          <tr>
            <th>First Name</th>
            <th>Last Name</th>
            <th>Pledge Type</th>
            <th>Status</th>
            <th>Due Date</th>
            <th>Pledge Amount</th>
            <th>Amount Paid</th>
            <th>Pending Amount</th>
            <th>Actions</th>
          </tr>
        </thead>
        <tbody>
          <?php while ($row = $result->fetch_assoc()): ?>
            <tr class="<?= $row['status'] == 'Paid' ? 'paid-row' : 'unpaid-row' ?>">
              <td><?= htmlspecialchars($row['firstname']) ?></td>
              <td><?= htmlspecialchars($row['lastname']) ?></td>
              <td><?= htmlspecialchars($row['pledge_type']) ?></td>
              <td>
                <input type="checkbox" class="status-checkbox" <?= $row['status'] == 'Paid' ? 'checked' : '' ?> disabled>
              </td>
              <td><?= htmlspecialchars($row['due_date']) ?></td>
              <td><?= htmlspecialchars($row['pledge_amount']) ?></td>
              <td><?= htmlspecialchars($row['amount_paid']) ?></td>
              <?php
              $current_pending = $row['pledge_amount'] - $row['amount_paid'];
              $pending_amount += $current_pending;
              ?>
              <td><?= htmlspecialchars($current_pending) ?></td>
              <td>
                <form method="POST" class="form-inline">
                  <input type="hidden" name="id" value="<?= $row['id'] ?>">
                  <input type="number" name="amount_paid" class="form-control mr-2" value="<?= $row['amount_paid'] ?>" min="0" max="<?= $row['pledge_amount'] ?>" step="0.01">
                  <select name="status" class="form-control mr-2">
                    <option value="Unpaid" <?= $row['status'] == 'Unpaid' ? 'selected' : '' ?>>Unpaid</option>
                    <option value="Paid" <?= $row['status'] == 'Paid' ? 'selected' : '' ?>>Paid</option>
                  </select>
                  <button type="submit" name="update" class="btn btn-success btn-update">Update</button>
                </form>
              </td>
            </tr>
            <?php
            $subtotal_pledge_amount += $row['pledge_amount'];
            $subtotal_amount_paid += $row['amount_paid'];
            ?>
          <?php endwhile; ?>
        </tbody>
      </table>
    </div>

    <!-- Subtotals -->
    <div class="mt-3">
      <strong>Subtotal Pledge Amount:</strong> <?= htmlspecialchars($subtotal_pledge_amount) ?><br>
      <strong>Subtotal Amount Paid:</strong> <?= htmlspecialchars($subtotal_amount_paid) ?><br>
      <strong>Total Pending Amount:</strong> <?= htmlspecialchars($pending_amount) ?><br>
      <strong>Acknowledgment:</strong> Prepared by <?= htmlspecialchars($admin_name) ?>
    </div>
  </div>

  <script>
    // Display current date and time
    function updateDateTime() {
      const dateTimeElement = document.getElementById('currentDateTime');
      const now = new Date();
      dateTimeElement.textContent = now.toLocaleString();
    }
    updateDateTime();
  </script>

  <script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.9.3/html2pdf.bundle.min.js"></script>
  <script>
    // Download table as PDF
    document.getElementById('downloadPDF').addEventListener('click', function() {
      const element = document.querySelector('.printable-table');
      html2pdf()
        .from(element)
        .save('youth_pledge_report.pdf');
    });
  </script>
</body>

</html>

<?php include '../includes/footer.php'; ?>