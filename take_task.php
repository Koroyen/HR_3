<?php
// Include your database connection and session handling
include 'db.php';
session_start();

if (!isset($_SESSION['id'])) {
    header('Location: login.php');
    exit();
}

$employee_id = $_SESSION['id'];

// Fetch employee's first and last name
$userStmt = $conn->prepare("
    SELECT first_name, last_name
    FROM users
    WHERE id = ?
");
$userStmt->bind_param("i", $employee_id);
$userStmt->execute();
$user = $userStmt->get_result()->fetch_assoc();

if (!$user) {
    echo "Error fetching user details.";
    exit();
}

if (!isset($_GET['quiz_id'])) {
    header('Location: employee_job.php'); // Redirect to employee job page if no quiz is selected
    exit();
}

$quiz_id = $_GET['quiz_id'];

// Fetch the quiz details
$quizStmt = $conn->prepare("
    SELECT quiz_title, quiz_description, due_date 
    FROM quizzes 
    WHERE id = ?
");
$quizStmt->bind_param("i", $quiz_id);
$quizStmt->execute();
$quiz = $quizStmt->get_result()->fetch_assoc();

if (!$quiz) {
    echo "Invalid quiz.";
    exit();
}

// Fetch the questions for the selected quiz
$questionsStmt = $conn->prepare("
    SELECT qq.id AS question_id, qq.question, qq.options, qq.correct_option 
    FROM quiz_questions qq 
    WHERE qq.quiz_id = ?
");
$questionsStmt->bind_param("i", $quiz_id);
$questionsStmt->execute();
$questionsResult = $questionsStmt->get_result();

// Initialize variables
$score = null; // This will store the score once answers are submitted

// Handle quiz form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $answers = $_POST['answers']; // This will be an array of question_id => chosen_option
    $totalQuestions = 0;
    $correctAnswers = 0;

    foreach ($answers as $question_id => $chosen_option) {
        // Fetch the correct option for this question
        $correctStmt = $conn->prepare("
            SELECT correct_option 
            FROM quiz_questions 
            WHERE id = ?
        ");
        $correctStmt->bind_param("i", $question_id);
        $correctStmt->execute();
        $correct = $correctStmt->get_result()->fetch_assoc()['correct_option'];

        // Check if the chosen option is correct
        if ($correct == $chosen_option) {
            $correctAnswers++;
        }
        $totalQuestions++;

        // Insert employee's answer into the quiz_answers table
        $stmt = $conn->prepare("
            INSERT INTO quiz_answers (employee_id, quiz_id, question_id, chosen_option) 
            VALUES (?, ?, ?, ?)
            ON DUPLICATE KEY UPDATE chosen_option = VALUES(chosen_option)
        ");
        $stmt->bind_param("iiii", $employee_id, $quiz_id, $question_id, $chosen_option);
        $stmt->execute();
    }

    // Calculate the score
    $score = ($totalQuestions > 0) ? ($correctAnswers / $totalQuestions) * 100 : 0;

    // Mark progress as completed
    $progressStmt = $conn->prepare("
        INSERT INTO progress (employee_id, quiz_id, progress_status) 
        VALUES (?, ?, 'completed') 
        ON DUPLICATE KEY UPDATE progress_status = 'completed'
    ");
    $progressStmt->bind_param("ii", $employee_id, $quiz_id);
    $progressStmt->execute();
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

<body class="sb-nav-fixed bg-dark">
    <nav class="sb-topnav navbar navbar-expand navbar-dark bg-dark">
        <a class="navbar-brand ps-3" href="employee_job.php">Microfinance</a>
        <button class="btn btn-link btn-sm order-1 order-lg-0 me-4 me-lg-0" id="sidebarToggle" href="#"><i class="fas fa-bars"></i></button>

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
            <nav class="sb-sidenav accordion sb-sidenav-dark bg-dark" id="sidenavAccordion">
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
                    <div class="small">Logged in as:</div>
                    <strong><?php echo htmlspecialchars($user['first_name']) . " " . htmlspecialchars($user['last_name']); ?></strong>
                </div>
            </nav>
        </div>

        <div id="layoutSidenav_content" class="bg-dark text-light">
    <main class="d-flex justify-content-center align-items-center vh-95">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-lg-8">
                    <h1 class="mt-4 text-center"><?php echo htmlspecialchars($quiz['quiz_title']); ?></h1>
                    <p class="text-center"><?php echo htmlspecialchars($quiz['quiz_description']); ?></p>
                    <p class="text-center"><small class="text-muted">Due Date: <?php echo htmlspecialchars($quiz['due_date']); ?></small></p>

                    <?php if ($score === null): ?>
                        <?php if ($questionsResult->num_rows > 0): ?>
                            <form method="POST" action="take_task.php?quiz_id=<?php echo $quiz_id; ?>" class="mt-4">
                                <?php while ($question = $questionsResult->fetch_assoc()): ?>
                                    <div class="mb-4">
                                        <div class="d-flex flex-column align-items-start">
                                            <p class="fw-bold text-break" style="min-width: 150px;"><?php echo htmlspecialchars($question['question']); ?></p>
                                            <?php
                                            $options = json_decode($question['options'], true);
                                            ?>
                                            <div class="d-flex flex-column" style="margin-left: 20px;">
                                                <?php foreach ($options as $index => $option): ?>
                                                    <div class="form-check mb-2 d-flex align-items-center" style="width: 100%;">
                                                        <input class="form-check-input me-3" style="flex-shrink: 0; width: 1.5em; height: 1.5em;" type="radio" name="answers[<?php echo $question['question_id']; ?>]" value="<?php echo $index; ?>" required>
                                                        <label class="form-check-label text-break" style="line-height: 1.5;">
                                                            <?php echo htmlspecialchars($option); ?>
                                                        </label>
                                                    </div>
                                                <?php endforeach; ?>
                                            </div>
                                        </div>
                                    </div>
                                <?php endwhile; ?>
                                <div class="text-center">
                                    <button type="submit" class="btn btn-success">Submit Answers</button>
                                </div>
                            </form>
                        <?php else: ?>
                            <p class="text-center">No questions available for this quiz.</p>
                        <?php endif; ?>
                    <?php else: ?>
                        <div class="alert alert-success text-center">
                            <h2>Your Score: <?php echo round($score, 2); ?>%</h2>
                        </div>
                        <div class="text-center">
                            <a href="task_answer.php" class="btn btn-primary">Go back to Job Dashboard</a>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
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

<!-- Policies Modal -->
<div class="modal fade bg-dark " id="policiesModal" tabindex="-1" aria-labelledby="policiesModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content bg-dark text-light">
            <div class="modal-header">
                <h5 class="modal-title" id="policiesModalLabel">HR Department Policies</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <h6>Policy 1: Equal Employment Opportunity</h6>
                <p>We ensure that all applicants are treated fairly without regard to race, gender, nationality, or religion during the hiring process.</p>
                <hr>
                <h6>Policy 2: Confidentiality of Applicant Information</h6>
                <p>All personal information submitted by applicants is strictly confidential and will not be shared without the applicant's consent.</p>
                <hr>
                <h6>Policy 3: Non-Discrimination</h6>
                <p>The HR Department adheres to a non-discriminatory hiring policy that ensures applicants are selected based on qualifications and merit.</p>
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
