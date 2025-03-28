<?php
session_start();
require 'db.php'; // Include database connection

// Check if user is logged in and is an Employee
if (!isset($_SESSION["id"]) || $_SESSION["role"] != 'Staff') {
    header("Location: login.php");
    exit();
}

// Fetch profile data for the logged-in user
$user_id = $_SESSION['id'];
$query = "SELECT first_name, last_name, email, profile_pic FROM users WHERE id = ?";
$stmt = $conn->prepare($query);
if ($stmt === false) {
    die('Error preparing query: ' . $conn->error);
}
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$profile_data = $result->fetch_assoc();
$stmt->close();

$quizzes = $conn->prepare("
    SELECT q.id, q.quiz_title, q.quiz_description, q.due_date, p.progress_status 
    FROM quizzes q 
    LEFT JOIN progress p ON q.id = p.quiz_id AND p.employee_id = ? 
    WHERE q.is_visible = 1
");

if ($quizzes === false) {
    // Output the error for debugging
    die('Error preparing query: ' . $conn->error);
}

$quizzes->bind_param("i", $user_id);  // Use user_id directly
$quizzes->execute();
$result = $quizzes->get_result();

// Store the quizzes in an array
$quizList = [];
while ($row = $result->fetch_assoc()) {
    $quizList[] = $row;
}
$quizzes->close();


// Handle quiz form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['quiz_id'])) {
    $quiz_id = $_POST['quiz_id'];
    $answers = $_POST['answers']; // This will be an array of question_id => chosen_option

    foreach ($answers as $question_id => $chosen_option) {
        // Insert employee's answer into the quiz_answers table
        $stmt = $conn->prepare("
            INSERT INTO quiz_answers (employee_id, quiz_id, question_id, chosen_option) 
            VALUES (?, ?, ?, ?)
            ON DUPLICATE KEY UPDATE chosen_option = VALUES(chosen_option)
        ");
        $stmt->bind_param("iiii", $user_id, $quiz_id, $question_id, $chosen_option);  // user_id is used here
        $stmt->execute();
    }

    // Mark progress as completed
    $progressStmt = $conn->prepare("
        INSERT INTO progress (employee_id, quiz_id, progress_status) 
        VALUES (?, ?, 'completed') 
        ON DUPLICATE KEY UPDATE progress_status = 'completed'
    ");
    $progressStmt->bind_param("ii", $user_id, $quiz_id);
    $progressStmt->execute();

    // Redirect to avoid form resubmission
    header('Location: employee_job.php');
    exit();
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
    <title>Tasks</title>
    <script src="https://cdn.jsdelivr.net/npm/simple-datatables@7.1.2/dist/umd/simple-datatables.min.js"></script>
    <link href="https://cdn.jsdelivr.net/npm/simple-datatables@7.1.2/dist/style.min.css" rel="stylesheet" />
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="css/styles.css" rel="stylesheet" />
    <script src="https://use.fontawesome.com/releases/v6.3.0/js/all.js" crossorigin="anonymous"></script>
</head>

<body class="sb-nav-fixed bg-light">
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
                        <div class="sb-sidenav-menu-heading">Message</div>
                        <a class="nav-link" href="requests.php">
                            <div class="sb-nav-link-icon"><i class="fas fa-file-alt"></i></div>
                            Message
                        </a>
                        <!-- Messages -->
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
                    <div class="small">Logged in as: <?php echo htmlspecialchars($profile_data['first_name']); ?></div>
                </div>
            </nav>
        </div>

        <div id="layoutSidenav_content" class="bg-dark">
            <main>
                <div class="container-fluid px-4 text-light">
                    <h1 class="text-light">Task</h1>

                    <?php if (empty($quizList)): ?>
                        <p>No Task available at the moment.</p>
                    <?php else: ?>
                        <?php foreach ($quizList as $quiz): ?>
                            <div class="card mb-3">
                                <div class="card-body">
                                    <h5 class="card-title">
                                        <a href="take_task.php?quiz_id=<?php echo $quiz['id']; ?>" class="text-white" style="text-decoration: none;">
                                            <?php echo htmlspecialchars($quiz['quiz_title']); ?>
                                        </a>
                                    </h5>
                                    <p class="card-text"><?php echo htmlspecialchars($quiz['quiz_description']); ?></p>
                                    <p class="card-text"><small class="text-muted">Due Date: <?php echo htmlspecialchars($quiz['due_date']); ?></small></p>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>

                    <?php
                    // Display quiz questions if a quiz is selected
                    if (isset($_GET['quiz_id'])) {
                        $quiz_id = $_GET['quiz_id'];

                        // Fetch questions for the selected quiz
                        $questionsStmt = $conn->prepare("
                    SELECT qq.id AS question_id, qq.question, qq.options 
                    FROM quiz_questions qq 
                    WHERE qq.quiz_id = ?
                ");
                        $questionsStmt->bind_param("i", $quiz_id);
                        $questionsStmt->execute();
                        $questionsResult = $questionsStmt->get_result();

                        if ($questionsResult->num_rows > 0) {
                            echo '<form method="POST" action="take_task.php">';
                            echo '<input type="hidden" name="quiz_id" value="' . htmlspecialchars($quiz_id) . '">';

                            while ($question = $questionsResult->fetch_assoc()) {
                                echo '<div class="mb-3">';
                                echo '<p>' . htmlspecialchars($question['question']) . '</p>';

                                // Decode options (assuming JSON format)
                                $options = json_decode($question['options'], true);

                                foreach ($options as $index => $option) {
                                    echo '<div class="form-check">';
                                    echo '<input class="form-check-input" type="radio" name="answers[' . $question['question_id'] . ']" value="' . $index . '" required>';
                                    echo '<label class="form-check-label">' . htmlspecialchars($option) . '</label>';
                                    echo '</div>';
                                }

                                echo '</div>';
                            }

                            echo '<button type="submit" class="btn btn-success">Submit Answers</button>';
                            echo '</form>';
                        } else {
                            echo '<p>No questions available for this task.</p>';
                        }
                    }
                    ?>
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

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="js/scripts.js"></script>
</body>

</html>