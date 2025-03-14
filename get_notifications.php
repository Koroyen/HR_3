<?php
require 'db.php';

// Fetch applicant ID, name, and date_uploaded where is_notified is 0
$query = "SELECT id, CONCAT(fName, ' ', lName) AS name, date_uploaded FROM hiring WHERE is_notified = 0";
$result = $conn->query($query);

$applicants = [];
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $applicants[] = [
            'id' => $row['id'], 
            'name' => $row['name'], 
            'date_uploaded' => $row['date_uploaded']
        ];
    }
}

// Send JSON response with applicant data and count
echo json_encode(['count' => count($applicants), 'applicants' => $applicants]);

$conn->close();
?>

