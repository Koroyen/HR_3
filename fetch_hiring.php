<?php
require 'db.php';

$query = "SELECT hiring.*, cities.city_name 
          FROM hiring 
          LEFT JOIN cities ON hiring.city_id = cities.city_id 
          WHERE hiring.is_visible = 1
          ORDER BY date_status_updated DESC"; // Order by date to get the latest on top

$result = mysqli_query($conn, $query);

if (mysqli_num_rows($result) > 0) {
    while ($row = mysqli_fetch_assoc($result)) {
        $highlightClass = (time() - strtotime($row['date_status_updated']) < 60 * 60) ? 'table-warning' : ''; // Highlight new records (less than an hour old)
        echo "<tr class='{$highlightClass}'>
                <td>{$row['id']}</td>
                <td>{$row['fName']}</td>
                <td>{$row['lName']}</td>
                <td>{$row['Age']}</td>
                <td>{$row['sex']}</td>
                <td>{$row['job_position']}</td>
                <td>{$row['email']}</td>
                <td>{$row['street']}</td>
                <td>{$row['barangay']}</td>
                <td>{$row['city_name']}</td>
                <td><img src='hiring/{$row['valid_ids']}' alt='ID' style='width: 100px;'></td>
                <td><img src='hiring/{$row['birthcerti']}' alt='Birth Certificate' style='width: 100px;'></td>
                <td>{$row['status']}</td>
                <td>{$row['date_uploaded']}</td>
                <td>{$row['date_status_updated']}</td>
                <td>
                    <form method='POST'>
                        <input type='hidden' name='id' value='{$row['id']}' />
                        " . ($row['status'] == 'Pending' ? "
                        <button type='submit' name='action' value='Approved' class='btn btn-success btn-sm'>Approve</button>
                        <button type='submit' name='action' value='Declined' class='btn btn-danger btn-sm'>Decline</button>
                        " : "
                        <button type='submit' name='action' value='remove' class='btn btn-warning btn-sm'>Remove</button>") . "
                    </form>
                </td>
              </tr>";
    }
} else {
    echo "<tr><td colspan='16'>No records found</td></tr>";
}
?>
