<?php
require_once '../includes/config.php';

if (isset($_SESSION['user_id'])) {
    header('Location: dashboard.php');
    exit();
}

$errors = [];
$email = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = sanitize_input($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($email === '' || $password === '') {
        $errors[] = 'Both email and password are required.';
    }

    if (!$errors) {
        $stmt = $conn->prepare('SELECT id, password, role FROM users WHERE email = ?');
        $stmt->bind_param('s', $email);
        $stmt->execute();
        $stmt->store_result();

        if ($stmt->num_rows === 1) {
            $stmt->bind_result($id, $hashedPassword, $role);
            $stmt->fetch();

            if ($role === 'admin') {
                $errors[] = 'A youth account with this email does not exist.';
            } elseif (password_verify($password, $hashedPassword)) {
                $_SESSION['user_id'] = $id;
                header('Location: dashboard.php');
                exit();
            } else {
                $errors[] = 'Incorrect password.';
            }
        } else {
            $errors[] = 'No account found with that email.';
        }

        $stmt->close();
    }
}

$pageTitle = 'Youth Login';
include '../includes/header.php';
?>
<main class="app-main container py-5">
    <div class="row justify-content-center">
        <div class="col-lg-10">
            <div class="card shadow-sm overflow-hidden">
                <div class="row g-0">
                    <div class="col-md-5 bg-dark text-white p-4 d-flex flex-column justify-content-center">
                        <h2 class="h3 mb-3">Welcome back</h2>
                        <p class="mb-4">Sign in to stay connected with the community and access the latest resources and events.</p>
                        <p class="mb-0">Don&apos;t have an account yet?</p>
                        <a class="btn btn-outline-light mt-3" href="register.php">Register now</a>
                    </div>
                    <div class="col-md-7 p-4">
                        <h1 class="h4 mb-3">Youth Login</h1>
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
                                        <i class="fa fa-eye"></i>
                                        <span class="visually-hidden">Toggle password visibility</span>
                                    </button>
                                </div>
                            </div>
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <a href="forgot_password.php">Forgot password?</a>
                            </div>
                            <div class="d-grid">
                                <button type="submit" class="btn btn-primary">Sign in</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</main>
<?php include '../includes/footer.php'; ?>
