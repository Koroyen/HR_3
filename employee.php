<?php
session_start();
require 'db.php'; // Include database connection

// Check if user is logged in and is an Employee
if (!isset($_SESSION["id"]) || $_SESSION["role"] != 2) {
    header("Location: login.php");
    exit();
}

// Fetch profile data for the logged-in user
$user_id = $_SESSION['id'];
$query = "SELECT fName, lName, email, profile_pic FROM users WHERE id = ?";
$stmt = $conn->prepare($query);
if ($stmt === false) {
    die('Error preparing query: ' . $conn->error);
}
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$profile_data = $result->fetch_assoc();
$stmt->close();

// Handle form submission to update profile picture only
if (isset($_POST['submit'])) {
    // Check if a file was uploaded
    if (isset($_FILES['profile_pic']) && $_FILES['profile_pic']['error'] === UPLOAD_ERR_OK) {
        // Set the directory to save uploaded files
        $target_dir = "uploads/profile_pics/";
        $file_name = basename($_FILES['profile_pic']['name']);
        $target_file = $target_dir . $file_name;

        // Move the uploaded file to the target directory
        if (move_uploaded_file($_FILES['profile_pic']['tmp_name'], $target_file)) {
            // Update the profile with the new profile picture
            $update_query = "UPDATE users SET profile_pic = ? WHERE id = ?";
            $stmt = $conn->prepare($update_query);
            if ($stmt === false) {
                die('Error preparing update query: ' . $conn->error);
            }
            $stmt->bind_param("si", $file_name, $user_id);
            $stmt->execute();
            $stmt->close();
            echo "<script>alert('Profile picture updated successfully!'); window.location.href = 'employee.php';</script>";
        } else {
            echo "<script>alert('Failed to upload the profile picture.');</script>";
        }
    } else {
        echo "<script>alert('No profile picture uploaded.');</script>";
    }
}

// Fetch task progress data for the logged-in user
$task_query = "
    SELECT pr.quiz_id, pr.progress_status
    FROM progress pr
    WHERE pr.employee_id = ?
";

$stmt = $conn->prepare($task_query);

// Check if the query preparation was successful
if ($stmt === false) {
    die("Error in SQL query: " . $conn->error);
}

// Bind the user_id parameter
$stmt->bind_param("i", $user_id);

// Execute the query
$stmt->execute();

// Get the result and fetch the data
$task_result = $stmt->get_result();
$tasks = $task_result->fetch_all(MYSQLI_ASSOC);

// Close the statement
$stmt->close();


?>


<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />
    <meta name="description" content="" />
    <meta name="author" content="" />
    <title>Profile Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/simple-datatables@7.1.2/dist/style.min.css" rel="stylesheet" />
    <link href="css/styles.css" rel="stylesheet" />
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://use.fontawesome.com/releases/v6.3.0/js/all.js" crossorigin="anonymous"></script>

</head>

<body class="sb-nav-fixed bg-light">
    <!-- Top Navbar -->
    <nav class="sb-topnav navbar navbar-expand navbar-dark bg-dark">
        <a class="navbar-brand ps-3" href="employee.php">Profile Dashboard</a>

        <!-- Navbar Toggle Button for collapsing navbar -->
        <button class="btn btn-link btn-sm order-1 order-lg-0 me-4 me-lg-0" id="sidebarToggle" href="#"><i class="fas fa-bars"></i></button>

        <!-- Right side of navbar (moved dropdown to the far right) -->
        <ul class="navbar-nav ms-auto">
            <li class="nav-item dropdown">
                <a class="nav-link dropdown-toggle" id="navbarDropdown" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                    <i class="fas fa-user fa-fw"></i>
                </a>
                <ul class="dropdown-menu dropdown-menu-end " aria-labelledby="navbarDropdown">
                    <li><a class="dropdown-item text-muted" href="logout.php">Logout</a></li>
                    <li><a class="dropdown-item text-muted" href="employee.php">Profile</a></li>

                </ul>
            </li>
        </ul>
    </nav>

    <!-- Side Navigation -->
    <div id="layoutSidenav">
        <div id="layoutSidenav_nav">
            <nav class="sb-sidenav accordion sb-sidenav-dark" id="sidenavAccordion">
                <div class="sb-sidenav-menu">
                    <div class="nav">
                        <div class="sb-sidenav-menu-heading">Interface</div>

                        <a class="nav-link" href="employee_job.php">
                            <div class="sb-nav-link-icon"><i class="fas fa-chart-area"></i></div>
                            Job applications
                        </a>


                        <div class="sb-sidenav-menu-heading">Notification</div>
                        <!-- Messages -->
                        <a class="nav-link" href="messages.php">
                            <div class="sb-nav-link-icon"><i class="fas fa-envelope"></i></div>
                            Messages
                        </a>

                        <!-- Requests -->
                        <a class="nav-link" href="requests.php">
                            <div class="sb-nav-link-icon"><i class="fas fa-file-alt"></i></div>
                            Requests
                        </a>
                        <a class="nav-link" href="task_answer.php">
                            <div class="sb-nav-link-icon"><i class="fas fa-file-alt"></i></div>
                            Task
                        </a>


                    </div>
                </div>
            </nav>
        </div>

        Main Content
        <div id="layoutSidenav_content">
            <main>

                <!-- Profile Display Card -->
                <div class="container-fluid px-4">
                    <h1 class="mt-4">Profile Details</h1>
                    <ol class="breadcrumb mb-4">
                        <li class="breadcrumb-item active">Profile</li>
                    </ol>

                    <div class="card mb-4">
                        <div class="card-header">
                            <i class="fas fa-user-circle me-1"></i> Your Profile
                        </div>
                        <div class="card-body text-center">
                            <div class="mb-3">
                                <a href="#" data-bs-toggle="modal" data-bs-target="#viewProfilePicModal">
                                    <img src="uploads/profile_pics/<?php echo htmlspecialchars($profile_data['profile_pic']); ?>" alt="Profile Picture" class="rounded-circle" style="width: 150px; height: 150px;">
                                </a>
                            </div>
                            <h5 class="card-title"><?php echo htmlspecialchars($profile_data['fName']) . ' ' . htmlspecialchars($profile_data['lName']); ?></h5>
                            <p class="card-text"><?php echo htmlspecialchars($profile_data['email']); ?></p>
                            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#editProfileModal">Edit Profile Picture</button>
                        </div>
                    </div>

                   <!-- Task Progress Section -->
<div class="card mb-4">
    <div class="card-header">
        <i class="fas fa-tasks me-1"></i> Task Progress
    </div>
    <div class="card-body">
        <?php if (!empty($tasks)) { ?>
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th>Task ID</th>
                        <th>Status</th>
                        <th>Progress</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($tasks as $task) { ?>
                        <tr>
                            <!-- Display Task (Quiz) ID -->
                            <td><?php echo htmlspecialchars($task['quiz_id']); ?></td>
                            
                            <!-- Display Task Status -->
                            <td>
                                <?php 
                                    if ($task['progress_status'] === 'not_started') {
                                        echo 'Not Started';
                                    } elseif ($task['progress_status'] === 'in_progress') {
                                        echo 'In Progress';
                                    } elseif ($task['progress_status'] === 'completed') {
                                        echo 'Completed';
                                    }
                                ?>
                            </td>

                            <!-- Display Progress Bar -->
                            <td>
                                <?php
                                // Assign percentage based on progress_status
                                $percentage = 0;
                                if ($task['progress_status'] === 'not_started') {
                                    $percentage = 0;
                                } elseif ($task['progress_status'] === 'in_progress') {
                                    // You can randomize this value or give a fixed mid-point like 50
                                    $percentage = 50; // Or rand(1, 99)
                                } elseif ($task['progress_status'] === 'completed') {
                                    $percentage = 100;
                                }
                                ?>
                                
                                <!-- Progress Bar -->
                                <div class="progress">
                                    <div class="progress-bar" role="progressbar" style="width: <?php echo $percentage; ?>%;" aria-valuenow="<?php echo $percentage; ?>" aria-valuemin="0" aria-valuemax="100">
                                        <?php echo $percentage; ?>%
                                    </div>
                                </div>
                            </td>

                            <!-- Action buttons: Continue or Completed -->
                            <td>
                                <?php if ($task['progress_status'] != 'completed') { ?>
                                    <a href="task_answer.php?task_id=<?php echo $task['quiz_id']; ?>" class="btn btn-primary btn-sm">Continue</a>
                                <?php } else { ?>
                                    <span class="text-success">Completed</span>
                                <?php } ?>
                            </td>
                        </tr>
                    <?php } ?>
                </tbody>
            </table>
        <?php } else { ?>
            <p class="text-muted">No tasks assigned yet.</p>
        <?php } ?>
    </div>
</div>
</div>



                <!-- Modal for Viewing Larger Profile Picture -->
                <div class="modal fade" id="viewProfilePicModal" tabindex="-1" aria-labelledby="viewProfilePicModalLabel" aria-hidden="true">
                    <div class="modal-dialog modal-dialog-centered">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title" id="viewProfilePicModalLabel">Your Profile Picture</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                            </div>
                            <div class="modal-body text-center">
                                <img src="uploads/profile_pics/<?php echo htmlspecialchars($profile_data['profile_pic']); ?>" alt="Profile Picture" class="img-fluid">
                            </div>
                        </div>
                    </div>
                </div>
        </div>
        </main>
    </div>
    </div>

    <!-- Bootstrap Bundle JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="js/scripts.js"></script>
</body>

</html>