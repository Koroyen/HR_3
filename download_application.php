<?php
// Include database connection
include 'db.php';

// Set headers to download the file as an Excel sheet
header("Content-Type: application/vnd.ms-excel");
header("Content-Disposition: attachment; filename=approved_applications.xls");
header("Pragma: no-cache");
header("Expires: 0");

// Open a table for Excel
echo "<table border='1'>";
echo "<tr>
        <th>ID</th>
        <th>First Name</th>
        <th>Last Name</th>
        <th>Age</th>
        <th>Sex</th>
        <th>Email</th>
        <th>Street</th>
        <th>Status</th>
        <th>Date Uploaded</th>
        <th>Date Status Updated</th>
      </tr>";

// Query to fetch approved applications
$query = "SELECT id, fName, lName, Age, sex, email, city, status, date_uploaded, date_status_updated
          FROM images_coe_birthc 
          WHERE status = 'Approved'";
$result = mysqli_query($conn, $query);

if (!$result) {
    die('Query failed: ' . mysqli_error($conn)); 
}

if (mysqli_num_rows($result) > 0) {
    // Loop through the results and print each row in the table
    while ($row = mysqli_fetch_assoc($result)) {
        echo "<tr>
                <td>".htmlspecialchars($row['id'])."</td>
                <td>".htmlspecialchars($row['fName'])."</td>
                <td>".htmlspecialchars($row['lName'])."</td>
                <td>".htmlspecialchars($row['Age'])."</td>
                <td>".htmlspecialchars($row['sex'])."</td>
                <td>".htmlspecialchars($row['email'])."</td>
                <td>".htmlspecialchars($row['city'])."</td>
                <td>".htmlspecialchars($row['status'])."</td>
                <td>".htmlspecialchars($row['date_uploaded'])."</td>
                <td>".htmlspecialchars($row['date_status_updated'])."</td>
              </tr>";
    }
} else {
    echo "<tr><td colspan='10'>No approved applications found.</td></tr>";
}

// Close the table
echo "</table>";

// Close database connection
mysqli_close($conn);
?>
