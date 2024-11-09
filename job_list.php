<?php 
session_start();
require 'db.php'; // Ensure your database connection is successful

// Check if user is logged in and is an admin
if (!isset($_SESSION["id"]) || $_SESSION["role"] != 1) {
    header("Location: login.php");
    exit();
}

// Fetch logged-in user data
$user_id = $_SESSION['id'];
$user_query = "SELECT fName, lName FROM users WHERE id = ?";
$stmt = $conn->prepare($user_query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$user_result = $stmt->get_result();
$user = $user_result->fetch_assoc();

// Fetch approved hire applications
$approved_query = "SELECT h.id, h.fName, h.lName, h.age, h.email, h.date_uploaded, h.status, 
                          c.city_name AS city, h.job_position
                   FROM hiring h
                   JOIN users u ON h.user_id = u.id
                   LEFT JOIN cities c ON h.city_id = c.city_id
                   WHERE h.application_type = 'hiring' AND h.status = 'approved'";
$approved_result = $conn->query($approved_query);

// Fetch declined hire applications
$declined_query = "SELECT h.id, h.fName, h.lName, h.age, h.email, h.date_uploaded, h.status, 
                          c.city_name AS city, h.job_position
                   FROM hiring h
                   JOIN users u ON h.user_id = u.id
                   LEFT JOIN cities c ON h.city_id = c.city_id
                   WHERE h.application_type = 'hiring' AND h.status = 'declined'";
$declined_result = $conn->query($declined_query);

// Check for query errors
if (!$approved_result || !$declined_result) {
    die("Query Failed: " . $conn->error);
}

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />
    <meta name="description" content="" />
    <meta name="author" content="" />
    <title>Job List</title>
    <link href="css/styles.css" rel="stylesheet" />
    <script src="https://use.fontawesome.com/releases/v6.3.0/js/all.js" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/css/bootstrap.min.css" rel="stylesheet" crossorigin="anonymous">
</head>

<body class="sb-nav-fixed bg-dark">
    <!-- Top Navbar -->
    <nav class="sb-topnav navbar navbar-expand navbar-dark bg-dark">
        <a class="navbar-brand ps-3" href="job_chart.php">Microfinance</a>

        <!-- Navbar Toggle Button for collapsing navbar -->
        <button class="btn btn-link btn-sm order-1 order-lg-0 me-4 me-lg-0" id="sidebarToggle" href="#"><i class="fas fa-bars"></i></button>

        <!-- Right side of navbar -->
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

    <!-- Sidenav -->
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
                        <a class="nav-link" href="tesda_chart.php">
                            <div class="sb-nav-link-icon"><i class="fas fa-chart-area"></i></div>
                            Tesda Charts
                        </a>

                        <div class="sb-sidenav-menu-heading"> Lists </div>
                        <a class="nav-link" href="job_list.php">
                            <div class="sb-nav-link-icon"><i class="fas fa-list"></i></div>
                            Job List
                        </a>
                        <a class="nav-link" href="tesda_list.php">
                            <div class="sb-nav-link-icon"><i class="fas fa-list"></i></div>
                            Tesda List
                        </a>
                    </div>
                </div>
                <div class="sb-sidenav-footer bg-dark">
                    <div class="small">Logged in as: <?php echo htmlspecialchars($_SESSION['user_name']); ?></div>
                </div>
            </nav>
        </div>

        <!-- Main Content -->
        <div id="layoutSidenav_content">
            <main>
                <div class="container-fluid px-4">
                    <h1 class="mt-4 text-light">Job Applications List</h1>

                    <!-- Approved Applications Table -->
                    <div class="card mb-4">
                        <div class="card-header">
                            <i class="fas fa-table me-1"></i>
                            List of Approved Hiring Applications
                        </div>
                        <div class="card-body table-responsive">
                            <table class="table table-striped table-light">
                                <thead>
                                    <tr>
                                        <th>ID</th> 
                                        <th>First Name</th>
                                        <th>Last Name</th>
                                        <th>Age</th>
                                        <th>Email</th>
                                        <th>City</th>
                                        <th>Job Position</th>
                                        <th>Date Uploaded</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    if ($approved_result->num_rows > 0) {
                                        while ($row = $approved_result->fetch_assoc()) {
                                            echo "<tr>
                                                    <td>{$row['id']}</td>
                                                    <td>{$row['fName']}</td>
                                                    <td>{$row['lName']}</td>
                                                    <td>{$row['age']}</td>
                                                    <td>{$row['email']}</td>
                                                    <td>{$row['city']}</td>
                                                    <td>{$row['job_position']}</td>
                                                    <td>{$row['date_uploaded']}</td>
                                                  </tr>";
                                        }
                                    } else {
                                        echo "<tr><td colspan='7'>No approved applications found.</td></tr>";
                                    }
                                    ?>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <!-- Button to Download Approved Applications as Excel -->
                    <button id="downloadAllBtn" class="btn btn-success mb-3">Download Approved Applications as Excel</button>

                    <!-- Declined Applications Table -->
                    <div class="card mb-4">
                        <div class="card-header">
                            <i class="fas fa-table me-1"></i>
                            List of Declined Hiring Applications
                        </div>
                        <div class="card-body table-responsive">
                            <table class="table table-striped table-light">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>First Name</th>
                                        <th>Last Name</th>
                                        <th>Age</th>
                                        <th>Email</th>
                                        <th>City</th>
                                        <th>Job Position</th>
                                        <th>Date Uploaded</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    if ($declined_result->num_rows > 0) {
                                        while ($row = $declined_result->fetch_assoc()) {
                                            echo "<tr>
                                                    <td>{$row['id']}</td>
                                                    <td>{$row['fName']}</td>
                                                    <td>{$row['lName']}</td>
                                                    <td>{$row['age']}</td>
                                                    <td>{$row['email']}</td>
                                                    <td>{$row['city']}</td>
                                                    <td>{$row['job_position']}</td>
                                                    <td>{$row['date_uploaded']}</td>
                                                  </tr>";
                                        }
                                    } else {
                                        echo "<tr><td colspan='7'>No declined applications found.</td></tr>";
                                    }
                                    ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </main>
        </div>
    </div>

    <!-- JavaScript to handle download action -->
    <script>
        document.getElementById('downloadAllBtn').addEventListener('click', function() {
            window.location.href = 'download_list.php'; // Redirect to download the Excel file
        });
    </script>

    <!-- Bootstrap Bundle and Other Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js" crossorigin="anonymous"></script>
    <script src="js/scripts.js"></script>
</body>

</html>
