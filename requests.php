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
// Fetch available instructors
$instructors = $conn->query("SELECT id, fName, lName FROM users WHERE role = 3"); // Assuming role 3 is for instructors

$conn->close(); // Close the database connection
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>Requests </title>
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
                        <a class="nav-link" href="messages.php">
                            <div class="sb-nav-link-icon"><i class="fas fa-envelope"></i></div>
                            Messages
                        </a>
                        <a class="nav-link" href="requests.php">
                            <div class="sb-nav-link-icon"><i class="fas fa-envelope"></i></div>
                            Requests
                        </a>
                    </div>
                </div>
                <div class="sb-sidenav-footer bg-dark">
                    <div class="small">Logged in as: <?php echo htmlspecialchars($_SESSION['user_name']); ?></div>
                </div>
            </nav>
        </div>

        <!-- Main Content Area for Request Form -->
        <div id="layoutSidenav_content">
            <div class="container mt-4">
                <h2>Send Request to Instructor</h2>
                <form action="send_feedback.php" method="POST">
                    <div class="mb-3">
                        <label for="instructorSelect" class="form-label">Select Instructor:</label>
                        <select id="instructorSelect" class="form-select" name="instructor_id" required>
                            <option value="">-- Select Instructor --</option>
                            <?php
                            while ($row = $instructors->fetch_assoc()) {
                                echo "<option value='{$row['id']}'>{$row['fName']} {$row['lName']}</option>";
                            }
                            ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="message" class="form-label">Message:</label>
                        <textarea id="message" class="form-control" name="message" rows="5" required></textarea>
                    </div>
                    <button type="submit" class="btn btn-primary">Send Request</button>
                </form>
            </div>
        </div>
    </div>

    <!-- Include Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js" crossorigin="anonymous"></script>
</body>

</html>
