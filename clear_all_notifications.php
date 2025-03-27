<?php
require 'db.php';

// Update the hiring table to set is_notified = 1 for all applicants
$query = "UPDATE hiring SET is_notified = 1 WHERE is_notified = 0";

if ($conn->query($query) === TRUE) {
    // Respond with success
    echo json_encode(['success' => true]);
} else {
    // Respond with error if something goes wrong
    echo json_encode(['success' => false, 'error' => $conn->error]);
}

$conn->close();
?>
