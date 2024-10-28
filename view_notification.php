<?php
session_start();
require 'db.php';

// Ensure the user is logged in and is not an admin
if (!isset($_SESSION["id"]) || $_SESSION["role"] != 0) {
    header("Location: login.php");
    exit();
}


?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Notification Details</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body class="bg-dark text-light">
    <div class="container mt-5">
        <h1>Notification Details</h1>
        <?php if ($notification): ?>
            <div class="card bg-secondary text-white">
                <div class="card-header">
                    Notification from <?= htmlspecialchars($notification['created_at']); ?>
                </div>
                <div class="card-body">
                    <p><?= htmlspecialchars($notification['message']); ?></p>
                    <hr>
                    <h5>Document Status</h5>
                    <p><strong>Status:</strong> <?= htmlspecialchars($notification['status']); ?></p>
                    <p><strong>Date Status Updated:</strong> <?= htmlspecialchars($notification['date_status_updated']); ?></p>
                </div>
            </div>
        <?php else: ?>
            <div class="alert alert-danger" role="alert">
                Notification not found or no longer available.
            </div>
        <?php endif; ?>
        <a href="home.php" class="btn btn-primary mt-3">Back to Home</a>
    </div>
</body>

</html>
