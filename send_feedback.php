<?php
session_start();
require 'db.php';
require 'mail.php'; // Ensure this has the correct PHPMailer setup

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Ensure the instructor ID and message are received
    if (!isset($_POST['instructor_id']) || !isset($_POST['message'])) {
        echo "Instructor ID or message not received.";
        exit();
    }

    $instructor_id = $_POST['instructor_id']; // Get the instructor ID from the form (receiver)
    $message = $_POST['message']; // Get the message from the form (feedback content)

    // Fetch the email of the instructor from the `users` table
    $query = "SELECT email FROM users WHERE id = ?";
    $stmt = $conn->prepare($query);
    if (!$stmt) {
        die("Prepare failed: " . $conn->error); // Check for SQL preparation errors
    }
    $stmt->bind_param("i", $instructor_id); // Bind the instructor ID
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $instructor_email = $row['email']; // Extract the email from the result

        // Now, insert the feedback message into the `feedback` table
        $employee_id = $_SESSION['id']; // Assuming the employee's ID is stored in session (sender)
        
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

        // Insert feedback message and file path into the database
        $insert_query = "
            INSERT INTO feedback (instructor_id, employee_id, message, file_path, date_sent)
            VALUES (?, ?, ?, ?, NOW())";  // Add the file_path column for storing the file
        $insert_stmt = $conn->prepare($insert_query);
        if (!$insert_stmt) {
            die("Prepare failed for feedback insertion: " . $conn->error);
        }
        $insert_stmt->bind_param("iiss", $instructor_id, $employee_id, $message, $file_path);
        $insert_stmt->execute();

        // Check if the feedback was successfully inserted
        if ($insert_stmt->affected_rows > 0) {
            // Prepare the email using PHPMailer
            $mail->setFrom("mfinance193@gmail.com", "From Employee");
            $mail->addAddress($instructor_email); // Send email to the instructor
            $mail->Subject = "New Feedback from an Employee";
            $mail->isHTML(true);  // Set email format to HTML
            $mail->Body = <<<END
            You have received new message from an employee.<br><br>

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
                    alert('Feedback sent to the instructor\'s email.');
                    window.location.href = 'requests.php'; // Redirect back to requests page
                </script>";
            } catch (Exception $e) {
                echo "Message could not be sent. Mailer Error: {$mail->ErrorInfo}";
            }
        } else {
            echo "Failed to insert feedback into the database.";
        }

        // Close the insert statement
        $insert_stmt->close();
    } else {
        echo "No email found for this instructor ID.";
    }

    // Close the main statement
    $stmt->close();
} else {
    echo "Invalid request.";
}


?>