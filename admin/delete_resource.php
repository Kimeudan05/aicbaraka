<?php
// delete_resource.php

require_once '../includes/config.php';

// Start the session
session_start();

// Check if admin is logged in
if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit();
}

// Check if the 'id' parameter is set in the URL
if (isset($_GET['id'])) {
    // Get the resource ID from the URL
    $id = intval($_GET['id']);

    // Fetch the resource from the database to get the file path
    $stmt = $conn->prepare("SELECT file_path FROM resources WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows > 0) {
        $stmt->bind_result($file_path);
        $stmt->fetch();

        // Full file path on the server
        $full_file_path = "../assets/resources/" . $file_path;

        // Delete the file from the server if it exists
        if (file_exists($full_file_path)) {
            if (!unlink($full_file_path)) {
                $_SESSION['error_message'] = "Failed to delete the resource file.";
                header("Location: resources.php");
                exit();
            }
        }

        // Now delete the resource from the database
        $stmt = $conn->prepare("DELETE FROM resources WHERE id = ?");
        $stmt->bind_param("i", $id);

        if ($stmt->execute()) {
            $_SESSION['success_message'] = "Resource deleted successfully.";
        } else {
            $_SESSION['error_message'] = "Failed to delete the resource from the database.";
        }

    } else {
        $_SESSION['error_message'] = "Resource not found.";
    }

    $stmt->close();

} else {
    $_SESSION['error_message'] = "Invalid resource ID.";
}

// Redirect back to the resources management page
header("Location: resources.php");
exit();
?>
