<?php
// admin/manage_youth.php

require_once '../includes/config.php';

// Check if admin is logged in
if (!isset($_SESSION['admin_id'])) {
  header("Location: login.php");
  exit();
}

// Handle delete operation
if (isset($_GET['delete_id'])) {
  $delete_id = intval($_GET['delete_id']);
  // Fetch profile picture path to delete the file
  $stmt = $conn->prepare("SELECT profile_picture FROM users WHERE id = ?");
  $stmt->bind_param("i", $delete_id);
  $stmt->execute();
  $stmt->bind_result($profile_picture);
  $stmt->fetch();
  $stmt->close();

  // Delete user from database
  $stmt = $conn->prepare("DELETE FROM users WHERE id = ?");
  $stmt->bind_param("i", $delete_id);
  if ($stmt->execute()) {
    // Delete profile picture file if exists
    if ($profile_picture && file_exists("../" . $profile_picture)) {
      unlink("../" . $profile_picture);
    }
    $message = "Youth deleted successfully.";
  } else {
    $message = "Failed to delete youth.";
  }
  $stmt->close();
}

// Fetch all youths
$youths = [];
$stmt = $conn->prepare("SELECT id, firstname, lastname, email, phone, profile_picture FROM users WHERE role = 'youth'");
$stmt->execute();
$result = $stmt->get_result();
while ($row = $result->fetch_assoc()) {
  $youths[] = $row;
}
$stmt->close();
?>

<?php include '../includes/header.php'; ?>
<?php include "admin_sidebar.php"; ?>

<div class="container mt-4">
  <div class=" mb-4 table-container">
    <div class="d-flex align-items-center bg-danger-subtle w-100 justify-content-between">
      <img src="../assets/images/logo.png" alt="Church Logo" style="height: 100px;width: 100px; margin-right: 10px;">
      <h3 class="mb-0">AIC BARAKA HUNTERS</h3>
      <div id="currentDateTime" class="font-weight-bold"></div>
    </div>

    <!-- the table -->
    <h2 class="text-center">Youths Available on our site</h2>

    <?php if (isset($message)): ?>
      <div class="alert alert-info">
        <?php echo htmlspecialchars($message); ?>
      </div>
    <?php endif; ?>

    <div class="">
      <table class="table table-bordered table-striped">
        <thead class="thead-dark">
          <tr>
            <th>ID</th>
            <th>Profile Picture</th>
            <th>Name</th>
            <th>Email</th>
            <th>Phone</th>
            <th>Actions</th>
          </tr>
        </thead>
        <tbody>
          <?php if (count($youths) > 0): ?>
            <?php foreach ($youths as $youth): ?>
              <tr>
                <td>
                  <?php echo htmlspecialchars($youth['id']); ?>
                </td>
                <td>
                  <?php if ($youth['profile_picture']): ?>
                    <img src="<?php echo htmlspecialchars('../youth/' . $youth['profile_picture']); ?>" alt="Profile
                Picture" width="100" height="50">
                  <?php else: ?>
                    <img src="../assets/images/boy.png" alt="Default Profile" width="50" height="50">
                  <?php endif; ?>
                </td>
                <td>
                  <?php echo htmlspecialchars($youth['firstname'] . " " . $youth['lastname']); ?>
                </td>
                <td><?php echo htmlspecialchars($youth['email']); ?></td>
                <td>
                  <?php echo htmlspecialchars($youth['phone']); ?>
                </td>
                <td>
                  <a href="manage_youth.php?delete_id=<?php echo $youth['id']; ?>" class="btn btn-sm btn-danger" onclick="return
                confirm('Are you sure you want to delete this youth?');">Delete</a>
                </td>
              </tr>
            <?php endforeach; ?>
          <?php else: ?>
            <tr>
              <td colspan="6" class="text-center">No youths found.</td>
            </tr>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>



  <button class="btn btn-primary" onclick="printTable()">Print Table</button>
  <button class="btn btn-secondary" onclick="downloadPDF()">Download as PDF</button>
</div>

<script>
  // Display current date and time
  function updateDateTime() {
    const dateTimeElement = document.getElementById('currentDateTime');
    const now = new Date();
    dateTimeElement.textContent = now.toLocaleString();
  }
  updateDateTime();

  // Function to print only the table
  function printTable() {
    var tableContent = document.querySelector(".table-container").innerHTML;
    var printWindow = window.open("", "_blank");
    printWindow.document.write("<html><head><title>Print Table</title>");
    printWindow.document.write("<link rel='stylesheet' href='https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css'>");
    printWindow.document.write("</head><body>");
    printWindow.document.write(tableContent);
    printWindow.document.write("</body></html>");
    printWindow.document.close();
    printWindow.print();
  }

  // Function to download the table as a PDF with profile pictures
  async function downloadPDF() {
    const {
      jsPDF
    } = window.jspdf;
    const doc = new jsPDF();
    const table = document.querySelector(".table-container");

    // Use html2canvas to capture the table as an image
    const canvas = await html2canvas(table);
    const imgData = canvas.toDataURL("image/png");

    doc.text("Manage Youths", 14, 10);
    doc.addImage(imgData, 'PNG', 10, 20, 190, 0); // Adjust dimensions as needed
    doc.save('manage_youths.pdf');
  }
</script>

<!-- Include jsPDF, jsPDF-AutoTable, and html2canvas libraries -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.4.0/jspdf.umd.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf-autotable/3.5.13/jspdf.plugin.autotable.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js"></script>

<?php include '../includes/footer.php'; ?>