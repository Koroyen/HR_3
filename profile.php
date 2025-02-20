<?php
session_start();
require 'db.php';

// Check if the user is logged in
if (!isset($_SESSION['id'])) {
    header("Location: login.php");
    exit();
}

// Get the logged-in user's ID
$student_id = $_SESSION['id'];

// Fetch profile and hiring data from the users and hiring tables
$query = "SELECT users.fName, users.lName, users.email, users.profile_pic, 
                 hiring.status, hiring.date_status_updated, hiring.date_uploaded, hiring.message 
          FROM users 
          LEFT JOIN hiring ON hiring.user_id = users.id 
          WHERE users.id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $student_id);
$stmt->execute();
$result = $stmt->get_result();
$profile_data = $result->fetch_assoc();
$stmt->close();




if (isset($_POST['submit'])) {
    // Check if a file was uploaded
    if (isset($_FILES['profile_pic']) && $_FILES['profile_pic']['error'] === UPLOAD_ERR_OK) {
        // Set the directory to save uploaded files
        $target_dir = "uploads/profile_pics/";
        $file_name = basename($_FILES['profile_pic']['name']);
        $target_file = $target_dir . $file_name;
        
        // Ensure the file is an image
        $file_type = mime_content_type($_FILES['profile_pic']['tmp_name']);
        if (strpos($file_type, 'image') !== false) {
            // Move the uploaded file to the target directory
            if (move_uploaded_file($_FILES['profile_pic']['tmp_name'], $target_file)) {
                // Update the profile with the new profile picture filename
                $update_query = "UPDATE users SET profile_pic = ? WHERE id = ?";
                $stmt = $conn->prepare($update_query);
                $stmt->bind_param("si", $file_name, $student_id);
                $stmt->execute();
                $stmt->close();
                
                // Reload the page to reflect the updated profile picture
                echo "<script>alert('Profile picture updated successfully!'); window.location.href = 'employee.php';</script>";
            } else {
                echo "<script>alert('Failed to upload the profile picture.');</script>";
            }
        } else {
            echo "<script>alert('Please upload a valid image file.');</script>";
        }
    } else {
        echo "<script>alert('No profile picture uploaded.');</script>";
    }
}
?>





<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />
    <meta name="description" content="Profile Dashboard" />
    <meta name="author" content="" />
    <title>Profile Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/css/bootstrap.min.css" rel="stylesheet" crossorigin="anonymous">
    <link href="css/styles.css" rel="stylesheet" />
</head>

<body class="sb-nav-fixed bg-dark">

    <!-- Top Navbar -->
    <nav class="sb-topnav navbar navbar-expand navbar-dark bg-dark">
        <a class="navbar-brand ps-3" href="home.php">Profile Dashboard</a>
        <ul class="navbar-nav ms-auto me-4">
            <li class="nav-item dropdown">
                <a class="nav-link dropdown-toggle" id="navbarDropdown" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                    <i class="fas fa-user-circle"></i> <?php echo htmlspecialchars($profile_data['fName']); ?>
                </a>
                <ul class="dropdown-menu dropdown-menu-end bg-dark" aria-labelledby="navbarDropdown">
                  <li><a class="dropdown-item text-muted" href="logout.php">Logout</a></li>
                   <li><a class="dropdown-item text-muted" href="profile.php">Profile</a></li>
                </ul>
            </li>
        </ul>
    </nav>

    <div id="layoutSidenav">
        <div id="layoutSidenav_nav">
            <nav class="sb-sidenav accordion sb-sidenav-dark bg-dark" id="sidenavAccordion">
                <div class="sb-sidenav-menu">
                    <div class="nav">
                        <!-- <div class="sb-sidenav-menu-heading">Main</div> -->
                        <!-- <a class="nav-link" href="dashboard.php">
                        <div class="sb-nav-link-icon"><i class="fas fa-tachometer-alt"></i></div>
                        Dashboard
                    </a>
                    <a class="nav-link" href="edit_profile.php">
                        <div class="sb-nav-link-icon"><i class="fas fa-user-edit"></i></div>
                        Edit Profile
                    </a> -->
                    </div>
                </div>
            </nav>
        </div>
        <!-- Main content -->
        <div id="layoutSidenav_content">
            <main>
                <div class="container-fluid px-4 text-white">
                    <h1 class="mt-4">Profile</h1>
                    <ol class="breadcrumb mb-4">
                        <li class="breadcrumb-item active">Profile</li>
                    </ol>

                   
                    <!-- Profile picture display -->
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



                   

                    <!-- Display Hiring Application Data -->
                    <div class="card mb-4">
                        <div class="card-header">
                            <i class="fas fa-briefcase me-1"></i>
                            Hiring Application Status
                        </div>
                        <div class="card-body">
                            <table class="table table-bordered">
                                <thead>
                                    <tr>
                                        <th>Status</th>
                                        <th>Date Updated</th>
                                        <th>Date Uploaded</th>
                                        <th>Message</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td><?php echo htmlspecialchars($hiring_data['status'] ?? 'N/A'); ?></td>
                                        <td><?php echo htmlspecialchars($hiring_data['date_status_updated'] ?? 'N/A'); ?></td>
                                        <td><?php echo htmlspecialchars($hiring_data['date_uploaded'] ?? 'N/A'); ?></td>
                                        <td><?php echo htmlspecialchars($hiring_data['message'] ?? 'No message'); ?></td>
                                    </tr>
                                </tbody>
                            </table>
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

                        <!-- Edit Profile Modal -->
                        <div class="modal fade" id="editProfileModal" tabindex="-1" aria-labelledby="editProfileModalLabel" aria-hidden="true">
                            <div class="modal-dialog">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h5 class="modal-title" id="editProfileModalLabel">Update Your Profile</h5>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                    </div>
                                    <div class="modal-body">
                                        <form method="post" enctype="multipart/form-data">
                                            <div class="mb-3">
                                                <label for="profile_pic" class="form-label">Choose a new profile picture</label>
                                                <input type="file" class="form-control" id="profile_pic" name="profile_pic" accept="image/*" required>
                                            </div>
                                            <button type="submit" name="submit" class="btn btn-primary">Update Profile Picture</button>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div>

            </main>

            <!-- Footer -->
            <footer class="py-4 bg-dark mt-auto">
                <div class="container-fluid px-4">
                    <div class="d-flex align-items-center justify-content-between small">
                        <div class="text-muted">Copyright &copy; Student Dashboard 2023</div>
                        <div>
                            <a href="#" class="text-muted">Privacy Policy</a>
                            &middot;
                            <a href="#" class="text-muted">Terms &amp; Conditions</a>
                        </div>
                    </div>
                </div>
            </footer>
        </div>

        <!-- Bootstrap JS -->
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js" crossorigin="anonymous"></script>
        <script src="js/scripts.js"></script>
</body>

</html>