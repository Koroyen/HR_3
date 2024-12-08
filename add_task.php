<?php
require 'db.php';
require 'mail.php'; // Include email notification setup

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Get form data
    $employee_id = $_POST['employee_id'];
    $task_description = $_POST['task_description'];
    $due_date = $_POST['due_date'];

    // Insert the task into the database
    $insert_task_query = "INSERT INTO tasks (employee_id, task_description, due_date, created_at) 
                          VALUES (?, ?, ?, NOW())";
    $stmt = $conn->prepare($insert_task_query);
    $stmt->bind_param("iss", $employee_id, $task_description, $due_date);

    if ($stmt->execute()) {
        // Fetch employee email for notification
        $email_query = "SELECT email FROM users WHERE id = ?";
        $email_stmt = $conn->prepare($email_query);
        $email_stmt->bind_param("i", $employee_id);
        $email_stmt->execute();
        $email_result = $email_stmt->get_result();
        $employee = $email_result->fetch_assoc();
        $employee_email = $employee['email'];
        $email_stmt->close();

        // Set up email notification
        $mail->addAddress($employee_email);
        $mail->Subject = "New Task Assigned";
        $mail->Body = "Hello, you have been assigned a new task: $task_description.\nDue Date: $due_date.";
        $mail->send(); // Send email

        // Alert and redirect to task.php
        echo "<script>
                alert('New task created and email sent successfully!');
                window.location.href = 'task.php';
              </script>";
        exit;
    } else {
        echo "Error: " . $insert_task_query . "<br>" . $conn->error;
    }
    $stmt->close();
}
?>
