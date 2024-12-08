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

// Check if the task ID is passed through POST
if (isset($_POST['task_id'])) {
    $task_id = $_POST['task_id'];

    // Prepare the SQL query to update the task status to 'complete' (No employee_id check required)
    $query = "UPDATE quizzes SET status = 'complete' WHERE id = ?";
    
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $task_id);

    // Execute the query
    if ($stmt->execute()) {
        // Redirect back to the employee task dashboard with a success message
        $_SESSION['success_message'] = "Task marked as complete!";
        echo "<script>
        alert('success');
        window.location.href = 'employee_tasks.php'; // Redirect to Admin dashboard
        </script>";
    } else {
        // Error occurred, redirect back with an error message
        $_SESSION['error_message'] = "Failed to update the task status.";
        header("Location: employee_tasks.php");
    }

    // Close the statement
    $stmt->close();
} else {
    // If task_id is not set, redirect back to employee tasks page
    $_SESSION['error_message'] = "Invalid task ID.";
    header("Location: employee_tasks.php");
}

// Close the database connection
$conn->close();
