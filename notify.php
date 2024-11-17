<?php
session_start();
require 'db.php';
require 'mail.php'; // Ensure this is the correct file for PHPMailer setup

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Ensure the ID is received
    if (!isset($_POST['id'])) {
        echo "No ID received.";
        exit();
    }

    $id = $_POST['id']; // Fetch the ID submitted from the modal
    $message = $_POST['message']; // Get the message from the form
    $action = $_POST['action']; // Approval or decline action

    // Fetch the email from the `images_coe_birthc`, `hiring`, or `certificate` table
    $query = "
        SELECT email FROM images_coe_birthc WHERE id = ? 
        UNION
        SELECT email FROM hiring WHERE id = ?
        UNION
        SELECT email FROM certificate WHERE id = ?";
    
    $stmt = $conn->prepare($query);
    if (!$stmt) {
        die("Prepare failed: " . $conn->error); // Check for SQL preparation errors
    }
    $stmt->bind_param("iii", $id, $id, $id); // Bind the ID for all three tables
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $email = $row['email']; // Extract the email from the result

        // Track if any table update succeeded
        $update_successful = false;

        // Update the `images_coe_birthc` table
        $update_query_images = "
            UPDATE images_coe_birthc 
            SET status = ?, message = ?, date_status_updated = NOW()
            WHERE id = ?";
        $update_stmt_images = $conn->prepare($update_query_images);
        if (!$update_stmt_images) {
            die("Prepare failed for images_coe_birthc: " . $conn->error);
        }
        $update_stmt_images->bind_param("ssi", $action, $message, $id);
        $update_stmt_images->execute();
        if ($update_stmt_images->affected_rows > 0) {
            $update_successful = true;
        }

        // Update the `hiring` table
        $update_query_hiring = "
            UPDATE hiring 
            SET status = ?, message = ?, date_status_updated = NOW()
            WHERE id = ?";
        $update_stmt_hiring = $conn->prepare($update_query_hiring);
        if (!$update_stmt_hiring) {
            die("Prepare failed for hiring: " . $conn->error);
        }
        $update_stmt_hiring->bind_param("ssi", $action, $message, $id);
        $update_stmt_hiring->execute();
        if ($update_stmt_hiring->affected_rows > 0) {
            $update_successful = true;
        }

        // Update the `certificate` table
        $update_query_certificate = "
            UPDATE certificate 
            SET status = ?, message = ?, date_status_updated = NOW()
            WHERE id = ?";
        $update_stmt_certificate = $conn->prepare($update_query_certificate);
        if (!$update_stmt_certificate) {
            die("Prepare failed for certificate: " . $conn->error);
        }
        $update_stmt_certificate->bind_param("ssi", $action, $message, $id);
        $update_stmt_certificate->execute();
        if ($update_stmt_certificate->affected_rows > 0) {
            $update_successful = true;
        }

        // Check if any updates were successful
        if ($update_successful) {
            // Prepare the email
            $mail->setFrom("mfinance@email.com");
            $mail->addAddress($email);
            $mail->Subject = "Application Status Update";
            $mail->isHTML(true);  // Ensure email content is HTML formatted
            $mail->Body = <<<END
            Your application has been <strong>{$action}</strong>.<br><br>

            Message from the admin: <br><em>{$message}</em><br><br>

            Click <a href="https://hr3.microfinance-solution.com/login.php">here</a> to view the details.
            END;

            // Send the email
            try {
                $mail->send();
                echo "<script>
                    alert('Message sent to the applicant\'s email.');
                    window.location.href = 'employee_job.php'; // Redirect to home page
                </script>";
            } catch (Exception $e) {
                echo "Message could not be sent. Mailer Error: {$mail->ErrorInfo}";
            }
        } else {
            echo "Failed to update any table.";
        }

        // Close all statements
        $update_stmt_images->close();
        $update_stmt_hiring->close();
        $update_stmt_certificate->close();
    } else {
        echo "No email found for this ID.";
    }

    // Close the main statement
    $stmt->close();
} else {
    echo "Invalid request.";
}
?>
