<?php
session_start(); // Start the session at the top of the file

// Check if the user is logged in by verifying if 'id' and 'role' are set
if (!isset($_SESSION['id']) || $_SESSION['role'] != 1) {
    // Redirect to login page if not logged in or if the role is not HR manager
    header("Location: login.php");
    exit(); // Stop further execution
}

// Now that we know the user is logged in, fetch the user ID
$user_id = $_SESSION['id']; // Logged-in user's ID

require 'db.php';

// SQL query to fetch messages for the HR manager
$sql = "SELECT reports.id, users.first_name, users.last_name, reports.message, reports.file_path, reports.date_sent 
        FROM reports 
        JOIN users ON reports.instructor_id = users.id 
        ORDER BY reports.date_sent DESC";

$messages = []; // Array to hold fetched messages
if ($stmt = $conn->prepare($sql)) {
    $stmt->execute();
    $result = $stmt->get_result();

    // Fetch messages into the array
    while ($row = $result->fetch_assoc()) {
        $messages[] = $row;
    }

    $stmt->close();
} else {
    echo "Error preparing the SQL query: " . htmlspecialchars($conn->error);
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />
    <meta name="description" content="" />
    <meta name="author" content="" />
    <title>Reports</title>
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
        <a class="navbar-brand ps-3" href="predict_suitability.php">Microfinance</a>
        <button class="btn btn-link btn-sm order-1 order-lg-0 me-4 me-lg-0" id="sidebarToggle" href="#"><i class="fas fa-bars"></i></button>

        <!-- Right side of navbar (moved dropdown to the far right) -->
        <ul class="navbar-nav ms-auto">
            <li class="nav-item dropdown">
                <a class="nav-link dropdown-toggle" id="navbarDropdown" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                    <i class="fas fa-user fa-fw"></i>
                </a>
               <ul class="dropdown-menu dropdown-menu-end bg-dark" aria-labelledby="navbarDropdown">
                    <li><a class="dropdown-item text-muted" href="logout.php">Logout</a></li>
                </ul>
            </li>
        </ul>
    </nav>

    <div id="layoutSidenav">
        <!-- Sidebar -->
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
                        <a class="nav-link" href="job_list.php">
                            <div class="sb-nav-link-icon"><i class="fas fa-chart-area"></i></div>
                            Application list
                        </a>
                        <a class="nav-link" href="hr_job.php">
                            <div class="sb-nav-link-icon"><i class="fas fa-chart-area"></i></div>
                            Job applicants
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

        <!-- Main Content Area for Reports -->
        <div id="layoutSidenav_content" class="bg-dark">
            <div class="container mt-4">
                <h2 class="text-white">Reports</h2>
                <div class="list-group">
                    <?php if (!empty($messages)): ?>
                        <?php foreach ($messages as $msg): ?>
                            <div class="list-group-item bg-dark-low d-flex justify-content-between align-items-center">
                                <div>
                                    <h5 class="text-light">From: <?php echo htmlspecialchars($msg['first_name']) . " " . htmlspecialchars($msg['last_name']); ?></h5>
                                    <p class="text-light"><?php echo htmlspecialchars($msg['message']); ?></p>
                                    <small class="text-light">Sent on: <?php echo htmlspecialchars($msg['date_sent']); ?></small>
                                </div>
                                <div>
                                    <!-- View Button -->
                                    <button class="btn btn-primary me-2" data-bs-toggle="modal" data-bs-target="#viewModal<?php echo $msg['id']; ?>">View</button>
                                    <!-- Delete Button -->
                                    <a href="delete_report.php?id=<?php echo $msg['id']; ?>" class="btn btn-danger">Delete</a>
                                </div>
                            </div>

                            <!-- Modal for Viewing the Message -->
                            <div class="modal fade" id="viewModal<?php echo $msg['id']; ?>" tabindex="-1" aria-labelledby="viewModalLabel<?php echo $msg['id']; ?>" aria-hidden="true">
                                <div class="modal-dialog">
                                    <div class="modal-content">
                                        <div class="modal-header">
                                            <h5 class="modal-title" id="viewModalLabel<?php echo $msg['id']; ?>">Message from <?php echo htmlspecialchars($msg['first_name']) . " " . htmlspecialchars($msg['last_name']); ?></h5>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                        </div>
                                        <div class="modal-body">
                                            <p><?php echo htmlspecialchars($msg['message']); ?></p>
                                            <?php if (!empty($msg['file_path'])): ?>
                                                <a href="<?php echo htmlspecialchars($msg['file_path']); ?>" class="btn btn-primary" download>Download File</a>
                                            <?php else: ?>
                                                <p class="text-muted">No file attached.</p>
                                            <?php endif; ?>
                                        </div>
                                        <div class="modal-footer">
                                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div class="alert alert-info text-light">No reports to display.</div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap Bundle JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="js/scripts.js"></script>

</body>

</html>
