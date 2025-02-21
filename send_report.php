<?php
session_start();
require 'db.php';
require 'mail.php'; // Ensure this has the correct PHPMailer setup

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Check CSRF token
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        die("CSRF token validation failed.");
    }

    // Check if the message is received
    if (!isset($_POST['message'])) {
        echo "Message not received.";
        exit();
    }

    $message = $_POST['message']; // Get the message from the form
    $instructor_id = $_SESSION['id']; // Instructor's ID from session

    // Get the HR manager's email (role = 1)
    $query = "SELECT id, email FROM users WHERE role = 1";
    $result = $conn->query($query);

    if ($result->num_rows > 0) {
        $hr_manager = $result->fetch_assoc();
        $hr_manager_id = $hr_manager['id'];
        $hr_manager_email = $hr_manager['email'];

        // Initialize file upload variables
        $file_path = '';
        $upload_dir = 'uploads/'; // Directory to store uploaded files (ensure this directory exists and is writable)

        if (!empty($_FILES['attachment']['name'])) {
            $file_name = basename($_FILES['attachment']['name']);
            $file_tmp = $_FILES['attachment']['tmp_name'];
            $file_size = $_FILES['attachment']['size'];
            
            // Define the target file path
            $target_file = $upload_dir . uniqid() . '_' . $file_name;

            // Move the uploaded file to the target directory
            if (move_uploaded_file($file_tmp, $target_file)) {
                $file_path = $target_file;
            } else {
                die("Error uploading the file.");
            }
        }

        // Insert the report into the database
        $stmt = $conn->prepare("INSERT INTO reports (instructor_id, hr_manager_id, message, file_path) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("iiss", $instructor_id, $hr_manager_id, $message, $file_path);
        if ($stmt->execute()) {
            echo "Report successfully sent!";
        } else {
            echo "Error sending report.";
        }

        // Send an email to the HR manager using PHPMailer
        $mail->addAddress($hr_manager_email); // Add the HR manager's email address
        $mail->Subject = "New Report from Instructor";
        $mail->Body = "You have received a new report: \n\nMessage: $message";

        // If a file was uploaded, attach it to the email
        if ($file_path) {
            $mail->addAttachment($file_path);
        }

        // Send the email
        if ($mail->send()) {
            echo "Email sent!";
        } else {
            echo "Error sending email.";
        }
    } else {
        echo "No HR manager found.";
    }

    // Close the database connection
    $conn->close();
}
?>
