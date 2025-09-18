<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$pageTitle = $pageTitle ?? 'AIC Baraka Hunters Youth Ministry';
$bodyClass = trim('app-body ' . ($bodyClass ?? ''));
$showSidebarToggle = $showSidebarToggle ?? false;
$extraStylesheets = $extraStylesheets ?? [];

if (!isset($assetBase)) {
    $scriptDir = trim(dirname($_SERVER['PHP_SELF']), '/');
    if ($scriptDir === '') {
        $assetBase = '';
    } else {
        $assetBase = str_repeat('../', substr_count($scriptDir, '/') + 1);
    }
}

$brandHref = $brandHref ?? $assetBase . 'index.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= htmlspecialchars($pageTitle); ?></title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css" integrity="sha512-Kc32mEVGcVq/654eBjXf6hlPdVsa1LQfFqkGRoZLl0D7MmAxJq7ljk4C5HBcDbopn7VDaNwslzHgdi0w8qlA1g==" crossorigin="anonymous" referrerpolicy="no-referrer" />
    <link rel="stylesheet" href="<?= htmlspecialchars($assetBase); ?>assets/css/styles.css">
    <?php foreach ($extraStylesheets as $stylesheet): ?>
        <link rel="stylesheet" href="<?= htmlspecialchars($stylesheet); ?>">
    <?php endforeach; ?>
</head>
<body class="<?= htmlspecialchars($bodyClass); ?>">
<nav class="navbar navbar-expand-lg navbar-dark bg-dark shadow-sm app-navbar">
    <div class="container-fluid">
        <a class="navbar-brand d-flex align-items-center gap-2" href="<?= htmlspecialchars($brandHref); ?>">
            <img src="<?= htmlspecialchars($assetBase); ?>assets/images/logo.png" alt="Church Logo" width="48" height="48" class="rounded-circle">
            <span class="fw-semibold">AIC Baraka Hunters</span>
        </a>
        <?php if ($showSidebarToggle): ?>
            <button class="btn btn-outline-light d-lg-none me-2" id="sidebarToggle" type="button" aria-controls="appSidebar" aria-expanded="false" aria-label="Toggle sidebar">
                <i class="fas fa-bars"></i>
            </button>
        <?php endif; ?>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#appNavbar" aria-controls="appNavbar" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="appNavbar">
            <ul class="navbar-nav ms-auto align-items-lg-center">
                <?php if (isset($_SESSION['admin_id'])): ?>
                    <li class="nav-item"><a class="nav-link" href="<?= htmlspecialchars($assetBase); ?>admin/dashboard.php">Dashboard</a></li>
                    <li class="nav-item"><a class="nav-link" href="<?= htmlspecialchars($assetBase); ?>admin/profile.php">Profile</a></li>
                    <li class="nav-item"><a class="nav-link" href="<?= htmlspecialchars($assetBase); ?>admin/logout.php">Logout</a></li>
                <?php elseif (isset($_SESSION['user_id'])): ?>
                    <li class="nav-item"><a class="nav-link" href="<?= htmlspecialchars($assetBase); ?>youth/dashboard.php">Dashboard</a></li>
                    <li class="nav-item"><a class="nav-link" href="<?= htmlspecialchars($assetBase); ?>youth/profile.php">Profile</a></li>
                    <li class="nav-item"><a class="nav-link" href="<?= htmlspecialchars($assetBase); ?>youth/logout.php">Logout</a></li>
                <?php else: ?>
                    <li class="nav-item"><a class="nav-link" href="<?= htmlspecialchars($assetBase); ?>youth/login.php">Youth Login</a></li>
                    <li class="nav-item"><a class="nav-link" href="<?= htmlspecialchars($assetBase); ?>admin/login.php">Admin Login</a></li>
                    <li class="nav-item"><a class="nav-link" href="<?= htmlspecialchars($assetBase); ?>youth/register.php">Register</a></li>
                <?php endif; ?>
            </ul>
        </div>
    </div>
</nav>
<div class="app-container container-fluid py-4">
