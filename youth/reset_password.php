<?php
require_once '../includes/config.php';

$errors = [];
$success = '';
$email = $_SESSION['reset_email'] ?? '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $newPassword = $_POST['new_password'] ?? '';
    $confirmPassword = $_POST['confirm_password'] ?? '';

    if ($email === '' || $newPassword === '' || $confirmPassword === '') {
        $errors[] = 'All fields are required.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Enter a valid email address.';
    } elseif ($newPassword !== $confirmPassword) {
        $errors[] = 'The confirmation password does not match.';
    } elseif (strlen($newPassword) < 8) {
        $errors[] = 'Please choose a password with at least 8 characters.';
    }

    if (!$errors) {
        $stmt = $conn->prepare('SELECT id FROM users WHERE email = ?');
        $stmt->bind_param('s', $email);
        $stmt->execute();
        $stmt->store_result();

        if ($stmt->num_rows === 1) {
            $stmt->close();
            $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
            $update = $conn->prepare('UPDATE users SET password = ? WHERE email = ?');
            $update->bind_param('ss', $hashedPassword, $email);

            if ($update->execute()) {
                $success = 'Your password has been updated. You can now sign in with your new credentials.';
                unset($_SESSION['reset_email']);
            } else {
                $errors[] = 'Failed to update password. Please try again.';
            }

            $update->close();
        } else {
            $errors[] = 'No account was found with that email address.';
            $stmt->close();
        }
    }
}

$pageTitle = 'Reset Password';
include '../includes/header.php';
?>
<main class="app-main container py-5">
    <div class="row justify-content-center">
        <div class="col-md-8 col-lg-6">
            <div class="card shadow-sm">
                <div class="card-body p-4">
                    <h1 class="h4 mb-3">Reset your password</h1>
                    <p class="text-muted">Create a new password for your account. Make sure it&apos;s strong and memorable.</p>

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

                    <form method="post" novalidate>
                        <div class="mb-3">
                            <label for="email" class="form-label">Email address</label>
                            <input type="email" class="form-control" id="email" name="email" required value="<?= htmlspecialchars($email); ?>" placeholder="you@example.com">
                        </div>
                        <div class="mb-3">
                            <label for="new_password" class="form-label">New password</label>
                            <div class="input-group">
                                <input type="password" class="form-control" id="new_password" name="new_password" required minlength="8" autocomplete="new-password">
                                <button class="btn btn-outline-secondary password-toggle" type="button" data-password-toggle="new_password">
                                    <i class="fa fa-eye"></i>
                                    <span class="visually-hidden">Toggle password visibility</span>
                                </button>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label for="confirm_password" class="form-label">Confirm password</label>
                            <div class="input-group">
                                <input type="password" class="form-control" id="confirm_password" name="confirm_password" required minlength="8" autocomplete="new-password">
                                <button class="btn btn-outline-secondary password-toggle" type="button" data-password-toggle="confirm_password">
                                    <i class="fa fa-eye"></i>
                                    <span class="visually-hidden">Toggle password visibility</span>
                                </button>
                            </div>
                        </div>
                        <div class="d-flex justify-content-between align-items-center">
                            <a href="login.php">Back to login</a>
                            <button type="submit" class="btn btn-primary">Update password</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</main>
<?php include '../includes/footer.php'; ?>
