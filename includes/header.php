<!-- includes/header.php -->
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <title>Youth Ministry</title>
  <!-- Bootstrap CSS -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <!-- Custom CSS -->
  <link rel="stylesheet" href="assets/css/styles.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css" integrity="sha512-Kc323vGBEqzTmouAECnVceyQqyqdsSiqLQISBL29aUW4U/M7pSPA/gEUZQqv1cwx4OnYxTxve5UMg5GT6L4JJg==" crossorigin="anonymous" referrerpolicy="no-referrer" />
  <style>
    body {
      padding-left: 255px;
      padding-bottom: 70px;
      padding-right: 20px;
      font-size: 1.4rem;
      /* Adjust padding to accommodate sidebar width */
    }

    .sidebar-sticky {
      height: calc(100vh - 56px);
      /* Full height minus navbar */
      overflow-y: auto;
      /* Enable scrolling */
    }
  </style>
</head>

<body>
  <!-- Navbar -->
  <nav class="navbar navbar-expand-lg navbar-dark bg-dark fixed-top">
    <div class="container-fluid justify-content-between align-content-center">
      <a class="navbar-brand" href="dashboard.php">
        <img src="../assets/images/logo.png" alt="Church Logo" width="70" height="70"
          class="d-inline-block align-text-top fw-bolder">
      </a>
      <h2 class="text-white">AIC BARAKA HUNTERS</h2>
      <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav"
        aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
        <span class="navbar-toggler-icon"></span>
      </button>
      <div class="collapse navbar-collapse" id="navbarNav">
        <ul class="navbar-nav ms-auto">
          <?php if (isset($_SESSION['user_id'])): ?>
            <li class="nav-item">
              <a class="nav-link" href="profile.php">Profile</a>
            </li>
            <li class="nav-item">
              <a class="nav-link" href="logout.php">Logout</a>
            </li>

          <?php elseif (isset($_SESSION['admin_id'])): ?>
            <li class="nav-item">
              <a class="nav-link fs-4" href="profile.php">Profile</a>
            </li>
            <li class="nav-item">
              <a class="nav-link fs-4" href="logout.php">Logout</a>
            </li>
          <?php else: ?>
            <!-- <li class="nav-item">
              <a class="nav-link" href="register.php">Register</a>
            </li> -->
            <li class="nav-item">
              <a class="nav-link btn btn-primary fs-4" href="login.php">Login</a>
            </li>
          <?php endif; ?>
        </ul>
      </div>
    </div>
  </nav>

  <div class="container mt-4">
    <div class="row">
      <!-- Main Content -->
      <main class="col-md-9 col-lg-10 offset-md-3 offset-lg-2">
        <div class="main-content p-4">
          <h2 class="mb-4"></h2>

          <!-- Add any page-specific content here -->
        </div>
      </main>
    </div>
  </div>

  <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.2/dist/umd/popper.min.js"></script>
  <script src="https://stackpath.bootstrapcdn.com/bootstrap/5.3.0/js/bootstrap.min.js"></script>
</body>

</html>