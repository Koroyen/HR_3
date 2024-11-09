<?php
session_start();
require 'db.php'; // Database connection

// Ensure only HR managers or admins can access this page
if (!isset($_SESSION['id']) || $_SESSION['role'] != 1) { // Assuming role 1 is for HR/admin
    header("Location: login.php");
    exit();
}

// Fetch the activity logs from the database
$log_query = "SELECT a.application_id, a.action, a.date_uploaded, a.date_status_updated, 
                     u.fName AS employee_fName, u.lName AS employee_lName
              FROM activity_logs a
              JOIN users u ON a.user_id = u.id
              ORDER BY a.date_status_updated DESC";
$log_result = $conn->query($log_query);

if (!$log_result) {
    die("Error fetching logs: " . $conn->error); // Add error handling for debugging
}

if (isset($_POST['delete_log'])) {
    $delete_id = $_POST['delete_id']; // Get the application_id from the form

    // Prepare the delete query
    $delete_query = "DELETE FROM activity_logs WHERE application_id = ?";
    $stmt_delete = $conn->prepare($delete_query);
    $stmt_delete->bind_param('i', $delete_id); // Bind the application ID

    if ($stmt_delete->execute()) {
        echo "<script>alert('Log deleted successfully.'); window.location.href='activity_logs.php';</script>";
    } else {
        echo "<script>alert('Error deleting log: " . $conn->error . "');</script>";
    }

    $stmt_delete->close();
}
// Display logs (HTML table for displaying logs)
?>


<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />
    <meta name="description" content="" />
    <meta name="author" content="" />
    <title>Charts</title>
    <link href="https://cdn.jsdelivr.net/npm/simple-datatables@7.1.2/dist/style.min.css" rel="stylesheet" />
    <link href="css/styles.css" rel="stylesheet" />
    <script src="https://use.fontawesome.com/releases/v6.3.0/js/all.js" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/css/bootstrap.min.css" rel="stylesheet" crossorigin="anonymous">
</head>

<body class="sb-nav-fixed bg-dark">
    <!-- Top Navbar -->
    <nav class="sb-topnav navbar navbar-expand navbar-dark bg-dark">
        <a class="navbar-brand ps-3" href="job_chart.php">Microfinance</a>

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
    <!-- Sidebar -->
    <div id="layoutSidenav">
        <div id="layoutSidenav_nav">
            <nav class="sb-sidenav accordion sb-sidenav-dark" id="sidenavAccordion">
                <div class="sb-sidenav-menu">
                    <div class="nav">
                        <div class="sb-sidenav-menu-heading">Charts</div>
                        <a class="nav-link" href="job_chart.php">
                            <div class="sb-nav-link-icon"><i class="fas fa-chart-area"></i></div>
                            Job Charts
                        </a>
                        <a class="nav-link" href="tesda_chart.php">
                            <div class="sb-nav-link-icon"><i class="fas fa-chart-area"></i></div>
                            Tesda Charts
                        </a>
                        <div class="sb-sidenav-menu-heading">Lists</div>
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
            <main class="container-fluid px-4">
                <h1 class="mt-4 text-light">Activity Logs</h1>

                <div class="card mb-4">
                    <div class="card-body">
                        <!-- Table for displaying logs -->
                        <table class="table table-striped table-bordered">
                            <thead>
                                <tr>
                                    <th>Application ID</th>
                                    <th>Action</th>
                                    <th>Employee Name</th>
                                    <th>Date Uploaded</th>
                                    <th>Date Status Updated</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if ($log_result->num_rows > 0) { ?>
                                    <?php while ($log = $log_result->fetch_assoc()) { ?>
                                        <tr>
                                            <td><?php echo $log['application_id']; ?></td>
                                            <td><?php echo $log['action']; ?></td>
                                            <td><?php echo $log['employee_fName'] . ' ' . $log['employee_lName']; ?></td>
                                            <td><?php echo $log['date_uploaded']; ?></td>
                                            <td><?php echo $log['date_status_updated']; ?></td>
                                            <td>
                                                <!-- Approve button -->
                                                <form method="POST" action="">
                                                    <input type="hidden" name="application_id" value="<?php echo $log['application_id']; ?>">
                                                    <input type="hidden" name="action" value="Approved">
                                                    <button type="submit" class="btn btn-success btn-sm">Approve</button>
                                                </form>
                                                <!-- Decline button -->
                                                <form method="POST" action="">
                                                    <input type="hidden" name="application_id" value="<?php echo $log['application_id']; ?>">
                                                    <input type="hidden" name="action" value="Declined">
                                                    <button type="submit" class="btn btn-warning btn-sm">Decline</button>
                                                </form>
                                                <!-- Delete button (existing) -->
                                                <form method="POST" action="">
                                                    <input type="hidden" name="delete_id" value="<?php echo $log['application_id']; ?>">
                                                    <button type="submit" name="delete_log" class="btn btn-danger btn-sm">Delete</button>
                                                </form>
                                            </td>
                                        </tr>
                                    <?php } ?>
                                <?php } else { ?>
                                    <tr>
                                        <td colspan="6">No logs found</td>
                                    </tr>
                                <?php } ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </main>
        </div>
    </div>

    <!-- Bootstrap Bundle JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="js/scripts.js"></script>
</body>

</html>

<?php
// This part handles the action that should log an entry into `activity_logs`
// Check if it's a POST request
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get the action from the form (approve or decline)
    $id = $_POST['application_id']; // Application ID from form
    $action = $_POST['action']; // Action ('Approved', 'Declined')
    $user_id = $_SESSION['id']; // Get the logged-in HR/admin ID from session

    // Update the application status
    $update_query = "UPDATE certificate SET status = ?, date_status_updated = NOW() WHERE id = ?";
    $stmt = $conn->prepare($update_query);
    $stmt->bind_param('si', $action, $id); // Bind action (Approved/Declined) and application ID

    if ($stmt->execute()) {
        // Log the action in the `activity_logs` table
        $log_action = ($action === 'Approved') ? 'Approved' : 'Declined';
        $log_query = "INSERT INTO activity_logs (application_id, user_id, action, date_uploaded, date_status_updated) 
                      VALUES (?, ?, ?, NOW(), NOW())";
        $stmt_log = $conn->prepare($log_query);
        $stmt_log->bind_param('iis', $id, $user_id, $log_action); // Bind application ID, user ID, and action

        if ($stmt_log->execute()) {
            echo "<script>alert('Action logged successfully.');</script>";
        } else {
            echo "Error inserting log: " . $stmt_log->error;
        }
        $stmt_log->close();
    } else {
        echo "Error updating application: " . $stmt->error;
    }
}
?>