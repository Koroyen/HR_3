<?php
session_start();
require 'db.php';

// Ensure the user is logged in and is an instructor
if (!isset($_SESSION['id']) || $_SESSION['role'] != 'Trainer') {
    echo "You are not authorized to view requests.";
    exit();
}
$instructor_id = $_SESSION['id'];  // Get instructor ID from session

// Fetch the instructor's first and last name
$name_query = "SELECT first_name, last_name FROM users WHERE id = ?";
$stmt_name = $conn->prepare($name_query);
$stmt_name->bind_param("i", $instructor_id);
$stmt_name->execute();
$result_name = $stmt_name->get_result();

if ($result_name->num_rows > 0) {
    $instructor = $result_name->fetch_assoc();
    $instructor_name = $instructor['first_name'] . ' ' . $instructor['last_name'];  // Concatenate first and last name
} else {
    $instructor_name = 'Unknown';  // Fallback in case the instructor is not found
}
$stmt_name->close();

// Fetch all employees (role = 2) with their emails, progress, and quiz descriptions
$query = "
    SELECT u.id, u.first_name, u.last_name, u.email, 
           p.quiz_id, p.progress_status, q.quiz_description
    FROM users u
    LEFT JOIN progress p ON u.id = p.employee_id
    LEFT JOIN quizzes q ON p.quiz_id = q.id
    WHERE u.role = 2
";
$result = $conn->query($query);


?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />
    <meta name="description" content="" />
    <meta name="author" content="" />
    <title>Employee List</title>
    <link href="https://cdn.jsdelivr.net/npm/simple-datatables@7.1.2/dist/style.min.css" rel="stylesheet" />
    <link href="css/styles.css" rel="stylesheet" />
    <script src="https://use.fontawesome.com/releases/v6.3.0/js/all.js" crossorigin="anonymous"></script>
</head>

<body class="sb-nav-fixed">
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
                    <div class="small">Logged in as:</div>
                    <strong><?php echo htmlspecialchars($instructor_name); ?></strong>
                </div>
            </nav>
        </div>

        <div id="layoutSidenav_content" class="bg-dark">
            <main>
                <div class="container-fluid px-4">
                    <h1 class="mt-4 text-light">Employee List</h1>
                    <div class="card mb-4">
                        <div class="card-header">
                            <i class="fas fa-table me-1"></i>
                            List of Employees
                        </div>
                        <div class="card-body">
                            <table class="table table-striped">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>First Name</th>
                                        <th>Last Name</th>
                                        <th>Email</th>
                                        <th>Progress</th>
                                        <th>Task Description</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    if ($result->num_rows > 0) {
                                        while ($row = $result->fetch_assoc()) {
                                            echo "<tr>";
                                            echo "<td>" . htmlspecialchars($row['id']) . "</td>";
                                            echo "<td>" . htmlspecialchars($row['first_name']) . "</td>";
                                            echo "<td>" . htmlspecialchars($row['last_name']) . "</td>";
                                            echo "<td>" . htmlspecialchars($row['email']) . "</td>";
                                            
                                            // Display progress status
                                            if ($row['progress_status']) {
                                                echo "<td>" . htmlspecialchars($row['progress_status']) . "</td>";
                                            } else {
                                                echo "<td>No Progress Yet</td>";
                                            }

                                            // Display quiz description
                                            if ($row['quiz_description']) {
                                                echo "<td>" . htmlspecialchars($row['quiz_description']) . "</td>";
                                            } else {
                                                echo "<td>No Task Assigned</td>";
                                            }
                                            
                                            echo "</tr>";
                                        }
                                    } else {
                                        echo "<tr><td colspan='6'>No employees found.</td></tr>";
                                    }
                                    ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </main>
            <footer class="py-4 bg-light mt-auto bg-dark">
                <div class="container-fluid px-4">
                    <div class="d-flex align-items-center justify-content-between small">
                        <div class="text-muted">Copyright &copy; Microfinance 2025</div>
                    </div>
                </div>
            </footer>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js" crossorigin="anonymous"></script>
    <script src="js/scripts.js"></script>
</body>

</html>
