<?php 

$email = $_POST['email'];

$token = bin2hex(random_bytes(16));
$token_hash = hash("sha256", $token);

// Set token expiration to 10 minutes from now
$expiry = date("Y-m-d H:i:s", time() + 60 * 10);

$conn = require __DIR__ . "/db.php";

$sql = "UPDATE users
        SET reset_token_hash = ?,
            reset_token_expires_at = ?
        WHERE email = ?";

$stmt = $conn->prepare($sql);
$stmt->bind_param("sss", $token_hash, $expiry, $email);
$stmt->execute();

if ($conn->affected_rows) {
    $mail = require __DIR__ . "/mail.php";
    $mail->setFrom("mfinance@email.com", "MFinance Support Team");
    $mail->addAddress($email);
    $mail->Subject = "Password Reset Request";

    // Dynamically set the domain
    $domain = $_SERVER['HTTP_HOST'];
    
    // Enable HTML in the email body
    $mail->isHTML(true);
    $mail->Body = <<<END
    <p>Dear User,</p>
    <p>We received a request to reset your password for your MFinance account. Please click the link below to proceed with resetting your password:</p>
    <p><a href="http://hr3.microfinance-solution.com/reset-password.php?token=$token">Reset Your Password</a></p>
    <p><strong>Note:</strong> This link is valid for the next 10 minutes. After this time, you will need to request a new password reset.</p>
    <p>If you did not request this, please ignore this email or contact support if you have any concerns.</p>
    <p>Thank you,</p>
    <p><em>MFinance Support Team</em></p>
    END;

    try {
        $mail->send();
        echo "<script>
        alert('A password reset link has been sent to your email. Please check your inbox and follow the instructions. The link will expire in 10 minutes.');
        window.location.href = 'login.php'; // Redirect to login page
        </script>";
    } catch (Exception $e) {
        echo "<script>
        alert('There was an error sending the email. Please try again later.');
        window.location.href = 'login.php'; // Redirect to login page
        </script>";
    }
}
?>

