<?php
session_start();
require 'db.php'; // Include database connection

// Check if the user is logged in and is not "Staff"
if (!isset($_SESSION["id"]) || $_SESSION["role"] != "Staff") {
    echo "Session role: " . $_SESSION["role"]; // For debugging, can be removed after testing
    header("Location: login.php");
    exit();
}


// Fetch profile data for the logged-in user
$user_id = $_SESSION['id'];
$query = "SELECT first_name, last_name, email, profile_pic FROM users WHERE id = ?";
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
    <title>Profile</title>
    <script src="https://cdn.jsdelivr.net/npm/simple-datatables@7.1.2/dist/umd/simple-datatables.min.js"></script>
    <link href="https://cdn.jsdelivr.net/npm/simple-datatables@7.1.2/dist/style.min.css" rel="stylesheet" />
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="css/styles.css" rel="stylesheet" />
    <script src="https://use.fontawesome.com/releases/v6.3.0/js/all.js" crossorigin="anonymous"></script>
</head>

<body class="sb-nav-fixed bg-dark">
    <nav class="sb-topnav navbar navbar-expand navbar-dark bg-dark">
        <a class="navbar-brand ps-3" href="employee_job.php">Ascenders business services</a>
        <button class="btn btn-link btn-sm order-1 order-lg-0 me-4 me-lg-0 p-5" id="sidebarToggle" href="#"><i class="fas fa-bars"></i></button>

        <!-- Right side of navbar (moved dropdown to the far right) -->
        <ul class="navbar-nav ms-auto">
            <li class="nav-item dropdown">
                <a class="nav-link dropdown-toggle" id="navbarDropdown" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                    <i class="fas fa-user fa-fw"></i>
                </a>
                <ul class="dropdown-menu dropdown-menu-end bg-dark" aria-labelledby="navbarDropdown">
                    <li><a class="dropdown-item text-muted" href="logout.php">Logout</a></li>
                    <li><a class="dropdown-item text-muted" href="password.php">Change password</a></li>
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
                        <div class="sb-sidenav-menu-heading">Employee Dashboard</div>
                        <a class="nav-link" href="employee_job.php">
                            <div class="sb-nav-link-icon"><i class="fas fa-chart-area"></i></div>
                            Job applications
                        </a>
                        <div class="sb-sidenav-menu-heading">Message</div>
                        <a class="nav-link" href="requests.php">
                            <div class="sb-nav-link-icon"><i class="fas fa-file-alt"></i></div>
                            Message
                        </a>
                        <a class="nav-link" href="messages.php">
                            <div class="sb-nav-link-icon"><i class="fas fa-envelope"></i></div>
                            Message Log
                        </a>
                        <a class="nav-link" href="employee_train.php">
                            <div class="sb-nav-link-icon"><i class="fas fa-file-alt"></i></div>
                            Task
                        </a>
                        <a class="nav-link" href="task_answer.php">
                            <div class="sb-nav-link-icon"><i class="fas fa-file-alt"></i></div>
                            Training
                        </a>
                    </div>
                </div>
            </nav>
        </div>

        Main Content
        <div id="layoutSidenav_content" class="bg-dark">
            <main>
                <div class="container-fluid px-4">
                    <h1 class="mt-4 text-light">Profile Details</h1>


                    <div class="card mb-4 bg-dark">
                        <div class="card-header">
                            <i class="fas fa-user-circle me-1 text-light"></i> Your Profile
                        </div>
                        <div class="card-body text-center">
                            <div class="mb-3">
                                <a href="#" data-bs-toggle="modal" data-bs-target="#viewProfilePicModal">
                                    <img src="uploads/profile_pics/<?php echo htmlspecialchars($profile_data['profile_pic']); ?>" alt="Profile Picture" class="rounded-circle" style="width: 150px; height: 150px;">
                                </a>
                            </div>
                            <h5 class="card-title text-light"><?php echo htmlspecialchars($profile_data['first_name']) . ' ' . htmlspecialchars($profile_data['last_name']); ?></h5>
                            <p class="card-text text-light"><?php echo htmlspecialchars($profile_data['email']); ?></p>
                            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#editProfileModal">Edit Profile Picture</button>
                        </div>
                    </div>

                    <!-- Modal for Editing Profile Picture -->
                    <div class="modal fade" id="editProfileModal" tabindex="-1" aria-labelledby="editProfileModalLabel" aria-hidden="true">
                        <div class="modal-dialog">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title" id="editProfileModalLabel">Edit Profile Picture</h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                </div>
                                <div class="modal-body">
                                    <form method="post" enctype="multipart/form-data">
                                        <div class="form-group mb-3">
                                            <label for="profile_pic">Select New Profile Picture</label>
                                            <input type="file" class="form-control" id="profile_pic" name="profile_pic" required>
                                        </div>
                                        <div class="form-group">
                                            <button type="submit" class="btn btn-primary" name="submit">Update Profile Picture</button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Task Progress Section -->
                    <div class="card mb-4 bg-dark">
                        <div class="card-header text-light">
                            <i class="fas fa-tasks me-1 text-light"></i> Task Progress
                        </div>
                        <div class="card-body bg-dark">
                            <?php if (!empty($tasks)) { ?>
                                <table class="table table-dark table-bordered table-hover">
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
                                                        $percentage = 50; // Or you can randomize
                                                    } elseif ($task['progress_status'] === 'completed') {
                                                        $percentage = 100;
                                                    }
                                                    ?>

                                                    <!-- Progress Bar -->
                                                    <div class="progress">
                                                        <div class="progress-bar bg-success" role="progressbar" style="width: <?php echo $percentage; ?>%;" aria-valuenow="<?php echo $percentage; ?>" aria-valuemin="0" aria-valuemax="100">
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
                                <p class="text-muted text-light">No tasks assigned yet.</p>
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
    <footer class="bg-dark text-center py-3 mt-5 text-light">
        <div class="container">
            <small>Copyright © Microfinance 2025</small><br>
            <button type="button" class="btn btn-link text-light" data-bs-toggle="modal" data-bs-target="#policiesModal">
                Policies
            </button>
        </div>
    </footer>
    </div>

    <!-- Policies Modal -->
    <div class="modal fade bg-dark " id="policiesModal" tabindex="-1" aria-labelledby="policiesModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content bg-dark text-light">
                <div class="modal-header">
                    <h5 class="modal-title" id="policiesModalLabel">Recuitment Human Resource Department Policies</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <h6>Applicant Confidentiality</h6>
                    <p>All personnel involved in the hiring process must refrain from discussing or disclosing any applicant information outside of the recruitment process. This includes not sharing information with colleagues, other departments, or external parties.</p>
                    <hr>
                    <h6>Confidentiality of Applicant Information</h6>
                    <p>All personal information submitted by applicants is strictly confidential and will not be shared without the applicant's consent.</p>
                    <hr>
                    <h6>Transparency</h6>
                    <p> If applicants request information about how their data is being used, the HR department will provide clear explanations of the recruitment process, the types of data collected, and how it is safeguarded.</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap Bundle JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="js/scripts.js"></script>
</body>

</html>