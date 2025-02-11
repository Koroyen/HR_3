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
    SELECT fName, lName
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
    SELECT qq.id AS question_id, qq.question, qq.options 
    FROM quiz_questions qq 
    WHERE qq.quiz_id = ?
");
$questionsStmt->bind_param("i", $quiz_id);
$questionsStmt->execute();
$questionsResult = $questionsStmt->get_result();

// Handle quiz form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $answers = $_POST['answers']; // This will be an array of question_id => chosen_option

    foreach ($answers as $question_id => $chosen_option) {
        // Insert employee's answer into the quiz_answers table
        $stmt = $conn->prepare("
            INSERT INTO quiz_answers (employee_id, quiz_id, question_id, chosen_option) 
            VALUES (?, ?, ?, ?)
            ON DUPLICATE KEY UPDATE chosen_option = VALUES(chosen_option)
        ");
        $stmt->bind_param("iiii", $employee_id, $quiz_id, $question_id, $chosen_option);
        $stmt->execute();
    }

    // Mark progress as completed
    $progressStmt = $conn->prepare("
        INSERT INTO progress (employee_id, quiz_id, progress_status) 
        VALUES (?, ?, 'completed') 
        ON DUPLICATE KEY UPDATE progress_status = 'completed'
    ");
    $progressStmt->bind_param("ii", $employee_id, $quiz_id);
    $progressStmt->execute();

    // Redirect to employee job page after submitting
    header('Location: task_answer.php');
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
    <title>Take Quiz - Employee Task Management</title>
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
                    <strong><?php echo htmlspecialchars($user['fName']) . " " . htmlspecialchars($user['lName']); ?></strong>
                </div>
            </nav>
        </div>

        <div id="layoutSidenav_content" class="bg-dark">
            <main>
                <div class="container-fluid px-4 text-light">
                    <div class="row justify-content-center">
                        <div class="col-lg-8">
                            <h1 class="mt-4 text-center "><?php echo htmlspecialchars($quiz['quiz_title']); ?></h1>
                            <p class="text-center"><?php echo htmlspecialchars($quiz['quiz_description']); ?></p>
                            <p class="text-center"><small class="text-muted">Due Date: <?php echo htmlspecialchars($quiz['due_date']); ?></small></p>

                            <?php if ($questionsResult->num_rows > 0): ?>
                                <form method="POST" action="take_task.php?quiz_id=<?php echo $quiz_id; ?>" class="mt-4">
                                    <?php while ($question = $questionsResult->fetch_assoc()): ?>
                                        <div class="mb-4">
                                            <p class="fw-bold"><?php echo htmlspecialchars($question['question']); ?></p>

                                            <?php
                                            // Decode options (assuming JSON format)
                                            $options = json_decode($question['options'], true);
                                            foreach ($options as $index => $option):
                                            ?>
                                                <div class="form-check text-start">
                                                    <input class="form-check-input" type="radio" name="answers[<?php echo $question['question_id']; ?>]" value="<?php echo $index; ?>" required>
                                                    <label class="form-check-label"><?php echo htmlspecialchars($option); ?></label>
                                                </div>
                                            <?php endforeach; ?>
                                        </div>
                                    <?php endwhile; ?>

                                    <button type="submit" class="btn btn-success">Submit Answers</button>
                                </form>
                            <?php else: ?>
                                <p>No questions available for this quiz.</p>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </main>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="js/scripts.js"></script>
</body>

</html>
