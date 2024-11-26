<?php
// admin/delete_encouragement.php

require_once '../includes/config.php';
session_start();

// Check if admin is logged in
if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit();
}

// Check if the 'id' parameter is set in the URL
if (isset($_GET['id'])) {
    $id = intval($_GET['id']);

    // Delete the encouragement from the database
    $stmt = $conn->prepare("DELETE FROM encouragements WHERE id = ?");
    $stmt->bind_param("i", $id);

    if ($stmt->execute()) {
        $_SESSION['success_message'] = "Encouragement deleted successfully.";
    } else {
        $_SESSION['error_message'] = "Failed to delete encouragement.";
    }

    $stmt->close();

} else {
    $_SESSION['error_message'] = "Invalid encouragement ID.";
}

// Redirect back to the encouragements management page
header("Location: view_encouragements.php");
exit();
?>
