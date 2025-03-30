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
    $message = isset($_POST['message']) ? $_POST['message'] : ''; // Get the message from the form
    $action = isset($_POST['action']) ? $_POST['action'] : ''; // Approval or decline action
    $interview_date = isset($_POST['interview_date']) ? $_POST['interview_date'] : null; // Get the interview date

    // Debug: Output the ID received from the form
    echo "ID received from form: " . htmlspecialchars($id) . "<br>";

    // Check if the action requires an interview date
    if ($action === 'approve' && !$interview_date) {
        echo "Interview date is missing for approval.";
        exit();
    }

    // Fetch the email from the `hiring` table
    $query = "SELECT email FROM hiring WHERE id = ?";
    $stmt = $conn->prepare($query);
    if (!$stmt) {
        die("Prepare failed: " . $conn->error); // Check for SQL preparation errors
    }

    $stmt->bind_param("i", $id); // Bind the ID for the hiring table
    $stmt->execute();
    $result = $stmt->get_result();

    // Debug: Check if the query returns any result
    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $email = $row['email']; // Extract the email from the result

        // Debug: Output the email found
        echo "Email found: " . htmlspecialchars($email) . "<br>";

        // Determine if the action is "decline" and set is_reapplying
        $is_reapplying = ($action === 'Declined') ? 1 : 0; // Set is_reapplying to 1 if declined, otherwise 0

        // Update the `hiring` table
        $update_query_hiring = "
            UPDATE hiring 
            SET status = ?, message = ?, date_status_updated = NOW(), interview_date = ?, is_reapplying = ?
            WHERE id = ?";
        $update_stmt_hiring = $conn->prepare($update_query_hiring);
        if (!$update_stmt_hiring) {
            die("Prepare failed for hiring: " . $conn->error);
        }

        // Allow NULL for interview_date if not set
        if (empty($interview_date)) {
            $interview_date = null; // If interview_date is empty, set it to NULL
        }

        // Debugging output for the values being used in the update
        echo "Status: " . htmlspecialchars($action) . "<br>";
        echo "Message: " . htmlspecialchars($message) . "<br>";
        echo "Interview Date: " . var_export($interview_date, true) . "<br>";
        echo "Is Reapplying: " . htmlspecialchars($is_reapplying) . "<br>";
        echo "ID: " . htmlspecialchars($id) . "<br>";

        // Bind the parameters and execute the query
        $update_stmt_hiring->bind_param("sssii", $action, $message, $interview_date, $is_reapplying, $id);
        $update_stmt_hiring->execute();

        if ($update_stmt_hiring->affected_rows > 0) {
            echo "Update successful! Rows affected: " . $update_stmt_hiring->affected_rows;

            // Prepare the email using PHPMailer
            $mail->setFrom("mfinance193@gmail.com", "Application Update");
            $mail->addAddress($email); // Send email to the user
            $mail->Subject = "Application Status Update";
            $mail->isHTML(true);  // Set email format to HTML

            // Check if interview date is included in the email
            $interview_info = $interview_date ? "<br>Interview Date: <strong>{$interview_date}</strong><br><br>" : '';

            $mail->Body = <<<END
            Your application status has been updated.<br><br>

            Status: <strong>{$action}</strong><br>
            Message: <br><em>{$message}</em><br><br>
            {$interview_info}
            END;

            // Send the email
            try {
                $mail->send();
                echo "<script>
                    alert('Action and email sent successfully.');
                    window.location.href = 'hr_job.php'; // Redirect back to requests page
                </script>";
            } catch (Exception $e) {
                echo "Message could not be sent. Mailer Error: {$mail->ErrorInfo}";
            }
        } else {
            echo "Failed to update the hiring table. Rows affected: " . $update_stmt_hiring->affected_rows;
        }

        // Close the statement
        $update_stmt_hiring->close();
    } else {
        echo "No email found for this hiring ID. Query returned 0 rows.<br>";
        echo "Check if the ID exists in the hiring table and the corresponding record has an email.<br>";
    }

    // Close the main statement
    $stmt->close();
} else {
    echo "Invalid request.";
}
?>