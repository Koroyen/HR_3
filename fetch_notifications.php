<?php
session_start();
require 'db.php';

// Check if the user is logged in
if (!isset($_SESSION['id'])) {
    echo "<p>Error: User not logged in.</p>";
    exit();
}

// Get the logged-in user ID from the session
$user_id = $_SESSION['id'];

// Query to fetch notifications from all three tables for the current user
$query = "
    SELECT 'birthc' AS source, images_coe_birthc.status, images_coe_birthc.date_status_updated, images_coe_birthc.message, users.fName
    FROM users
    JOIN images_coe_birthc ON images_coe_birthc.user_id = users.id
    WHERE users.id = ?
    UNION ALL
    SELECT 'hiring' AS source, hiring.status, hiring.date_status_updated, hiring.message, users.fName
    FROM users
    JOIN hiring ON hiring.user_id = users.id
    WHERE users.id = ?
    UNION ALL
    SELECT 'certificate' AS source, certificate.status, certificate.date_status_updated, certificate.message, users.fName
    FROM users
    JOIN certificate ON certificate.user_id = users.id
    WHERE users.id = ?
    ORDER BY date_status_updated DESC
";

// Prepare the statement
$stmt = $conn->prepare($query);

if ($stmt === false) {
    echo "<p>Error preparing the query: " . htmlspecialchars($conn->error) . "</p>";
    exit();
}

// Bind the user ID to the query three times (for each UNION-ed query)
$stmt->bind_param('iii', $user_id, $user_id, $user_id);

// Execute the query
if ($stmt->execute()) {
    $result = $stmt->get_result();

    // Check if there are notifications
    if ($result->num_rows > 0) {
        // Loop through and display notifications
        while ($row = $result->fetch_assoc()) {
            $message = !empty($row['message']) ? htmlspecialchars($row['message']) : 'No message provided';
            $status = htmlspecialchars($row['status']);
            $fName = htmlspecialchars($row['fName']);
            $date_updated = htmlspecialchars($row['date_status_updated']);
            $source = htmlspecialchars($row['source']); // To know the source table

            // Display the notifications with table-specific information
            echo "<div class='notification-item mb-3 p-2 border rounded'>";
            echo "<p><strong>Source: </strong>" . ucfirst($source) . "</p>"; // Show which table this notification came from
            echo "<p><strong>Name: </strong>$fName</p>";
            echo "<p><strong>Status: </strong>$status</p>";
            echo "<p><strong>Message: </strong>$message</p>";
            echo "<p><small>Date Updated: $date_updated</small></p>";
            echo "</div>";
        }
    } else {
        echo "<p>No new notifications.</p>";
    }
} else {
    echo "<p>Error executing the query: " . htmlspecialchars($stmt->error) . "</p>";
}

// Close the statement
$stmt->close();
?>
