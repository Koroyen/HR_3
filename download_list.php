<?php
require 'db.php'; // Include your database connection

if ($_SERVER['REQUEST_METHOD'] == 'GET') {
    // Set the headers to force download as an Excel file
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename=approved_job_applications.csv');

    // Create a file pointer connected to the output stream
    $output = fopen('php://output', 'w');

    // Output the column headings
    fputcsv($output, array('First Name', 'Last Name', 'Age', 'Email', 'City', 'Job Position', 'Date Uploaded'));

    // Query to fetch approved job applications
    $query = "SELECT 
                  hiring.fName, 
                  hiring.lName, 
                  hiring.Age, 
                  hiring.email, 
                  cities.city_name AS city,  -- Fetch city name from cities table
                  hiring.job_position, 
                  hiring.date_uploaded
              FROM hiring
              LEFT JOIN cities ON hiring.city_id = cities.city_id  -- Join with cities table
              WHERE hiring.status = 'Approved' 
              AND hiring.application_type = 'hiring'";

    $result = mysqli_query($conn, $query);

    if (!$result) {
        die('Query failed: ' . mysqli_error($conn)); // Error handling
    }

    // Fetch each row and output it as CSV
    if (mysqli_num_rows($result) > 0) {
        while ($row = mysqli_fetch_assoc($result)) {
            fputcsv($output, array(
                $row['fName'],
                $row['lName'],
                $row['Age'],
                $row['email'],
                $row['city'],
                $row['job_position'],
                $row['date_uploaded']
            ));
        }
    } else {
        // Output a message if no data found
        fputcsv($output, array('No approved job applications found.'));
    }

    // Close the output stream
    fclose($output);
    exit();
}
?>
