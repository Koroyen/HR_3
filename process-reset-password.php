<?php 
session_start(); // Start session if not already started

// Check if the form is submitted
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    if (isset($_POST["token"], $_POST["password"], $_POST["confirmpassword"])) {
        $token = $_POST["token"];
        $password = $_POST["password"];
        $confirm_password = $_POST["confirmpassword"];

        // Validate password length (minimum 8 characters)
        if (strlen($password) < 8) {
            echo "<script>
                alert('Password must be at least 8 characters long.');
                window.location.href = 'password.php'; // Redirect back to reset page
            </script>";
            exit();
        }

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
        
        // Check if the statement was prepared successfully
        if (!$stmt) {
            echo "<script>
                alert('Error preparing SQL query.');
                window.location.href = 'reset-password.php';
            </script>";
            exit();
        }

        // Bind the token hash to the SQL query
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
                window.location.href = 'login.php'; // Redirect to forgot password page
            </script>";
            exit();
        }

        // Update the user's password and clear the reset token
        $sql = "UPDATE users 
                SET password = ?, reset_token_hash = NULL, reset_token_expires_at = NULL
                WHERE id = ?";
        $stmt = $conn->prepare($sql);
        
        // Check if the statement was prepared successfully
        if (!$stmt) {
            echo "<script>
                alert('Error preparing SQL query for update.');
                window.location.href = 'reset-password.php';
            </script>";
            exit();
        }

        // Bind the hashed password and user ID to the SQL query
        $stmt->bind_param("si", $password_hash, $user["id"]);  // Use 'i' for integer (user id)

        if ($stmt->execute()) {
            echo "<script>
                alert('Password updated successfully.');
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
