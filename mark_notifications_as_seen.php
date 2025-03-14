<?php
require 'db.php';

// Update the `is_notified` field to 1 (seen) for all unseen applicants
$query = "UPDATE hiring SET is_notified = 1 WHERE is_notified = 0";
if ($conn->query($query) === TRUE) {
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false, 'error' => $conn->error]);
}

$conn->close();
?>
