<?php
// Start session
session_start();

// Include the database connection file
require 'db.php';

// Check if the user is logged in
if (!isset($_SESSION['id'])) {
    header("Location: login.php");
    exit();
}

$employee_id = $_SESSION['id']; // Get the employee ID from the session

// Fetch the logged-in employee's name for display in the navbar/footer
$employee_query = "SELECT fName FROM users WHERE id = ?";
$stmt = $conn->prepare($employee_query);
$stmt->bind_param("i", $employee_id);
$stmt->execute();
$employee_result = $stmt->get_result();
$employee = $employee_result->fetch_assoc(); // Fetch employee data as an associative array
$employee_name = $employee['fName']; // Store the first name

// Fetch the tasks (quizzes) from the quizzes table that are assigned by trainers (instructors)
$query = "
    SELECT quizzes.id, quizzes.quiz_title, quizzes.quiz_description, quizzes.due_date, users.fName AS instructor_name
    FROM quizzes
    JOIN users ON quizzes.instructor_id = users.id
    ORDER BY quizzes.due_date ASC"; // Modify query as needed if you have a relation between trainers and employees

$result = $conn->query($query);

// Check if there are results and store them in an array
$tasks = [];
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $tasks[] = $row; // Store each row of task data into the $tasks array
    }
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
    <title>Employee Task Management</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" />
    <link href="https://cdn.jsdelivr.net/npm/simple-datatables@7.1.2/dist/style.min.css" rel="stylesheet" />
    <link href="css/styles.css" rel="stylesheet" />
    <script src="https://use.fontawesome.com/releases/v6.3.0/js/all.js" crossorigin="anonymous"></script>
</head>

<body class="sb-nav-fixed">
    <nav class="sb-topnav navbar navbar-expand navbar-dark bg-dark">
        <!-- Navbar Brand-->
        <a class="navbar-brand ps-3" href="index.html">Microfinance</a>
        <!-- Sidebar Toggle-->
        <button class="btn btn-link btn-sm order-1 order-lg-0 me-4 me-lg-0" id="sidebarToggle" href="#!"><i class="fas fa-bars"></i></button>
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
                        <div class="sb-sidenav-menu-heading">Employee Dashboard</div>
                        <a class="nav-link" href="employee_job.php">
                            <div class="sb-nav-link-icon"><i class="fas fa-chart-area"></i></div>
                            Job applications
                        </a>
                       

                        <div class="sb-sidenav-menu-heading">Notification</div>
                        <!-- Messages -->
                        <a class="nav-link" href="messages.php">
                            <div class="sb-nav-link-icon"><i class="fas fa-envelope"></i></div>
                            Messages
                        </a>

                        <!-- Requests -->
                        <a class="nav-link" href="requests.php">
                            <div class="sb-nav-link-icon"><i class="fas fa-file-alt"></i></div>
                            Requests
                        </a>
                        <a class="nav-link" href="take_task.php">
                            <div class="sb-nav-link-icon"><i class="fas fa-file-alt"></i></div>
                            Take task
                        </a>

                        
                    </div>
                </div>
                <div class="sb-sidenav-footer bg-dark">
                    <div class="small">Logged in as:</div>
                    <strong><?php echo htmlspecialchars($employee_name); ?></strong> <!-- Display employee name -->
                </div>
            </nav>
        </div>

        <div id="layoutSidenav_content">
            <main>
                <div class="container-fluid px-4">
                    <h1 class="mt-4">My Tasks</h1>
                    <ol class="breadcrumb mb-4">
                        <li class="breadcrumb-item"><a href="employee_job.php">Dashboard</a></li>
                        <li class="breadcrumb-item active">Tasks</li>
                    </ol>

                    <!-- Employee Tasks Section -->
                    <div class="card mb-4">
                        <div class="card-header">
                            <i class="fas fa-tasks"></i> Task List
                        </div>
                        <div class="card-body">
                            <?php if (count($tasks) > 0): ?>
                                <table id="datatablesSimple" class="table table-bordered">
                                    <thead>
                                        <tr>
                                            <th>Task ID</th>
                                            <th>Title</th>
                                            <th>Description</th>
                                            <th>Instructor</th>
                                            <th>Due Date</th>
                                            <th>Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($tasks as $task): ?>
                                            <tr>
                                                <td><?php echo htmlspecialchars($task['id']); ?></td>
                                                <td><?php echo htmlspecialchars($task['quiz_title']); ?></td>
                                                <td><?php echo htmlspecialchars($task['quiz_description']); ?></td>
                                                <td><?php echo htmlspecialchars($task['instructor_name']); ?></td>
                                                <td><?php echo htmlspecialchars($task['due_date']); ?></td>
                                                <td>
                                                    <!-- Button to Mark Task Completed or Respond -->
                                                    <form method="post" action="update_task.php">
                                                        <input type="hidden" name="task_id" value="<?php echo htmlspecialchars($task['id']); ?>" />
                                                        <button type="submit" class="btn btn-success btn-sm" name="complete_task">Complete</button>
                                                    </form>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            <?php else: ?>
                                <p>No tasks available at the moment.</p>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </main>

            <footer class="py-4 bg-light mt-auto">
                <div class="container-fluid px-4">
                    <div class="d-flex align-items-center justify-content-between small">
                        <div class="text-muted">Copyright &copy; Your Website 2024</div>
                        <div>
                            <a href="#">Privacy Policy</a>
                            &middot;
                            <a href="#">Terms & Conditions</a>
                        </div>
                    </div>
                </div>
            </footer>
        </div>
    </div>

    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/simple-datatables@7.1.2/dist/umd/simple-datatables.min.js"></script>
    <script src="js/scripts.js"></script>
    <script>
        const datatablesSimple = document.getElementById('datatablesSimple');
        if (datatablesSimple) {
            new simpleDatatables.DataTable(datatablesSimple);
        }
    </script>
</body>

</html>
