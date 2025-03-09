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
    $mail->setFrom("mfinance@email.com");
    $mail->addAddress($email);
    $mail->Subject = "Password Reset";

    // Dynamically set the domain
    $domain = $_SERVER['HTTP_HOST'];
    
    // Enable HTML in the email body
    $mail->isHTML(true);
    $mail->Body = <<<END
    Click <a href="http://hr3.microfinance-solution.com/reset-password.php?token=$token">here</a> to reset your password.
    END;

    try {
        $mail->send();
    } catch (Exception $e) {
        echo "Message could not be sent. Mailer Error: {$mail->ErrorInfo}";
    }
}

echo "<script>
alert('Message sent to your Email.');
window.location.href = 'login.php'; // Redirect to login page
</script>";
?>
