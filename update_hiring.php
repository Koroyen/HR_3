<?php
session_start();
require 'db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_POST['id']) || !isset($_POST['action'])) {
        echo "Missing data.";
        exit();
    }

    $id = $_POST['id'];
    $action = $_POST['action'];

    if ($action == 'Approved') {
        $update_query = "UPDATE hiring SET status = 'Approved', date_status_updated = NOW() WHERE id = ?";
    } elseif ($action == 'Declined') {
        $update_query = "UPDATE hiring SET status = 'Declined', date_status_updated = NOW(), is_visible = 0 WHERE id = ?";
    } elseif ($action == 'remove') {
        $update_query = "UPDATE hiring SET is_visible = 0 WHERE id = ?";
    }

    $stmt = $conn->prepare($update_query);
    $stmt->bind_param('i', $id);

    if ($stmt->execute()) {
        echo "Status updated successfully.";
    } else {
        echo "Error: " . $stmt->error;
    }
}
?>
