<?php
// Start the session
session_start();

// Include the database connection file
require 'db.php';

// Check if the user is logged in
if (!isset($_SESSION['id'])) {
    header("Location: login.php");
    exit();
}
// Check for success message in the query string
if (isset($_GET['success']) && $_GET['success'] == 1) {
    echo '<div class="alert alert-success" role="alert">Quiz and questions added successfully!</div>';
}

$instructor_id = $_SESSION['id']; // Get instructor ID from session

// Fetch the instructor's first and last name
$name_query = "SELECT fName, lName FROM users WHERE id = ?";
$stmt_name = $conn->prepare($name_query);
if ($stmt_name === false) {
    die('Error preparing statement: ' . $conn->error); // Check for errors
}
$stmt_name->bind_param("i", $instructor_id);
$stmt_name->execute();
$result_name = $stmt_name->get_result();

if ($result_name->num_rows > 0) {
    $instructor = $result_name->fetch_assoc();
    $instructor_name = $instructor['fName'] . ' ' . $instructor['lName']; // Concatenate first and last name
} else {
    $instructor_name = 'Unknown'; // Fallback in case the instructor is not found
}
$stmt_name->close();

// Handle quiz (task) creation
if (isset($_POST['create_task'])) {
    $title = $_POST['title'];
    $description = $_POST['description'];
    $due_date = $_POST['due_date'];

    // Insert the quiz into the quizzes table, ensuring instructor_id is passed
    $quiz_query = "INSERT INTO quizzes (quiz_title, quiz_description, due_date, instructor_id) VALUES (?, ?, ?, ?)";
    $stmt_quiz = $conn->prepare($quiz_query);
    if ($stmt_quiz === false) {
        die('Error preparing quiz query: ' . $conn->error);
    }
    $stmt_quiz->bind_param("sssi", $title, $description, $due_date, $instructor_id);
    if ($stmt_quiz->execute()) {
        $quiz_id = $conn->insert_id; // Get the ID of the created quiz

        // Insert questions and answers
        foreach ($_POST['questions'] as $question_index => $question) {
            $question_text = $question['question'];
            $options = json_encode($question['answers']); // Encode options as JSON
            $correct_option = $question['correct_option']; // The correct answer index

            // Insert the question into quiz_questions table
            $question_query = "INSERT INTO quiz_questions (quiz_id, question, options, correct_option) VALUES (?, ?, ?, ?)";
            $stmt_question = $conn->prepare($question_query);
            if ($stmt_question === false) {
                die('Error preparing question query: ' . $conn->error);
            }
            $stmt_question->bind_param("issi", $quiz_id, $question_text, $options, $correct_option);
            $stmt_question->execute();
        }

        $stmt_question->close();
        echo "Quiz and related questions/answers added successfully!";
    } else {
        echo "Failed to add quiz.";
    }

    $stmt_quiz->close();
}

// Fetch existing quizzes (tasks) with a JOIN using the correct column name for instructor_id
$quiz_query = "
    SELECT q.id, q.quiz_title, q.quiz_description, q.due_date 
    FROM quizzes q
    JOIN users u ON u.id = q.instructor_id  -- Linking quizzes to users via instructor_id
    WHERE u.role = 3 AND u.id = ?";
$stmt_quiz = $conn->prepare($quiz_query);
if ($stmt_quiz === false) {
    die('Error preparing quiz query: ' . $conn->error); // Check for errors
}
$stmt_quiz->bind_param("i", $instructor_id); // Make sure $instructor_id is set correctly
$stmt_quiz->execute();
$result_quiz = $stmt_quiz->get_result();
$quizzes = [];

if ($result_quiz->num_rows > 0) {
    while ($quiz = $result_quiz->fetch_assoc()) {
        $quizzes[] = $quiz;
    }
}
$stmt_quiz->close();
?>


<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />
    <meta name="description" content="" />
    <meta name="author" content="" />
    <title>Task Management - Instructor</title>
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
                        <div class="sb-sidenav-menu-heading">Trainer Dashboard</div>
                        <a class="nav-link" href="instructor.php">
                            <div class="sb-nav-link-icon"><i class="fas fa-tachometer-alt"></i></div>
                            Dashboard
                        </a>
                        <a class="nav-link" href="task.php">
                            <div class="sb-nav-link-icon"><i class="fas fa-tasks"></i></div>
                            Task
                        </a>
                        <a class="nav-link" href="task.php">
                            <div class="sb-nav-link-icon"><i class="fas fa-tasks"></i></div>
                            Manage Task
                        </a>
                    </div>
                </div>
                <div class="sb-sidenav-footer bg-dark">
                    <div class="small">Logged in as:</div>
                    <strong><?php echo htmlspecialchars($instructor_name); ?></strong>
                </div>
            </nav>
        </div>

        <div id="layoutSidenav_content">
            <main>
                <div class="container-fluid px-4">
                    <h1 class="mt-4">Manage Tasks</h1>

                    <!-- Card for task creation form -->
                    <div class="card mb-4">
                        <div class="card-header">
                            <i class="fas fa-tasks me-1"></i>
                            Create New Task
                        </div>
                        <div class="card-body">
                        <form method="post" action="create_task.php">

                                <div class="row mb-3">
                                    <div class="col-md-6">
                                        <label for="title" class="form-label">Title</label>
                                        <input type="text" id="title" name="title" class="form-control" placeholder="Enter task title" required>
                                    </div>
                                    <div class="col-md-6">
                                        <label for="due_date" class="form-label">Due Date</label>
                                        <input type="date" id="due_date" name="due_date" class="form-control" required>
                                    </div>
                                </div>

                                <div class="mb-3">
                                    <label for="description" class="form-label">Description</label>
                                    <textarea id="description" name="description" class="form-control" rows="3" placeholder="Enter task description" required></textarea>
                                </div>

                                <!-- Questions and Answers -->
                                <div id="questions-container" class="mb-3">
                                    <h5>Add Questions</h5>
                                    <div class="question-block border p-3 mb-3">
                                        <div class="mb-3">
                                            <label class="form-label">Question:</label>
                                            <input type="text" name="questions[0][question]" class="form-control" placeholder="Enter question" required>
                                        </div>
                                        <div class="row">
                                            <div class="col">
                                                <label class="form-label">Answer 1:</label>
                                                <input type="text" name="questions[0][answers][]" class="form-control" placeholder="Enter answer 1" required>
                                            </div>
                                            <div class="col">
                                                <label class="form-label">Answer 2:</label>
                                                <input type="text" name="questions[0][answers][]" class="form-control" placeholder="Enter answer 2" required>
                                            </div>
                                        </div>
                                        <div class="row mt-2">
                                            <div class="col">
                                                <label class="form-label">Correct Answer (Index):</label>
                                                <input type="number" name="questions[0][correct_option]" class="form-control" placeholder="Correct answer index" required>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="mb-3">
                                    <button type="button" id="add-question" class="btn btn-outline-primary">Add Another Question</button>
                                </div>

                                <button type="submit" name="create_task" class="btn btn-success">Create Task</button>
                            </form>
                        </div>
                    </div>

                    <!-- Card for task list -->
                    <div class="card mb-4">
                        <div class="card-header">
                            <i class="fas fa-list me-1"></i>
                            Task List
                        </div>
                        <div class="card-body">
                            <table class="table table-bordered table-striped" id="datatablesSimple">
                                <thead class="table-dark">
                                    <tr>
                                        <th>Title</th>
                                        <th>Description</th>
                                        <th>Due Date</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($quizzes as $quiz): ?>
                                        <tr>
                                            <td><?php echo htmlspecialchars($quiz['quiz_title']); ?></td>
                                            <td><?php echo htmlspecialchars($quiz['quiz_description']); ?></td>
                                            <td><?php echo htmlspecialchars($quiz['due_date']); ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </main>

            <footer class="py-4 bg-light mt-auto">
                <div class="container-fluid px-4">
                    <div class="d-flex align-items-center justify-content-between small">
                        <div class="text-muted">Copyright &copy; Microfinance 2024</div>
                    </div>
                </div>
            </footer>
        </div>
    </div>

    <!-- Bootstrap JS and Datatables Script -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/simple-datatables@7.1.2/dist/umd/simple-datatables.min.js" crossorigin="anonymous"></script>
    <script src="js/scripts.js"></script>

    <script>
        // Add more questions functionality
        let questionIndex = 1;
        document.getElementById('add-question').addEventListener('click', function () {
            const container = document.getElementById('questions-container');
            const newQuestion = document.createElement('div');
            newQuestion.classList.add('question-block', 'border', 'p-3', 'mb-3');
            newQuestion.innerHTML = `
                <div class="mb-3">
                    <label class="form-label">Question:</label>
                    <input type="text" name="questions[${questionIndex}][question]" class="form-control" placeholder="Enter question" required>
                </div>
                <div class="row">
                    <div class="col">
                        <label class="form-label">Answer 1:</label>
                        <input type="text" name="questions[${questionIndex}][answers][]" class="form-control" placeholder="Enter answer 1" required>
                    </div>
                    <div class="col">
                        <label class="form-label">Answer 2:</label>
                        <input type="text" name="questions[${questionIndex}][answers][]" class="form-control" placeholder="Enter answer 2" required>
                    </div>
                </div>
                <div class="row mt-2">
                    <div class="col">
                        <label class="form-label">Correct Answer (Index):</label>
                        <input type="number" name="questions[${questionIndex}][correct_option]" class="form-control" placeholder="Correct answer index" required>
                    </div>
                </div>
            `;
            container.appendChild(newQuestion);
            questionIndex++;
        });
    </script>
</body>

</html>
