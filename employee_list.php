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
    SELECT u.id AS employee_id, u.first_name, u.last_name, u.email, 
           p.quiz_id, p.progress_status, q.quiz_description, qq.question, 
           qq.options, qa.chosen_option, qq.correct_option
    FROM users u
    LEFT JOIN progress p ON u.id = p.employee_id
    LEFT JOIN quizzes q ON p.quiz_id = q.id
    LEFT JOIN quiz_questions qq ON q.id = qq.quiz_id
    LEFT JOIN quiz_answers qa ON qq.id = qa.question_id AND qa.employee_id = u.id
    WHERE u.role = 'Staff'
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
        <a class="navbar-brand ps-3" href="employee_job.php">Ascenders business services</a>
        <button class="btn btn-link btn-sm order-1 order-lg-0 me-4 me-lg-0 p-5" id="sidebarToggle" href="#"><i class="fas fa-bars"></i></button>
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
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            if ($result->num_rows > 0) {
                                $currentEmployeeId = null;
                                $quizData = [];

                                while ($row = $result->fetch_assoc()) {
                                    // Store quiz-related data for the employee
                                    $quizData[$row['employee_id']][] = $row;
                                }

                                // Iterate through employees
                                foreach ($quizData as $employeeId => $quizzes) {
                                    $firstQuiz = $quizzes[0];

                                    echo "<tr>";
                                    echo "<td>" . htmlspecialchars($firstQuiz['employee_id']) . "</td>";
                                    echo "<td>" . htmlspecialchars($firstQuiz['first_name']) . "</td>";
                                    echo "<td>" . htmlspecialchars($firstQuiz['last_name']) . "</td>";
                                    echo "<td>" . htmlspecialchars($firstQuiz['email']) . "</td>";

                                    // Display progress status
                                    echo "<td>" . htmlspecialchars($firstQuiz['progress_status'] ?? 'No Progress Yet') . "</td>";

                                    // View details button
                                    echo '<td><button class="btn btn-info" type="button" data-bs-toggle="collapse" data-bs-target="#details-' . htmlspecialchars($employeeId) . '">View Details</button></td>';
                                    echo "</tr>";

                                    // Quiz details section
                                    echo '<tr><td colspan="6">';
                                    echo '<div id="details-' . htmlspecialchars($employeeId) . '" class="collapse">';
                                    
                                    foreach ($quizzes as $quiz) {
                                        echo '<h5 class="text-light mt-3">Quiz Title: ' . htmlspecialchars($quiz['quiz_description']) . '</h5>';
                                        echo '<table class="table table-sm table-dark">';
                                        echo '<thead><tr><th>Question</th><th>Your Answer</th><th>Correct Answer</th></tr></thead>';
                                        echo '<tbody>';

                                        if ($quiz['question']) {
                                            $options = json_decode($quiz['options'], true);
                                            $user_choice = isset($options[$quiz['chosen_option']]) ? $options[$quiz['chosen_option']] : 'No Answer';
                                            $correct_answer = isset($options[$quiz['correct_option']]) ? $options[$quiz['correct_option']] : 'No Correct Answer';

                                            echo "<tr>";
                                            echo "<td>" . htmlspecialchars($quiz['question']) . "</td>";
                                            echo "<td>" . htmlspecialchars($user_choice) . "</td>";
                                            echo "<td>" . htmlspecialchars($correct_answer) . "</td>";
                                            echo "</tr>";
                                        }

                                        echo '</tbody></table>';
                                    }

                                    echo '</div></td></tr>';
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

            <!-- Apply CSS styles for word-wrap and truncation -->
            <style>
                .table td {
                    word-wrap: break-word;
                    white-space: normal;
                    max-width: 250px;
                    /* Adjust to control how much space each cell gets */
                    overflow: hidden;
                    text-overflow: ellipsis;
                }

                .table td:hover {
                    overflow: visible;
                    white-space: normal;
                    position: relative;
                    z-index: 1;
                }
            </style>
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