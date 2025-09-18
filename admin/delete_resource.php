<?php
require_once '../includes/config.php';

if (!isset($_SESSION['admin_id'])) {
    header('Location: login.php');
    exit();
}

if (isset($_GET['id'])) {
    $id = (int) $_GET['id'];

    $stmt = $conn->prepare('SELECT file_path FROM resources WHERE id = ?');
    $stmt->bind_param('i', $id);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows > 0) {
        $stmt->bind_result($filePath);
        $stmt->fetch();
        $stmt->close();

        $absolutePath = dirname(__DIR__) . '/' . ltrim($filePath, '/');
        if ($filePath && file_exists($absolutePath)) {
            if (!unlink($absolutePath)) {
                $_SESSION['error_message'] = 'Failed to delete the resource file.';
                header('Location: resources.php');
                exit();
            }
        }

        $delete = $conn->prepare('DELETE FROM resources WHERE id = ?');
        $delete->bind_param('i', $id);
        if ($delete->execute()) {
            $_SESSION['success_message'] = 'Resource deleted successfully.';
        } else {
            $_SESSION['error_message'] = 'Failed to delete the resource from the database.';
        }
        $delete->close();
    } else {
        $stmt->close();
        $_SESSION['error_message'] = 'Resource not found.';
    }
} else {
    $_SESSION['error_message'] = 'Invalid resource ID.';
}

header('Location: resources.php');
exit();
