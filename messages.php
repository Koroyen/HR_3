<?php
session_start(); // Start the session at the top of the file

// Check if the user is logged in by verifying if 'id' and 'role' are set
if (!isset($_SESSION['id']) || $_SESSION['role'] != 2) {
    // Redirect to login page if not logged in or if the role is not employee
    header("Location: login.php");
    exit(); // Stop further execution
}

// Now that we know the user is logged in, fetch the user ID
$user_id = $_SESSION['id']; // Logged-in user's ID

require 'db.php';

// SQL query to fetch messages for the logged-in user
$sql = "SELECT users.fName, users.lName, feedback.message, feedback.date_sent 
        FROM feedback 
        JOIN users ON feedback.instructor_id = users.id 
        WHERE feedback.employee_id = ? 
        ORDER BY feedback.date_sent DESC";

$messages = []; // Array to hold fetched messages
if ($stmt = $conn->prepare($sql)) {
    $stmt->bind_param("i", $user_id); // Bind the logged-in user's ID to the query
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
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>Messages</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/css/bootstrap.min.css" rel="stylesheet" crossorigin="anonymous">
    <link href="css/styles.css" rel="stylesheet">
</head>

<body>
    <!-- Top Navbar -->
    <nav class="sb-topnav navbar navbar-expand navbar-dark bg-dark">
        <a class="navbar-brand ps-3" href="employee.php">Profile Dashboard</a>
        <ul class="navbar-nav ms-auto me-4">
            <li class="nav-item dropdown">
                <a class="nav-link dropdown-toggle" id="navbarDropdown" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false"></a>
                <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="navbarDropdown">
                    <li><a class="dropdown-item" href="logout.php">Logout</a></li>
                    <li><a class="dropdown-item text-muted" href="employee.php">Profile</a></li>
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
                        <div class="sb-sidenav-menu-heading">Interface</div>

                        <a class="nav-link" href="employee_scholar.php">
                            <div class="sb-nav-link-icon"><i class="fas fa-chart-area"></i></div>
                            Scholarship Applications
                        </a>
                        <a class="nav-link" href="employee_job.php">
                            <div class="sb-nav-link-icon"><i class="fas fa-chart-area"></i></div>
                            Job Applications
                        </a>
                        <a class="nav-link" href="employee_tesda.php">
                            <div class="sb-nav-link-icon"><i class="fas fa-chart-area"></i></div>
                            Tesda Applications
                        </a>
                        <div class="sb-sidenav-menu-heading">Notification</div>
                        <!-- Requests -->
                        <a class="nav-link" href="requests.php">
                            <div class="sb-nav-link-icon"><i class="fas fa-file-alt"></i></div>
                            Requests
                        </a>
                    </div>
                </div>
                <div class="sb-sidenav-footer bg-dark">
                    <div class="small">Logged in as: <?php echo htmlspecialchars($_SESSION['user_name']); ?></div>
                </div>
            </nav>
        </div>

        <!-- Main Content Area for Messages -->
        <div id="layoutSidenav_content">
            <div class="container mt-4">
                <h2>Messages</h2>
                <div class="list-group">
                    <?php if (!empty($messages)): ?>
                        <?php foreach ($messages as $msg): ?>
                            <div class="list-group-item">
                                <h5>From: <?php echo htmlspecialchars($msg['fName']) . " " . htmlspecialchars($msg['lName']); ?></h5>
                                <p><?php echo htmlspecialchars($msg['message']); ?></p>
                                <small>Sent on: <?php echo htmlspecialchars($msg['date_sent']); ?></small>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div class="alert alert-info">No messages to display.</div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</body>

</html>