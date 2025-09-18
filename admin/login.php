<?php
require_once '../includes/config.php';

$errors = [];
$email = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = sanitize_input($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($email === '' || $password === '') {
        $errors[] = 'Both email and password are required.';
    }

    if (!$errors) {
        $stmt = $conn->prepare('SELECT id, password, name, role FROM users WHERE email = ? LIMIT 1');
        $stmt->bind_param('s', $email);
        $stmt->execute();
        $stmt->store_result();

        if ($stmt->num_rows === 1) {
            $stmt->bind_result($id, $hashedPassword, $name, $role);
            $stmt->fetch();

            if ($role === 'admin' && password_verify($password, $hashedPassword)) {
                $_SESSION['admin_id'] = $id;
                $_SESSION['admin_name'] = $name;
                header('Location: dashboard.php');
                exit();
            }
        }

        $errors[] = 'Invalid login credentials.';
        $stmt->close();
    }
}

$pageTitle = 'Admin Login';
include '../includes/header.php';
?>
<main class="app-main container py-5">
    <div class="row justify-content-center">
        <div class="col-md-6 col-lg-5">
            <div class="card shadow-sm">
                <div class="card-body p-4">
                    <h1 class="h4 text-center mb-4">Admin Login</h1>
                    <?php if ($errors): ?>
                        <div class="alert alert-danger">
                            <ul class="mb-0">
                                <?php foreach ($errors as $error): ?>
                                    <li><?= htmlspecialchars($error); ?></li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                    <?php endif; ?>
                    <form method="post" novalidate>
                        <div class="mb-3">
                            <label for="email" class="form-label">Email address</label>
                            <input type="email" class="form-control" id="email" name="email" value="<?= htmlspecialchars($email); ?>" required autocomplete="username">
                        </div>
                        <div class="mb-3">
                            <label for="password" class="form-label">Password</label>
                            <div class="input-group">
                                <input type="password" class="form-control" id="password" name="password" required autocomplete="current-password">
                                <button class="btn btn-outline-secondary password-toggle" type="button" data-password-toggle="password">
                                    <i class="fa fa-eye" aria-hidden="true"></i>
                                    <span class="visually-hidden">Toggle password visibility</span>
                                </button>
                            </div>
                        </div>
                        <div class="d-grid">
                            <button type="submit" class="btn btn-primary">Sign in</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</main>
<?php include '../includes/footer.php'; ?>
