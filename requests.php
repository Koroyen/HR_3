<?php
session_start(); // Start the session at the top of the file

// Check if the user is logged in by verifying if 'id' and 'role' are set
if (!isset($_SESSION['id']) || $_SESSION['role'] != 'Staff') {
    // Redirect to login page if not logged in or if the role is not employee
    header("Location: login.php");
    exit(); // Stop further execution
}

// Now that we know the user is logged in, fetch the user ID
$user_id = $_SESSION['id']; // Logged-in user's ID

// Generate CSRF token if not already set
if (!isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

require 'db.php';
// Fetch available instructors
$instructors = $conn->query("SELECT id, first_name, last_name FROM users WHERE role = 'Trainer'"); // Assuming role 3 is for instructors

$conn->close(); // Close the database connection
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
    <script src="https://cdn.jsdelivr.net/npm/simple-datatables@7.1.2/dist/umd/simple-datatables.min.js"></script>
    <link href="https://cdn.jsdelivr.net/npm/simple-datatables@7.1.2/dist/style.min.css" rel="stylesheet" />
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="css/styles.css" rel="stylesheet" />
    <script src="https://use.fontawesome.com/releases/v6.3.0/js/all.js" crossorigin="anonymous"></script>
</head>

<body class="d-flex flex-column min-vh-100 sb-nav-fixed bg-light">
    <nav class="sb-topnav navbar navbar-expand navbar-dark bg-dark">
        <a class="navbar-brand ps-3" href="employee_job.php">Ascenders business services</a>
        <button class="btn btn-link btn-sm order-1 order-lg-0 me-4 me-lg-0 p-5" id="sidebarToggle" href="#"><i class="fas fa-bars"></i></button>

        <!-- Right side of navbar (moved dropdown to the far right) -->
        <ul class="navbar-nav ms-auto">
            <li class="nav-item dropdown">
                <a class="nav-link dropdown-toggle" id="navbarDropdown" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                    <i class="fas fa-user fa-fw"></i>
                </a>
                <ul class="dropdown-menu dropdown-menu-end bg-dark" aria-labelledby="navbarDropdown">
                    <li><a class="dropdown-item text-muted" href="logout.php">Logout</a></li>
                    <li><a class="dropdown-item text-muted" href="employee.php">Profile</a></li>
                </ul>
            </li>
        </ul>
    </nav>

    <div id="layoutSidenav" class="d-flex flex-grow-1">
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
                        <a class="nav-link" href="requests.php">
                            <div class="sb-nav-link-icon"><i class="fas fa-file-alt"></i></div>
                            Message
                        </a>
                        <a class="nav-link" href="messages.php">
                            <div class="sb-nav-link-icon"><i class="fas fa-envelope"></i></div>
                            Message Log
                        </a>
                        <a class="nav-link" href="employee_train.php">
                            <div class="sb-nav-link-icon"><i class="fas fa-file-alt"></i></div>
                            Task
                        </a>
                        <a class="nav-link" href="task_answer.php">
                            <div class="sb-nav-link-icon"><i class="fas fa-file-alt"></i></div>
                            Training
                        </a>
                    </div>
                </div>
                <div class="sb-sidenav-footer bg-dark">
                    <div class="small">Logged in as: <?php echo htmlspecialchars($_SESSION['user_name']); ?></div>
                </div>
            </nav>
        </div>

        <!-- Main Content Area for Request Form -->
        <div id="layoutSidenav_content" class="bg-dark d-flex flex-column">
            <main class="flex-grow-1">
                <div class="container mt-4">
                    <h2 class="text-light">Send Message to Trainer</h2>

                    <form action="send_feedback.php" method="POST" enctype="multipart/form-data">
                        <!-- Add CSRF token input -->
                        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token']); ?>">

                        <div class="mb-3">
                            <label for="instructorSelect" class="form-label text-light">Select Trainer:</label>
                            <select id="instructorSelect" class="form-select" name="instructor_id" required>
                                <option value="">-- Select Trainer --</option>
                                <?php
                                while ($row = $instructors->fetch_assoc()) {
                                    echo "<option value='{$row['id']}'>{$row['first_name']} {$row['last_name']}</option>";
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

            </main>

            <footer class="bg-dark text-center py-3 mt-5 text-light">
                <div class="container">
                    <small>Copyright © Microfinance 2025</small><br>
                    <button type="button" class="btn btn-link text-light" data-bs-toggle="modal" data-bs-target="#policiesModal">
                        Policies
                    </button>
                </div>
            </footer>
        </div>
    </div>

    <!-- Policies Modal -->
    <div class="modal fade bg-dark " id="policiesModal" tabindex="-1" aria-labelledby="policiesModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content bg-dark text-light">
                <div class="modal-header">
                    <h5 class="modal-title" id="policiesModalLabel">Recuitment Human Resource Department Policies</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <h6>Applicant Confidentiality</h6>
                    <p>All personnel involved in the hiring process must refrain from discussing or disclosing any applicant information outside of the recruitment process. This includes not sharing information with colleagues, other departments, or external parties.</p>
                    <hr>
                    <h6>Confidentiality of Applicant Information</h6>
                    <p>All personal information submitted by applicants is strictly confidential and will not be shared without the applicant's consent.</p>
                    <hr>
                    <h6>Transparency</h6>
                    <p> If applicants request information about how their data is being used, the HR department will provide clear explanations of the recruitment process, the types of data collected, and how it is safeguarded.</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap Bundle JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="js/scripts.js"></script>

</body>

</html>