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
<meta charset="utf-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />
    <meta name="description" content="" />
    <meta name="author" content="" />
    <title>Messages</title>
    <link href="https://cdn.jsdelivr.net/npm/simple-datatables@7.1.2/dist/style.min.css" rel="stylesheet" />
    <link href="css/styles.css" rel="stylesheet" />
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://use.fontawesome.com/releases/v6.3.0/js/all.js" crossorigin="anonymous"></script>

</head>

<body>
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
                    <div class="sb-sidenav-menu-heading">Employee Dashboard</div>

                        <a class="nav-link" href="employee_job.php">
                            <div class="sb-nav-link-icon"><i class="fas fa-chart-area"></i></div>
                            Job applications
                        </a>
                        

                        <div class="sb-sidenav-menu-heading">Message</div>
                      
                        <!-- Requests -->
                        <a class="nav-link" href="requests.php">
                            <div class="sb-nav-link-icon"><i class="fas fa-file-alt"></i></div>
                            Requests
                        </a>

                         <!-- Messages -->
                         <a class="nav-link" href="messages.php">
                            <div class="sb-nav-link-icon"><i class="fas fa-envelope"></i></div>
                            Messages
                        </a>
                        <a class="nav-link" href="task_answer.php">
                            <div class="sb-nav-link-icon"><i class="fas fa-file-alt"></i></div>
                            Task
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

    
    <!-- Bootstrap Bundle JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="js/scripts.js"></script>
    
</body>

</html>