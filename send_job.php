<?php
session_start();
require 'db.php';
require 'mail.php'; // Ensure this has the correct PHPMailer setup

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Check if the message is received
    if (!isset($_POST['message'])) {
        echo "Message not received.";
        exit();
    }

    $message = $_POST['message']; // Get the message from the form
    $hr_manager_id = $_SESSION['id']; // HR Manager's ID from session

    // Get the instructor's email (role = 3)
    $query = "SELECT id, email FROM users WHERE role = 3";
    $result = $conn->query($query);

    if ($result->num_rows > 0) {
        $instructor = $result->fetch_assoc();
        $instructor_id = $instructor['id'];
        $instructor_email = $instructor['email'];

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

        // Insert the job message into the job table
        $insert_query = "
            INSERT INTO job (hr_manager_id, instructor_id, message, file_path, date_sent)
            VALUES (?, ?, ?, ?, NOW())";
        $insert_stmt = $conn->prepare($insert_query);
        if (!$insert_stmt) {
            die("Prepare failed for job insertion: " . $conn->error);
        }
        $insert_stmt->bind_param("iiss", $hr_manager_id, $instructor_id, $message, $file_path);
        $insert_stmt->execute();

        // Check if the job message was successfully inserted
        if ($insert_stmt->affected_rows > 0) {
            // Prepare the email using PHPMailer
            $mail->setFrom("mfinance193@gmail.com", "HR Manager");
            $mail->addAddress($instructor_email); // Send email to the Instructor
            $mail->Subject = "New Message from HR Manager";
            $mail->isHTML(true);  // Set email format to HTML
            $mail->Body = <<<END
            You have received a new message from the HR manager.<br><br>

            Message: <br><em>{$message}</em><br><br>

           
            END;

            // Attach the file if it exists
            if (!empty($file_path)) {
                $mail->addAttachment($file_path);
            }

            // Send the email
            try {
                $mail->send();
                echo "<script>
                    alert('Message sent to the instructor\'s email.');
                    window.location.href = 'job.php'; // Redirect back to the job form page
                </script>";
            } catch (Exception $e) {
                echo "Message could not be sent. Mailer Error: {$mail->ErrorInfo}";
            }
        } else {
            echo "Failed to insert job message into the database.";
        }

        // Close the insert statement
        $insert_stmt->close();
    } else {
        echo "No instructor found.";
    }

    // Close the result
    $result->close();
} else {
    echo "Invalid request.";
}
?>
