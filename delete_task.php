<?php
session_start();
include('db.php');

// Check if task ID is provided via GET
if (isset($_GET['task_id'])) {
    $task_id = $_GET['task_id'];

    // Delete the task from the database
    $delete_query = "DELETE FROM tasks WHERE id = ?";
    $stmt = $conn->prepare($delete_query);
    $stmt->bind_param("i", $task_id);

    if ($stmt->execute()) {
        echo "<script>alert('Task deleted successfully!'); window.location.href = 'task.php';</script>";
    } else {
        echo "<script>alert('Error deleting task!'); window.location.href = 'task.php';</script>";
    }
} else {
    echo "<script>alert('No task ID provided!'); window.location.href = 'task.php';</script>";
}
?>
