<?php
require_once '../includes/config.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

$youthId = $_SESSION['user_id'];

$stmt = $conn->prepare('SELECT firstname, lastname, email, phone, profile_picture FROM users WHERE id = ?');
$stmt->bind_param('i', $youthId);
$stmt->execute();
$stmt->bind_result($firstName, $lastName, $email, $phone, $profilePicture);
$stmt->fetch();
$stmt->close();

$errors = [];
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $phone = sanitize_input($_POST['phone'] ?? '');

    if ($phone === '') {
        $errors[] = 'Phone number is required.';
    }

    $uploadedPath = $profilePicture;
    if (isset($_FILES['profile_picture']) && $_FILES['profile_picture']['error'] === UPLOAD_ERR_OK) {
        $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif'];
        $originalName = $_FILES['profile_picture']['name'];
        $tmpPath = $_FILES['profile_picture']['tmp_name'];
        $extension = strtolower(pathinfo($originalName, PATHINFO_EXTENSION));

        if (!in_array($extension, $allowedExtensions, true)) {
            $errors[] = 'Invalid file type. Please upload an image (JPG, PNG, or GIF).';
        } else {
            $newFilename = uniqid('profile_', true) . '.' . $extension;
            $uploadDirectory = dirname(__DIR__) . '/assets/images/profiles/';
            if (!is_dir($uploadDirectory)) {
                mkdir($uploadDirectory, 0755, true);
            }

            if (move_uploaded_file($tmpPath, $uploadDirectory . $newFilename)) {
                if ($profilePicture) {
                    $existingPath = dirname(__DIR__) . '/' . $profilePicture;
                    if (is_file($existingPath)) {
                        unlink($existingPath);
                    }
                }
                $uploadedPath = 'assets/images/profiles/' . $newFilename;
            } else {
                $errors[] = 'Failed to upload the new profile picture. Please try again.';
            }
        }
    }

    if (!$errors) {
        $stmt = $conn->prepare('UPDATE users SET phone = ?, profile_picture = ? WHERE id = ?');
        $stmt->bind_param('ssi', $phone, $uploadedPath, $youthId);
        if ($stmt->execute()) {
            $success = 'Profile updated successfully.';
            $profilePicture = $uploadedPath;
        } else {
            $errors[] = 'Failed to update profile details.';
        }
        $stmt->close();
    }
}

$pageTitle = 'My Profile';
$bodyClass = 'has-sidebar';
$showSidebarToggle = true;
include '../includes/header.php';
include 'sidebar.php';
?>
<main class="app-main container py-4">
    <h1 class="h4 mb-3">My profile</h1>
    <p class="text-muted mb-4">Keep your contact information up to date so we can reach you about upcoming events.</p>

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

    <div class="row g-4">
        <div class="col-md-4">
            <div class="card shadow-sm h-100">
                <div class="card-body text-center">
                    <?php if ($profilePicture): ?>
                        <img src="<?= htmlspecialchars($assetBase . $profilePicture); ?>" class="img-fluid rounded-circle mb-3" alt="Profile picture" style="width: 180px; height: 180px; object-fit: cover;">
                    <?php else: ?>
                        <div class="d-flex align-items-center justify-content-center bg-light rounded-circle mb-3" style="width: 180px; height: 180px;">
                            <i class="fas fa-user fa-4x text-muted"></i>
                        </div>
                    <?php endif; ?>
                    <h2 class="h5 mb-0"><?= htmlspecialchars($firstName . ' ' . $lastName); ?></h2>
                    <p class="text-muted"><?= htmlspecialchars($email); ?></p>
                </div>
            </div>
        </div>
        <div class="col-md-8">
            <div class="card shadow-sm h-100">
                <div class="card-body">
                    <form method="post" enctype="multipart/form-data" class="row g-3" novalidate>
                        <div class="col-md-6">
                            <label class="form-label" for="firstname">First name</label>
                            <input class="form-control" id="firstname" type="text" value="<?= htmlspecialchars($firstName); ?>" disabled>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label" for="lastname">Last name</label>
                            <input class="form-control" id="lastname" type="text" value="<?= htmlspecialchars($lastName); ?>" disabled>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label" for="email_field">Email</label>
                            <input class="form-control" id="email_field" type="email" value="<?= htmlspecialchars($email); ?>" disabled>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label" for="phone">Phone number</label>
                            <input class="form-control" id="phone" name="phone" type="text" value="<?= htmlspecialchars($phone ?? ''); ?>" required>
                        </div>
                        <div class="col-12">
                            <label class="form-label" for="profile_picture">Profile picture</label>
                            <input class="form-control" id="profile_picture" name="profile_picture" type="file" accept="image/*">
                            <div class="form-text">Accepted formats: JPG, PNG, GIF. Maximum size depends on server limits.</div>
                        </div>
                        <div class="col-12 d-flex justify-content-end">
                            <button type="submit" class="btn btn-primary">Save changes</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</main>
<?php include '../includes/footer.php'; ?>
