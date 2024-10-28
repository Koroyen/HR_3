<?php 
session_start(); // Start session if not already started

// Check if the form is submitted
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    if (isset($_POST["token"], $_POST["password"], $_POST["confirmpassword"])) {
        $token = $_POST["token"];
        $password = $_POST["password"];
        $confirm_password = $_POST["confirmpassword"];

        // Validate password and confirm password match
        if ($password !== $confirm_password) {
            echo "<script>
                alert('Passwords do not match!');
                window.location.href = 'reset-password.php'; // Redirect back to reset page
            </script>";
            exit();
        }

        // Hash the new password securely
        $password_hash = password_hash($password, PASSWORD_DEFAULT);

        // Hash the token for verification
        $token_hash = hash("sha256", $token);

        // Database connection
        $conn = require __DIR__ . "/db.php";

        // Find the user with the reset token
        $sql = "SELECT * FROM users WHERE reset_token_hash = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("s", $token_hash);
        $stmt->execute();
        $result = $stmt->get_result();
        $user = $result->fetch_assoc();

        // Handle invalid token or expired token
        if ($user === null) {
            echo "<script>
                alert('Token not found');
                window.location.href = 'reset-password.php'; // Redirect to reset page
            </script>";
            exit();
        }

        // Check if the reset token has expired
        if (strtotime($user["reset_token_expires_at"]) <= time()) {
            echo "<script>
                alert('Token has expired');
                window.location.href = 'forgot-password.php'; // Redirect to forgot password page
            </script>";
            exit();
        }

        // Update the user's password and clear the reset token
        $sql = "UPDATE users 
                SET password_hash = ?, reset_token_hash = NULL, reset_token_expires_at = NULL
                WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ss", $password_hash, $user["id"]);

        if ($stmt->execute()) {
            echo "<script>
                alert('Password updated successfully. Please login.');
                window.location.href = 'login.php'; // Redirect to login page
            </script>";
        } else {
            echo "<script>
                alert('Error updating password. Please try again.');
                window.location.href = 'reset-password.php'; // Redirect back to reset page
            </script>";
        }

    } else {
        echo "<script>
            alert('Invalid request');
            window.location.href = 'reset-password.php'; // Redirect to reset page
        </script>";
    }
} else {
    echo "<script>
        alert('Unauthorized access');
        window.location.href = 'login.php'; // Redirect to login page
    </script>";
}
?>
