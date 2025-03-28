<?php
session_start();
require 'db.php'; // Include the database connection

// Check if user is logged in and is an HR Manager (role = 1)
if (!isset($_SESSION["id"]) || $_SESSION["role"] != 'Manager') {
    header("Location: login.php");
    exit();
}

// Establish the database connection
$conn = mysqli_connect("localhost", "hr3_mfinance", "bgn^C8sHe8k*aPC6", "hr3_mfinance");

// Check for connection errors
if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

$prediction_result = ""; // Initialize the prediction result to an empty string
$output_result = ""; // Initialize the output result to an empty string

// Check if a hiring ID is provided for prediction
if (isset($_GET['id'])) {
    $hiring_id = intval($_GET['id']);

    // Run the Python script for predicting suitability using the hiring_id Live server
   $command = escapeshellcmd("python3 /home/hr3.microfinance-solution.com/public_html/predict_model.py $hiring_id");
   $output = shell_exec($command . " 2>&1");

    // Save the raw output into a variable to display in the frontend
    $output_result = "<pre>$output</pre>";

    // Capture the last line of the output, which should be the score
    $lines = explode("\n", trim($output));
    $last_line = end($lines); // Get the last line

    // Convert the last line to a float (ensure it's the score)
    $prediction_score = (float)trim($last_line);

    // Format the output to 2 decimal places
    $formatted_prediction = number_format($prediction_score, 2);

    // Update the suitability score in the hiring table for the specific applicant
    $update_query = "UPDATE hiring SET suitability_score = $formatted_prediction WHERE id = $hiring_id";
    $result = mysqli_query($conn, $update_query);

    if ($result) {
        $prediction_result = "Predicted suitability score for Applicant ID " . $hiring_id . ": " . $formatted_prediction;
    } else {
        $prediction_result = "Error updating suitability score: " . mysqli_error($conn);
    }
}

// Fetch the list of applicants
$query = "SELECT id, fName, lName, job_position, experience_years, experience_months, education, otherEducation, suitability_score FROM hiring";
$result = $conn->query($query);

// Close the connection after queries
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
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script> <!-- Chart.js -->


</head>

<body class="sb-nav-fixed bg-dark">
    <!-- Top Navbar -->
    <nav class="sb-topnav navbar navbar-expand navbar-dark bg-dark">
        <a class="navbar-brand ps-3" href="predict_suitability.php">Ascenders business services</a>
        <button class="btn btn-link btn-sm order-1 order-lg-0 me-4 me-lg-0 p-5" id="sidebarToggle" href="#"><i class="fas fa-bars"></i></button>

        <!-- Right side of navbar -->
        <ul class="navbar-nav ms-auto bg-dark text-light">
            <!-- Notification Icon with Badge -->
            <a class="nav-link" href="#" id="notificationsDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                <i class="fas fa-bell"></i>
                <span class="badge bg-danger" id="notifCount">0</span> <!-- Dynamic count -->
            </a>

            <!-- Notifications Dropdown -->
            <div class="dropdown-menu dropdown-menu-end p-3 bg-dark text-light" aria-labelledby="notificationsDropdown" style="width: 300px;">
                <!-- Notification Header -->
                <div class="d-flex justify-content-between align-items-center mb-2 bg-dark text-light">
                    <h6 class="m-0">Notifications</h6>
                    <button id="clearAllNotifications" class="btn btn-sm btn-link text-danger">Clear All</button>
                </div>

                <!-- Notifications List -->
                <ul class="list-group bg-dark text-light" id="notificationsList">
                    <li class="list-group-item text-center text-muted" id="noNotifications">No new notifications</li>
                    <!-- Dynamic notifications will be injected here -->
                </ul>
            </div>



            <!-- User dropdown -->
            <li class="nav-item dropdown">
                <a class="nav-link dropdown-toggle" id="navbarDropdown" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                    <i class="fas fa-user fa-fw"></i>
                </a>
                <ul class="dropdown-menu dropdown-menu-end bg-dark" aria-labelledby="navbarDropdown">
                    <li><a class="dropdown-item text-muted" href="logout.php">Logout</a></li>
                    <li><a class="dropdown-item text-muted" href="password.php">Change password</a></li>
                </ul>
            </li>
        </ul>
    </nav>

    <div id="layoutSidenav">
        <div id="layoutSidenav_nav">
            <nav class="sb-sidenav accordion sb-sidenav-dark" id="sidenavAccordion">
                <div class="sb-sidenav-menu">
                    <div class="nav">
                        <div class="sb-sidenav-menu-heading">Analytics</div>
                        <a class="nav-link" href="predict_suitability.php">
                            <div class="sb-nav-link-icon"><i class="fas fa-chart-area"></i></div>
                            Job Charts
                        </a>
                        <div class="sb-sidenav-menu-heading"> Lists</div>
                        <a class="nav-link" href="hr_job.php">
                            <div class="sb-nav-link-icon"><i class="fas fa-chart-area"></i></div>
                            Applicant list
                        </a>
                        <a class="nav-link" href="job_list.php">
                            <div class="sb-nav-link-icon"><i class="fas fa-chart-area"></i></div>
                            Application list
                        </a>
                        <a class="nav-link" href="reports.php">
                            <div class="sb-nav-link-icon"><i class="fas fa-file-alt"></i></div>
                            Reports
                        </a>
                        <a class="nav-link" href="job.php">
                            <div class="sb-nav-link-icon"><i class="fas fa-file-alt"></i></div>
                            Manage Job
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
                        <?php
                        // Display any prediction result
                        if (!empty($prediction_result)) {
                            echo "<div class='alert alert-info'>$prediction_result</div>";
                        }

                        // Display the output from the Python script
                        if (!empty($output_result)) {
                            echo "<div class='alert alert-warning'>$output_result</div>";
                        }
                        ?>
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
                                    $applicants = []; // For chart data
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

                                        // Add to chart data
                                        $applicants[] = [
                                            'name' => $lName,
                                            'score' => $suitability_score
                                        ];

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
                                <a href="predict_suitability.php?id=' . htmlspecialchars($hiring_id) . '" class="btn btn-primary">Predict Suitability</a>
                            </td>
                        </tr>';
                                    }
                                } else {
                                    echo '<tr><td colspan="10">No job applications found.</td></tr>';
                                }
                                ?>
                            </tbody>
                        </table>
                        <div class="chart-container">
                            <!-- Bar Chart for lName and Suitability Score -->
                            <div class="chart-item-bar">
                                <canvas id="barChart"></canvas>
                            </div>

                            <!-- Pie Chart for Suitability Score Distribution -->
                            <div class="chart-item-pie">
                                <canvas id="pieChart"></canvas>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Applicants data for the chart
            const applicants = <?php echo json_encode($applicants); ?>;
            const names = applicants.map(a => a.name);
            const scores = applicants.map(a => a.score);

            // Bar Chart (Suitability scores)
            const barCtx = document.getElementById('barChart').getContext('2d');
            const barChart = new Chart(barCtx, {
                type: 'bar',
                data: {
                    labels: names,
                    datasets: [{
                        label: 'Suitability Score',
                        data: scores,
                        backgroundColor: 'rgba(54, 162, 235, 0.6)',
                        borderColor: 'rgba(54, 162, 235, 1)',
                        borderWidth: 1
                    }]
                },
                options: {
                    responsive: true,
                    scales: {
                        y: {
                            beginAtZero: true
                        }
                    }
                }
            });

            // Pie Chart (Low, Medium, High)
            const pieCtx = document.getElementById('pieChart').getContext('2d');
            const lowCount = applicants.filter(a => a.score < 0.5).length;
            const mediumCount = applicants.filter(a => a.score >= 1.5 && a.score < 2.2).length;
            const highCount = applicants.filter(a => a.score >= 2.2).length;

            const pieChart = new Chart(pieCtx, {
                type: 'pie',
                data: {
                    labels: ['Low', 'Medium', 'High'],
                    datasets: [{
                        label: 'Suitability Distribution',
                        data: [lowCount, mediumCount, highCount],
                        backgroundColor: ['#FF6384', '#36A2EB', '#FFCE56']
                    }]
                },
                options: {
                    responsive: true
                }
            });
        });
    </script>

    <style>
        /* Flexbox layout for the charts */
        .chart-container {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        /* Adjust the bar chart size */
        .chart-item-bar {
            width: 58%;
            /* Adjust the width of the bar chart (bigger) */
        }

        /* Adjust the pie chart size */
        .chart-item-pie {
            width: 38%;
            /* Smaller width for pie chart */
        }

        /* Global canvas styling for responsiveness */
        canvas {
            max-width: 100%;
            height: auto;
        }

        /* Sidebar nav fix */
        .sb-sidenav-menu {
            overflow-y: auto;
            /* Ensure scroll if content is too large */
        }
    </style>



    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Initialize the DataTable with pagination
            const jobApplicationsTable = new simpleDatatables.DataTable('#jobApplicationsTable', {
                searchable: true,
                fixedHeight: true,
                perPage: 5 // Show 5 entries per page
            });
        });
    </script>



    <!-- JavaScript to Handle Notifications (Like Facebook) -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Fetch notifications on page load
            fetchNotifications();

            // Mark notifications as seen when you click the "Mark as Read" button
            document.getElementById('notificationsList').addEventListener('click', function(e) {
                if (e.target.classList.contains('mark-as-read')) {
                    const notificationId = e.target.getAttribute('data-id');
                    markNotificationAsRead(notificationId);
                }
            });

            // Clear all notifications when the "Clear All" button is clicked
            const clearAllButton = document.getElementById('clearAllNotifications');
            clearAllButton.addEventListener('click', function() {
                clearAllNotifications();
            });
        });

        // Function to fetch notifications via AJAX
        function fetchNotifications() {
            fetch('get_notifications.php')
                .then(response => response.json())
                .then(data => {
                    const notificationCount = document.getElementById('notifCount');
                    const notificationsList = document.getElementById('notificationsList');

                    if (data.count > 0) {
                        // Update the badge with the number of new applicants
                        notificationCount.textContent = data.count;
                        notificationCount.style.display = 'inline';

                        // Clear the default "No new notifications" message
                        notificationsList.innerHTML = '';

                        // Populate the dropdown with new applicants, including ID and date_uploaded
                        data.applicants.forEach(applicant => {
                            const formattedDate = new Date(applicant.date_uploaded).toLocaleString(); // Format the date

                            const listItem = document.createElement('li');
                            listItem.classList.add('list-group-item', 'd-flex', 'justify-content-between', 'align-items-start', 'bg-dark');
                            listItem.innerHTML = `
        <div class="ms-2 me-auto bg-dark text-light">
            <div class="fw-bold text-light">${applicant.name}</div>
            <span>New applicant applied on ${formattedDate} (ID: ${applicant.id}).</span>
        </div>
        <button class="btn btn-sm btn-outline-success mark-as-read" data-id="${applicant.id}">
            Mark as Read
        </button>`;
                            notificationsList.appendChild(listItem);
                        });

                    } else {
                        // Hide the badge if no notifications
                        notificationCount.style.display = 'none';

                        // Show "No new notifications" message
                        notificationsList.innerHTML = '<li class="list-group-item text-center text-muted">No new notifications</li>';
                    }
                });
        }

        // Function to mark a specific notification as read via AJAX
        function markNotificationAsRead(notificationId) {
            fetch('mark_notifications_as_seen.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({
                        id: notificationId
                    })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        // Remove the notification from the list
                        const listItem = document.querySelector(`button[data-id="${notificationId}"]`).parentElement;
                        listItem.remove();

                        // Update notification count
                        const notificationCount = document.getElementById('notifCount');
                        const newCount = parseInt(notificationCount.textContent) - 1;
                        if (newCount > 0) {
                            notificationCount.textContent = newCount;
                        } else {
                            notificationCount.style.display = 'none';

                            // Show "No new notifications" message if all notifications are cleared
                            const notificationsList = document.getElementById('notificationsList');
                            notificationsList.innerHTML = '<li class="list-group-item text-center text-muted">No new notifications</li>';
                        }
                    }
                });
        }

        // Function to clear all notifications via AJAX
        function clearAllNotifications() {
            fetch('clear_all_notifications.php')
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        // Clear all notifications from the list and update badge
                        const notificationsList = document.getElementById('notificationsList');
                        notificationsList.innerHTML = '<li class="list-group-item text-center text-muted">No new notifications</li>';
                        const notificationCount = document.getElementById('notifCount');
                        notificationCount.style.display = 'none';
                    }
                });
        }
    </script>


    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.8/dist/umd/popper.min.js" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js" crossorigin="anonymous"></script>
    <script src="js/scripts.js"></script>
</body>

</html>