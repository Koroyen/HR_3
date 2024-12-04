<?php
require 'db.php';
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Get form data
    $employee_id = $_POST['employee_id'];
    $task_description = $_POST['task_description'];
    $due_date = $_POST['due_date'];

    // Insert the task into the database
    $insert_task_query = "INSERT INTO tasks (employee_id, task_description, due_date) 
                          VALUES ('$employee_id', '$task_description', '$due_date')";
    
    if ($conn->query($insert_task_query) === TRUE) {
        echo "New task created successfully!";
        header("Location: instructor.php");
        exit;
    } else {
        echo "Error: " . $insert_task_query . "<br>" . $conn->error;
    }
}
?>
