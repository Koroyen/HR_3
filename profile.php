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

// Fetch profile data from the users table
$query = "SELECT users.fName, users.lName, users.email, users.profile_pic, 
                 images_coe_birthc.status, images_coe_birthc.date_status_updated, images_coe_birthc.date_uploaded, images_coe_birthc.message 
          FROM users 
          LEFT JOIN images_coe_birthc ON images_coe_birthc.user_id = users.id 
          WHERE users.id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $student_id);
$stmt->execute();
$result = $stmt->get_result();
$profile_data = $result->fetch_assoc();
$stmt->close();

// Fetch data from the hiring table
$query_hiring = "
    SELECT hiring.status, hiring.date_status_updated, hiring.date_uploaded, hiring.message, users.fName, users.lName 
    FROM users 
    LEFT JOIN hiring ON hiring.user_id = users.id 
    WHERE users.id = ?";
$stmt_hiring = $conn->prepare($query_hiring);
$stmt_hiring->bind_param("i", $student_id);
$stmt_hiring->execute();
$result_hiring = $stmt_hiring->get_result();
$hiring_data = $result_hiring->fetch_assoc();
$stmt_hiring->close();

// Fetch data from the certificate table
$query_certificate = "
    SELECT certificate.status, certificate.date_status_updated, certificate.date_uploaded, certificate.message, users.fName, users.lName 
    FROM users 
    LEFT JOIN certificate ON certificate.user_id = users.id 
    WHERE users.id = ?";
$stmt_certificate = $conn->prepare($query_certificate);
$stmt_certificate->bind_param("i", $student_id);
$stmt_certificate->execute();
$result_certificate = $stmt_certificate->get_result();
$certificate_data = $result_certificate->fetch_assoc();
$stmt_certificate->close();
?>


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
<body class="sb-nav-fixed bg-light">

<!-- Top Navbar -->
<nav class="sb-topnav navbar navbar-expand navbar-dark bg-dark">
    <a class="navbar-brand ps-3" href="home.php">Profile Dashboard</a>
    <ul class="navbar-nav ms-auto me-4">
        <li class="nav-item dropdown">
            <a class="nav-link dropdown-toggle" id="navbarDropdown" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                <i class="fas fa-user-circle"></i> <?php echo htmlspecialchars($profile_data['fName']); ?>
            </a>
            <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="navbarDropdown">
                <li><a class="dropdown-item" href="logout.php">Logout</a></li>
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
        <div class="container-fluid px-4">
            <h1 class="mt-4">Profile Details</h1>
            <ol class="breadcrumb mb-4">
                <li class="breadcrumb-item active">Profile</li>
            </ol>

            <!-- Profile Display Card -->
            <div class="card mb-4">
                <div class="card-header">
                    <i class="fas fa-user-circle me-1"></i>
                    Your Profile
                </div>
                <div class="card-body text-center">
                    <div class="mb-3">
                        <!-- Use the correct path for the profile picture and make it clickable -->
                        <a href="#" data-bs-toggle="modal" data-bs-target="#viewProfilePicModal">
                            <img src="<?php echo htmlspecialchars($profile_data['profile_pic']); ?>" alt="Profile Picture" class="rounded-circle" style="width: 150px; height: 150px;">
                        </a>
                    </div>
                    <h5 class="card-title"><?php echo htmlspecialchars($profile_data['fName']) . ' ' . htmlspecialchars($profile_data['lName']); ?></h5>
                    <p class="card-text"><?php echo htmlspecialchars($profile_data['email']); ?></p>
                    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#editProfileModal">Edit Profile</button>
                </div>
            </div>
<!-- Display Additional Data in a Table (Images COE/Birth Certificate Applications) -->
<div class="card mb-4">
                <div class="card-header">
                    <i class="fas fa-info-circle me-1"></i>
                    Scholarship Status
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
                                <td><?php echo htmlspecialchars($profile_data['status'] ?? 'N/A'); ?></td>
                                <td><?php echo htmlspecialchars($profile_data['date_status_updated'] ?? 'N/A'); ?></td>
                                <td><?php echo htmlspecialchars($profile_data['date_uploaded'] ?? 'N/A'); ?></td>
                                <td><?php echo htmlspecialchars($profile_data['message'] ?? 'No message'); ?></td>
                            </tr>
                        </tbody>
                    </table>
                </div>
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

            <!-- Display Certificate Application Data -->
            <div class="card mb-4">
                <div class="card-header">
                    <i class="fas fa-certificate me-1"></i>
                    Certificate Application Status
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
                                <td><?php echo htmlspecialchars($certificate_data['status'] ?? 'N/A'); ?></td>
                                <td><?php echo htmlspecialchars($certificate_data['date_status_updated'] ?? 'N/A'); ?></td>
                                <td><?php echo htmlspecialchars($certificate_data['date_uploaded'] ?? 'N/A'); ?></td>
                                <td><?php echo htmlspecialchars($certificate_data['message'] ?? 'No message'); ?></td>
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
                            <img src="<?php echo htmlspecialchars($profile_data['profile_pic']); ?>" alt="Profile Picture" class="img-fluid">
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
                            <form action="" method="POST" enctype="multipart/form-data">
                               
                                    <label for="profile_pic" class="form-label">Profile Picture</label>
                                    <input type="file" class="form-control-file" id="profile_pic" name="profile_pic">
                                </div>
                                <button type="submit" name="submit" class="btn btn-primary">Update Profile</button>
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
