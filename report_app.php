<?php
session_start(); // Start the session at the top of the file

// Check if the user is logged in by verifying if 'id' and 'role' are set
if (!isset($_SESSION['id']) || $_SESSION['role'] != 'Trainer') {
    header("Location: login.php");
    exit(); // Stop further execution
}

// Now that we know the user is logged in, fetch the user ID
$user_id = $_SESSION['id']; // Logged-in user's ID

require 'db.php';
// Fetch all HR managers (assuming role 1 is HR manager)
$hr_managers = $conn->query("SELECT id, first_name, last_name FROM users WHERE role = 'Manager'");

$conn->close(); // Close the database connection

// Include CSRF protection script
require 'csrf_protection.php';

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />
    <meta name="description" content="" />
    <meta name="author" content="" />
    <title>Send Report</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" />
    <link href="https://cdn.jsdelivr.net/npm/simple-datatables@7.1.2/dist/style.min.css" rel="stylesheet" />
    <link href="css/styles.css" rel="stylesheet" />
    <script src="https://use.fontawesome.com/releases/v6.3.0/js/all.js" crossorigin="anonymous"></script>

</head>

<body>
<nav class="sb-topnav navbar navbar-expand navbar-dark bg-dark">
        <!-- Navbar Brand-->
        <a class="navbar-brand ps-3" href="instructor.php">Microfinance</a>
        <!-- Sidebar Toggle-->
        <button class="btn btn-link btn-sm order-1 order-lg-0 me-4 me-lg-0" id="sidebarToggle" href="#!"><i class="fas fa-bars"></i></button>
        <!-- Navbar Search-->
        <form class="d-none d-md-inline-block form-inline ms-auto me-0 me-md-3 my-2 my-md-0">
            <div class="input-group">
              
            </div>
        </form>
        <!-- Navbar-->
        <ul class="navbar-nav ms-auto ms-md-0 me-3 me-lg-4">
            <li class="nav-item dropdown">
                <a class="nav-link dropdown-toggle" id="navbarDropdown" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false"><i class="fas fa-user fa-fw"></i></a>
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
                <div class="sb-sidenav-menu-heading"></div>
                <a class="nav-link" href="instructor.php">
                            <div class="sb-nav-link-icon"><i class="fas fa-tachometer-alt"></i></div>
                            Report Log
                        </a>
                        <a class="nav-link" href="task.php">
                            <div class="sb-nav-link-icon"><i class="fas fa-tasks"></i></div>
                            Task
                        </a>
                        <a class="nav-link" href="quiz.php">
                            <div class="sb-nav-link-icon"><i class="fas fa-book"></i></div>
                            Manage Training
                        </a>
                        <a class="nav-link" href="employee_list.php">
                            <div class="sb-nav-link-icon"><i class="fas fa-users"></i></div>
                            Employee List
                        </a>
                        <a class="nav-link" href="report_app.php">
                            <div class="sb-nav-link-icon"><i class="fas fa-users"></i></div>
                            Report
                        </a>
                        <a class="nav-link" href="list.php">
                            <div class="sb-nav-link-icon"><i class="fas fa-users"></i></div>
                            Applicant List
                        </a>
                    </div>
                </div>
                <div class="sb-sidenav-footer bg-dark">
                    <div class="small">Logged in as: <?php echo htmlspecialchars($_SESSION['user_name']); ?></div>
                </div>
            </nav>
        </div>

        <!-- Main Content Area for Report Form -->
        <div id="layoutSidenav_content" class="bg-dark">
            <div class="container mt-4">
                <h2 class="text-light">Send Message and File to HR Manager</h2>
                <form action="send_report.php" method="POST" enctype="multipart/form-data">
                    <?php csrf_token_field(); ?> <!-- CSRF Token Field -->
                    <div class="mb-3">
                        <label for="hrManager" class="form-label text-light">HR Manager:</label>
                        <select id="hrManager" class="form-select" name="hr_manager_id" required>
                            <option value="">Select HR Manager</option>
                            <?php
                            // Loop through each HR manager and populate the dropdown
                            while ($row = $hr_managers->fetch_assoc()) {
                                echo "<option value='" . $row['id'] . "'>" . $row['first_name'] . " " . $row['last_name'] . "</option>";
                            }
                            ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="message" class="form-label text-light">Message:</label>
                        <textarea id="message" class="form-control" name="message" rows="5" required></textarea>
                    </div>
                    <div class="mb-3">
                        <label for="file" class="form-label text-light">Attach File (optional):</label>
                        <input type="file" id="file" class="form-control" name="attachment" accept=".pdf,.doc,.docx,.xls,.xlsx">
                    </div>
                    <button type="submit" class="btn btn-primary">Send</button>
                </form>
            </div>
            <footer class="py-4 bg-light mt-auto bg-dark">
                <div class="container-fluid px-4">
                    <div class="d-flex align-items-center justify-content-between small">
                        <div class="text-muted">Copyright &copy; Microfinance 2025</div>
                    </div>
                </div>
            </footer>
        </div>

    </div>
    

    <!-- Bootstrap Bundle JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="js/scripts.js"></script>

</body>

</html>
