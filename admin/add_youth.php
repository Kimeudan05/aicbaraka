<?php
require_once '../includes/config.php';
require_once '../includes/password.php';

if (!isset($_SESSION['admin_id'])) {
    header('Location: login.php');
    exit();
}

$errors = [];
$success = '';
$firstname = $lastname = $email = $phone = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $firstname = sanitize_input($_POST['firstname'] ?? '');
    $lastname = sanitize_input($_POST['lastname'] ?? '');
    $email = sanitize_input($_POST['email'] ?? '');
    $phone = sanitize_input($_POST['phone'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirmPassword = $_POST['confirm_password'] ?? '';

    if ($firstname === '' || $lastname === '' || $email === '' || $phone === '' || $password === '' || $confirmPassword === '') {
        $errors[] = 'All fields except profile picture are required.';
    }

    if ($email !== '' && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Invalid email format.';
    }

    if ($password !== $confirmPassword) {
        $errors[] = 'Passwords do not match.';
    }

    if ($password !== '' && !validate_password($password)) {
        $errors[] = password_requirements_message();
    }

    if (!$errors) {
        $stmt = $conn->prepare('SELECT id FROM users WHERE email = ? LIMIT 1');
        $stmt->bind_param('s', $email);
        $stmt->execute();
        $stmt->store_result();
        if ($stmt->num_rows > 0) {
            $errors[] = 'Email is already registered.';
        }
        $stmt->close();
    }

    $profilePicturePath = null;
    if (!$errors && isset($_FILES['profile_picture']) && $_FILES['profile_picture']['error'] === UPLOAD_ERR_OK) {
        $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif'];
        $filename = $_FILES['profile_picture']['name'];
        $fileTmp = $_FILES['profile_picture']['tmp_name'];
        $fileExt = strtolower(pathinfo($filename, PATHINFO_EXTENSION));

        if (!in_array($fileExt, $allowedExtensions, true)) {
            $errors[] = 'Invalid file type for profile picture.';
        } else {
            $newFilename = uniqid('profile_', true) . '.' . $fileExt;
            $storageDirectory = dirname(__DIR__) . '/assets/images/profiles/';
            if (!is_dir($storageDirectory)) {
                mkdir($storageDirectory, 0755, true);
            }
            if (!move_uploaded_file($fileTmp, $storageDirectory . $newFilename)) {
                $errors[] = 'Unable to upload profile picture.';
            } else {
                $profilePicturePath = 'assets/images/profiles/' . $newFilename;
            }
        }
    }

    if (!$errors) {
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
        $fullName = trim($firstname . ' ' . $lastname);

        $stmt = $conn->prepare('INSERT INTO users (firstname, lastname, name, email, phone, password, profile_picture) VALUES (?, ?, ?, ?, ?, ?, ?)');
        $stmt->bind_param('sssssss', $firstname, $lastname, $fullName, $email, $phone, $hashedPassword, $profilePicturePath);

        if ($stmt->execute()) {
            $success = 'Youth added successfully.';
            $firstname = $lastname = $email = $phone = '';
        } else {
            $errors[] = 'Failed to add youth.';
        }

        $stmt->close();
    }
}

$pageTitle = 'Add Youth';
$bodyClass = 'has-sidebar';
$showSidebarToggle = true;
include '../includes/header.php';
include 'admin_sidebar.php';
?>
<main class="app-main container py-4">
    <h1 class="h4 mb-4 text-center">Add New Youth</h1>

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

    <form method="post" enctype="multipart/form-data" class="form-container bg-white rounded shadow-sm p-4">
        <div class="row g-3">
            <div class="col-md-6">
                <label for="firstname" class="form-label">First name</label>
                <input type="text" class="form-control" id="firstname" name="firstname" value="<?= htmlspecialchars($firstname); ?>" required>
            </div>
            <div class="col-md-6">
                <label for="lastname" class="form-label">Last name</label>
                <input type="text" class="form-control" id="lastname" name="lastname" value="<?= htmlspecialchars($lastname); ?>" required>
            </div>
            <div class="col-md-6">
                <label for="email" class="form-label">Email</label>
                <input type="email" class="form-control" id="email" name="email" value="<?= htmlspecialchars($email); ?>" required>
            </div>
            <div class="col-md-6">
                <label for="phone" class="form-label">Phone number</label>
                <input type="tel" class="form-control" id="phone" name="phone" value="<?= htmlspecialchars($phone); ?>" maxlength="15" required>
            </div>
            <div class="col-md-6">
                <label for="password" class="form-label">Password</label>
                <div class="input-group">
                    <input type="password" class="form-control" id="password" name="password" required>
                    <button class="btn btn-outline-secondary password-toggle" type="button" data-password-toggle="password">
                        <i class="fa fa-eye"></i>
                        <span class="visually-hidden">Toggle password visibility</span>
                    </button>
                </div>
                <div class="form-text"><?= htmlspecialchars(password_requirements_message()); ?></div>
            </div>
            <div class="col-md-6">
                <label for="confirm_password" class="form-label">Confirm password</label>
                <div class="input-group">
                    <input type="password" class="form-control" id="confirm_password" name="confirm_password" required>
                    <button class="btn btn-outline-secondary password-toggle" type="button" data-password-toggle="confirm_password">
                        <i class="fa fa-eye"></i>
                        <span class="visually-hidden">Toggle password visibility</span>
                    </button>
                </div>
            </div>
            <div class="col-12">
                <label for="profile_picture" class="form-label">Profile picture (optional)</label>
                <input type="file" class="form-control" id="profile_picture" name="profile_picture" accept="image/*">
            </div>
        </div>
        <div class="d-grid mt-4">
            <button type="submit" class="btn btn-primary">Add Youth</button>
        </div>
    </form>
</main>
<?php include '../includes/footer.php'; ?>
