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

$instructor_id = $_SESSION['id']; // Get instructor ID from session

// Handle quiz (task) creation
if (isset($_POST['create_task'])) {
    $title = $_POST['title'];
    $description = $_POST['description'];
    $due_date = $_POST['due_date'];

    // Insert the quiz into the quizzes table, including instructor_id
    $quiz_query = "INSERT INTO quizzes (quiz_title, quiz_description, due_date, instructor_id) VALUES (?, ?, ?, ?)";
    $stmt_quiz = $conn->prepare($quiz_query);
    if ($stmt_quiz === false) {
        die('Error preparing quiz query: ' . $conn->error);
    }
    $stmt_quiz->bind_param("sssi", $title, $description, $due_date, $instructor_id); // Bind the instructor_id
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

        // Close statement and redirect to quiz page
        $stmt_question->close();
        echo "<script>
                alert('Create Task success');
                window.location.href = 'quiz.php'; // Redirect to Admin dashboard
                </script>";
    } else {
        echo "Failed to add quiz.";
    }

    $stmt_quiz->close();
} else {
    // Redirect to quiz page if accessed directly without POST request
    header("Location: quiz.php");
    exit();
}
?>
