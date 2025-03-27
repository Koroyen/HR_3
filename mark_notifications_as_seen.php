<?php
require 'db.php';

// Get the notification ID from the request
$data = json_decode(file_get_contents('php://input'), true);
$id = $data['id'];

// Mark the notification as read in the database
$query = "UPDATE hiring SET is_notified = 1 WHERE id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $id);

if ($stmt->execute()) {
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false]);
}

$stmt->close();
$conn->close();
?>
