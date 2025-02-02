<?php
session_start();
require 'db.php'; // Include the database connection

// Check if user is logged in and is an Employee
if (!isset($_SESSION["id"]) || $_SESSION["role"] != 2) {
    header("Location: login.php");
    exit();
}

// $employee_id = $_SESSION['id']; // Get the logged-in user's ID

// Establish the database connection (ensure this is done before any other operations)
$conn = mysqli_connect("localhost", "hr3_mfinance", "bgn^C8sHe8k*aPC6", "hr3_mfinance");

// Check if a hiring ID is provided for prediction
if (isset($_GET['id'])) {
    $hiring_id = intval($_GET['id']);
    
    // Adjust the paths to your live server environment
    // Make sure to replace this with the correct path to your virtual environment and Python script
    $python_path = "/path/to/venv/bin/python";  // Update to your live server Python path
    $script_path = "/path/to/predict_model.py";  // Update to your live server predict_model.py path

    // Construct the shell command
    $command = escapeshellcmd("$python_path $script_path $hiring_id");

    // Execute the command and capture the output
    $output = shell_exec($command . " 2>&1");  // Capture stderr as well for debugging

    // Display the result from Python script
    if ($output) {
        // Extract the suitability score from the Python output
        $prediction_score = (float)trim($output);
        
        // Format the output to 3 decimal places as a fallback
        $formatted_prediction = number_format($prediction_score, 3);

        // Update the suitability score in the hiring table for the specific applicant
        $update_query = "UPDATE hiring SET suitability_score = $formatted_prediction WHERE id = $hiring_id";
        $result = mysqli_query($conn, $update_query);

        if ($result) {
            // Output message confirming score update
            $prediction_result = "Predicted suitability score for Applicant ID " .  $hiring_id . ": " . $formatted_prediction;
        } else {
            // Log the MySQL error for debugging if the query fails
            error_log("MySQL error: " . mysqli_error($conn));
            $prediction_result = "Error updating suitability score: " . mysqli_error($conn);
        }
    } else {
        // Log the error from shell_exec for debugging
        error_log("Error running prediction script: " . $command);
        $prediction_result = "Error running prediction script.";
    }
    
} else {
    $prediction_result = "";    
}


// Fetch job applications from the hiring table
$query = "SELECT id, fName, lName, job_position, experience, suitability_score FROM hiring";
$result = $conn->query($query);


// Do NOT close the connection until after all queries are done
// Close the database connection
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
                <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="navbarDropdown">
                    <li><a class="dropdown-item text-muted" href="logout.php">Logout</a></li>
                    <li><a class="dropdown-item text-muted" href="employee.php">Profile</a></li>
                </ul>
            </li>
        </ul>
    </nav>

    <div id="layoutSidenav">
        <div id="layoutSidenav_nav">
            <nav class="sb-sidenav accordion sb-sidenav-dark" id="sidenavAccordion">
                <div class="sb-sidenav-menu">
                    <div class="nav">
                        <div class="sb-sidenav-menu-heading">Interface</div>
                        <a class="nav-link" href="employee_job.php">
                            <div class="sb-nav-link-icon"><i class="fas fa-chart-area"></i></div>
                            Job applications
                        </a>

                        <div class="sb-sidenav-menu-heading">Message</div>

                        <a class="nav-link" href="requests.php">
                            <div class="sb-nav-link-icon"><i class="fas fa-file-alt"></i></div>
                            Message
                        </a>

                        <!-- Messages -->
                        <a class="nav-link" href="messages.php">
                            <div class="sb-nav-link-icon"><i class="fas fa-envelope"></i></div>
                            Message Log
                        </a>
                        <a class="nav-link" href="task_answer.php">
                            <div class="sb-nav-link-icon"><i class="fas fa-file-alt"></i></div>
                            Task
                        </a>
                        <a class="nav-link" href="predict_suitability.php">
                            <div class="sb-nav-link-icon"><i class="fas fa-file-alt"></i></div>
                            Suitability Score
                        </a>
                    </div>
                </div>
                <div class="sb-sidenav-footer bg-dark">
                    <div class="small">Logged in as: <?php echo htmlspecialchars($_SESSION['user_name']); ?></div>
                </div>
            </nav>
        </div>

        <div id="layoutSidenav_content">
            <main>
                <div class="container-fluid px-4">
                    <h1 class="mt-4">Job Suitability Prediction</h1>

                    <div class="card mb-4">
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

                            <!-- Job applications table with search and pagination -->
                            <table id="jobApplicationsTable" class="table table-striped table-bordered">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>First Name</th>
                                        <th>Last Name</th>
                                        <th>Job Position</th>
                                        <th>Experience</th>
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
                                            $experience = $row['experience'];
                                            $suitability_score = $row['suitability_score'];

                                            echo '
                                            <tr>
                                                <td>' . htmlspecialchars($hiring_id) . '</td>
                                                <td>' . htmlspecialchars($fName) . '</td>
                                                <td>' . htmlspecialchars($lName) . '</td>
                                                <td>' . htmlspecialchars($job_position) . '</td>
                                                <td>' . htmlspecialchars($experience) . '</td>
                                                <td>' . htmlspecialchars($suitability_score) . '</td>
                                                <td>
                                                    <a href="predict_suitability.php?id=' . $hiring_id . '" class="btn btn-primary">Check Suitability</a>
                                                </td>
                                            </tr>';
                                        }
                                    } else {
                                        echo "<tr><td colspan='7'>No job applications found.</td></tr>";
                                    }
                                    ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </main>

            <footer class="py-4 bg-light mt-auto">
                <div class="container-fluid px-4">
                    <div class="d-flex align-items-center justify-content-between small">
                        <div class="text-muted">© 2024 Microfinance</div>
                    </div>
                </div>
            </footer>
        </div>
    </div>

    <script src="js/scripts.js"></script>

    <!-- Bootstrap and DataTable scripts -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/simple-datatables@7.1.2/dist/umd/simple-datatables.min.js"></script>
    <script>
        // Initialize DataTable with search and pagination
        const dataTable = new simpleDatatables.DataTable("#jobApplicationsTable", {
            searchable: true,
            fixedHeight: true,
            perPage: 10 // Show 10 records per page
        });
    </script>
</body>

