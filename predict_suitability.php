<?php
session_start();
require 'db.php'; // Include the database connection

// Check if user is logged in and is an Employee
if (!isset($_SESSION["id"]) || $_SESSION["role"] != 1) {
    header("Location: login.php");
    exit();
}

// Establish the database connection
$conn = mysqli_connect("localhost", "hr3_mfinance", "bgn^C8sHe8k*aPC6", "hr3_mfinance");

// Check for connection errors
if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

// Check if a hiring ID is provided for prediction
if (isset($_GET['id'])) {
    $hiring_id = intval($_GET['id']);

    // Run the Python script for predicting suitability using the hiring_id
    $command = escapeshellcmd("C:/xampp/htdocs/mfinance/venv/Scripts/python.exe C:/xampp/htdocs/mfinance/predict_model.py $hiring_id");
    $output = shell_exec($command);

    // Debugging lines are commented out for cleanliness
    // echo "<pre>Raw output from Python script: " . $output . "</pre>";

    // Capture the last line of the output, which should be the score
    $lines = explode("\n", trim($output));
    $last_line = end($lines); // Get the last line

    // Convert the last line to a float (ensure it's the score)
    $prediction_score = (float)trim($last_line);

    // Debugging line commented out
    // echo "<pre>Raw score before formatting: " . $prediction_score . "</pre>";

    // Format the output to 3 decimal places
    $formatted_prediction = number_format($prediction_score, 3);

    // Debugging line commented out
    // echo "<pre>Formatted score: " . $formatted_prediction . "</pre>";

    // Update the suitability score in the hiring table for the specific applicant
    $update_query = "UPDATE hiring SET suitability_score = $formatted_prediction WHERE id = $hiring_id";
    $result = mysqli_query($conn, $update_query);

    if ($result) {
        // Check if the score was updated correctly in the database
        $check_query = "SELECT suitability_score FROM hiring WHERE id = $hiring_id";
        $check_result = mysqli_query($conn, $check_query);
        $row = mysqli_fetch_assoc($check_result);
        $updated_score = $row['suitability_score'];

        // Debugging line commented out
        // echo "<pre>Updated score in DB: " . $updated_score . "</pre>";

        // Output the final result confirming score update
        $prediction_result = "Predicted suitability score for Applicant ID " .  $hiring_id . ": " . $formatted_prediction;
    } else {
        // Debugging line commented out
        // echo "<pre>MySQL Update Error: " . mysqli_error($conn) . "</pre>";
        $prediction_result = "Error updating suitability score: " . mysqli_error($conn);
    }
} else {
    // Debugging line commented out
    // echo "<pre>Error: No hiring ID provided.</pre>";
    $prediction_result = "";
}


// Fetch the list of applicants
$query = "SELECT id, fName, lName, job_position, experience_years, experience_months, education, otherEducation, suitability_score FROM hiring";
$result = $conn->query($query);

// Do NOT close the connection until after all queries are done
mysqli_close($conn);


?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />
    <meta name="description" content="" />
    <meta name="author" content="" />
    <title>Job Suitability Prediction</title>
    <script src="https://cdn.jsdelivr.net/npm/simple-datatables@7.1.2/dist/umd/simple-datatables.min.js"></script>
    <link href="https://cdn.jsdelivr.net/npm/simple-datatables@7.1.2/dist/style.min.css" rel="stylesheet" />
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="css/styles.css" rel="stylesheet" />
    <script src="https://use.fontawesome.com/releases/v6.3.0/js/all.js" crossorigin="anonymous"></script>
</head>

<body class="sb-nav-fixed">
    <!-- Top Navbar -->
    <nav class="sb-topnav navbar navbar-expand navbar-dark bg-dark">
        <a class="navbar-brand ps-3" href="employee_job.php">Microfinance</a>

        <!-- Navbar Toggle Button for collapsing navbar -->
        <button class="btn btn-link btn-sm order-1 order-lg-0 me-4 me-lg-0" id="sidebarToggle" href="#"><i class="fas fa-bars"></i></button>

        <!-- Right side of navbar (moved dropdown to the far right) -->
        <ul class="navbar-nav ms-auto">
            <li class="nav-item dropdown">
                <a class="nav-link dropdown-toggle" id="navbarDropdown" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                    <i class="fas fa-user fa-fw"></i>
                </a>
                <ul class="dropdown-menu dropdown-menu-end " aria-labelledby="navbarDropdown">
                    <li><a class="dropdown-item text-muted" href="logout.php">Logout</a></li>
                    
                </ul>
            </li>
        </ul>
    </nav>

    <div id="layoutSidenav">
        <div id="layoutSidenav_nav">
            <nav class="sb-sidenav accordion sb-sidenav-dark" id="sidenavAccordion">
                <div class="sb-sidenav-menu">
                    <div class="nav">
                    <div class="sb-sidenav-menu-heading"> Charts </div>
                    <a class="nav-link" href="job_chart.php">
                            <div class="sb-nav-link-icon"><i class="fas fa-chart-area"></i></div>
                            Job Charts
                        </a>
                        <div class="sb-sidenav-menu-heading"> Lists</div>
                        <a class="nav-link" href="job_list.php">
                            <div class="sb-nav-link-icon"><i class="fas fa-chart-area"></i></div>
                            Application list
                        </a>
                        <a class="nav-link" href="hr_job.php">
                            <div class="sb-nav-link-icon"><i class="fas fa-chart-area"></i></div>
                            Job applicants
                        </a>
                        <a class="nav-link" href="predict_suitability.php">
                            <div class="sb-nav-link-icon"><i class="fas fa-file-alt"></i></div>
                            Suitability Score
                        </a>
                        <a class="nav-link" href="reports.php">
                            <div class="sb-nav-link-icon"><i class="fas fa-file-alt"></i></div>
                            Reports
                        </a>
                    </div>
                </div>
                <div class="sb-sidenav-footer bg-dark">
                    <div class="small">Logged in as: <?php echo htmlspecialchars($_SESSION['user_name']); ?></div>
                </div>
            </nav>
        </div>
        <div id="layoutSidenav_content" class="bg-dark-low">
            <div class="container-fluid px-4 bg-dark-low">
                <h1 class="mt-4 text-light">Job Suitability Prediction</h1>

                <div class="card mb-4 bg-dark-low">
                    <div class="card-header">
                        <i class="fas fa-chart-area me-1"></i>
                        Job Applications
                    </div>
                    <div class="card-body">
                        <!-- Display the prediction result -->
                        <?php if (!empty($prediction_result)): ?>
                            <div class="alert alert-info">
                                <?php echo $prediction_result; ?>
                            </div>
                        <?php endif; ?>

                        <!-- Job applications table -->
                        <table id="jobApplicationsTable" class="table table-striped table-bordered">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>First Name</th>
                                    <th>Last Name</th>
                                    <th>Job Position</th>
                                    <th>Experience (Years)</th>
                                    <th>Experience (Months)</th>
                                    <th>Education</th>
                                    <th>Other Education</th>
                                    <th>Suitability Score</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                if ($result && $result->num_rows > 0) {
                                    while ($row = $result->fetch_assoc()) {
                                        $hiring_id = $row['id'];
                                        $fName = $row['fName'];
                                        $lName = $row['lName'];
                                        $job_position = $row['job_position'];
                                        $experience_years = $row['experience_years'];
                                        $experience_months = $row['experience_months'];
                                        $education = $row['education'];
                                        $otherEducation = $row['otherEducation'];
                                        $suitability_score = $row['suitability_score'];

                                        echo '
                        <tr>
                            <td>' . htmlspecialchars($hiring_id) . '</td>
                            <td>' . htmlspecialchars($fName) . '</td>
                            <td>' . htmlspecialchars($lName) . '</td>
                            <td>' . htmlspecialchars($job_position) . '</td>
                            <td>' . htmlspecialchars($experience_years) . '</td>
                            <td>' . htmlspecialchars($experience_months) . '</td>
                            <td>' . htmlspecialchars($education) . '</td>
                            <td>' . htmlspecialchars($otherEducation) . '</td>
                            <td>' . htmlspecialchars($suitability_score) . '</td>
                            <td>
                                <a href="predict_suitability.php?id=' . $hiring_id . '" class="btn btn-primary">Check Suitability</a>
                            </td>
                        </tr>';
                                    }
                                } else {
                                    echo "<tr><td colspan='10'>No job applications found.</td></tr>";
                                }
                                ?>
                            </tbody>
                        </table>
                    </div>
                </div>

            </div>
            </main>

            <footer class="py-4 bg-dark-low mt-auto">
                <div class="container-fluid px-4">
                    <div class="d-flex align-items-center justify-content-between small">
                        <div class="text-muted">Microfinance © 2025</div>
                    </div>
                </div>
            </footer>
        </div>
    </div>

    <!-- Script to enable datatable search and pagination -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            var dataTable = new simpleDatatables.DataTable('#jobApplicationsTable');
        });
    </script>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

</body>

</html>