<?php
session_start();
require 'db.php'; // Ensure this is still required for session, but no DB queries needed here
require 'mail.php'; // Ensure this has the correct PHPMailer setup

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Check CSRF token
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        die("CSRF token validation failed.");
    }

    // Check if the message and applicant's email are received
    if (!isset($_POST['message']) || !isset($_POST['applicant_email'])) {
        echo "Message or applicant email not received.";
        exit();
    }

    $message = $_POST['message']; // Get the message from the form
    $applicant_email = $_POST['applicant_email']; // Get applicant's email

    // Retrieve the full name of the logged-in user from the session
    $logged_in_user_name = isset($_SESSION['user_name']) ? $_SESSION['user_name'] : '';

    // Prepare the email using PHPMailer
    $mail->setFrom("mfinance193@gmail.com", );
    $mail->addAddress($applicant_email); // Send email to the Applicant
    $mail->Subject = "Notification from Human Resources";
    $mail->isHTML(true);  // Set email format to HTML

    // Update the body to include the user's full name
    $mail->Body = <<<END
    You have received a new notification from Microfinance.<br><br>

    Message: <br><em>{$message}</em><br><br>
    END;

    // Append the sender's name if it exists
    if (!empty($logged_in_user_name)) {
        $mail->Body .= "This notification was sent by: {$logged_in_user_name}<br><br>";
    }

    $mail->Body .= "Thank you,<br>Microfinance Team";

    // Send the email
    try {
        $mail->send();
        echo "<script>
            alert('Notification sent to the applicant\'s email.');
            window.location.href = 'employee_job.php'; // Redirect back to the employee job page
        </script>";
    } catch (Exception $e) {
        echo "Message could not be sent. Mailer Error: {$mail->ErrorInfo}";
    }
} else {
    echo "Invalid request.";
}
?>
