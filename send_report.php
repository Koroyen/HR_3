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
        $insert_query = "
            INSERT INTO reports (hr_manager_id, instructor_id, message, file_path, date_sent)
            VALUES (?, ?, ?, ?, NOW())";
        $insert_stmt = $conn->prepare($insert_query);
        if (!$insert_stmt) {
            die("Prepare failed for report insertion: " . $conn->error);
        }
        $insert_stmt->bind_param("iiss", $hr_manager_id, $instructor_id, $message, $file_path);
        $insert_stmt->execute();

        // Check if the report was successfully inserted
        if ($insert_stmt->affected_rows > 0) {
            // Prepare the email using PHPMailer
            $mail->setFrom("mfinance193@gmail.com", "From Instructor");
            $mail->addAddress($hr_manager_email); // Send email to the HR manager
            $mail->Subject = "New Report from Instructor";
            $mail->isHTML(true);  // Set email format to HTML
            $mail->Body = <<<END
            You have received a new report from an instructor.<br><br>

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
                    alert('Report sent to the HR manager\'s email.');
                    window.location.href = 'report_app.php'; // Redirect back to the report form page
                </script>";
            } catch (Exception $e) {
                echo "Message could not be sent. Mailer Error: {$mail->ErrorInfo}";
            }
        } else {
            echo "Failed to insert report into the database.";
        }

        // Close the insert statement
        $insert_stmt->close();
    } else {
        echo "No HR manager found.";
    }

    // Close the result
    $result->close();
} else {
    echo "Invalid request.";
}
?>
