<?php
session_start();
require 'db.php'; // Include database connection

// Check if user is logged in and is an Employee
if (!isset($_SESSION["id"]) || $_SESSION["role"] != 2) {
    header("Location: login.php");
    exit();
}
?>


<!DOCTYPE html>
<html lang="en">

<head>
<meta charset="utf-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />
    <meta name="description" content="" />
    <meta name="author" content="" />
    <title>Employee Dashboard </title>
    <link href="https://cdn.jsdelivr.net/npm/simple-datatables@7.1.2/dist/style.min.css" rel="stylesheet" />
    <link href="css/styles.css" rel="stylesheet" />
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://use.fontawesome.com/releases/v6.3.0/js/all.js" crossorigin="anonymous"></script>
</head>

<body class="sb-nav-fixed">
    <!-- Top Navbar -->
    <nav class="sb-topnav navbar navbar-expand navbar-dark bg-dark">
        <a class="navbar-brand ps-3" href="employee_job.php">Microfinance</a>

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

    <div id="layoutSidenav">
        <!-- Sidebar -->
        <div id="layoutSidenav_nav">
            <nav class="sb-sidenav accordion sb-sidenav-dark" id="sidenavAccordion">
                <div class="sb-sidenav-menu">
                    <div class="nav">
                        <div class="sb-sidenav-menu-heading">Interface</div>
                        <a class="nav-link" href="employee_job.php">
                            <div class="sb-nav-link-icon"><i class="fas fa-chart-area"></i></div>
                            Job applications
                        </a>

                        <div class="sb-sidenav-menu-heading">Message</div>

                        
                        <a class="nav-link" href="requests.php">
                            <div class="sb-nav-link-icon"><i class="fas fa-file-alt"></i></div>
                            Message
                        </a>

                        <!-- Messages -->
                        <a class="nav-link" href="messages.php">
                            <div class="sb-nav-link-icon"><i class="fas fa-envelope"></i></div>
                            Message Log
                        </a>
                        <a class="nav-link" href="task_answer.php">
                            <div class="sb-nav-link-icon"><i class="fas fa-file-alt"></i></div>
                            Task
                        </a>
                        <a class="nav-link" href="predict_suitability.php">
                            <div class="sb-nav-link-icon"><i class="fas fa-file-alt"></i></div>
                            Suitability Score
                        </a>
                    </div>
                </div>
                <div class="sb-sidenav-footer bg-dark">
                    <div class="small">Logged in as: <?php echo htmlspecialchars($_SESSION['user_name']); ?></div>
                </div>
            </nav>
        </div>

       <!-- Content -->
<div id="layoutSidenav_content" class="bg-dark" style="--bs-bg-opacity: .95;">
    
        <div class="container-fluid px-4">
            <h1 class="mt-4 text-light">Job Hiring Details</h1>
            <div class="card mb-4">
                <div class="card-header">
                    <i class="fas fa-table me-1"></i> Hiring Applications DataTable
                </div>
                <div class="card-body table-responsive">
                    <table id="datatablesSimple" class="table table-striped table-bordered ">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>First Name</th>
                                <th>Last Name</th>
                                <th>Age</th>
                                <th>Sex</th>
                                <th>Job Position</th>
                                <th>Email</th>
                                <th>Street</th>
                                <th>Barangay</th>
                                <th>City</th>
                                <th>Birth Certificate</th>
                                <th>Curriculum Vitae</th>
                                <th>Status</th>
                                <th>Date Uploaded</th>
                                <th>Date Status Updated</th>
                                <th>AI Suitability Score</th> <!-- AI Score Column -->
                                <th>Actions</th>
                            </tr>
                        </thead>

                        <tbody>

                            <?php
                            // Fetch data from the `hiring` table and join with `cities`
                            $query = "SELECT hiring.*, cities.city_name, hiring.suitability_score 
                                      FROM hiring
                                      LEFT JOIN cities ON hiring.city_id = cities.city_id 
                                      WHERE hiring.is_visible = 1";

                            $result = mysqli_query($conn, $query);

                            if (!$result) {
                                die('Query failed: ' . mysqli_error($conn)); 
                            }

                            // Handle Approve/Decline/Remove actions
                            if (isset($_POST['action']) && isset($_POST['id'])) {
                                $id = $_POST['id'];
                                $action = $_POST['action'];

                                if ($action == 'Approved') {
                                    $update_query = "UPDATE hiring SET status = 'Approved', date_status_updated = NOW() WHERE id = ?";
                                } elseif ($action == 'Declined') {
                                    $update_query = "UPDATE hiring SET status = 'Declined', date_status_updated = NOW(), is_visible = 0 WHERE id = ?";
                                } elseif ($action == 'remove') {
                                    $remove_query = "UPDATE hiring SET is_visible = 0 WHERE id = ?";
                                }

                                if (isset($update_query)) {
                                    $stmt = $conn->prepare($update_query);
                                    $stmt->bind_param('i', $id);
                                    if ($stmt->execute()) {
                                        echo "<script>alert('Status updated successfully.');</script>";
                                    } else {
                                        echo "Error updating record: " . mysqli_error($conn);
                                    }
                                } elseif (isset($remove_query)) {
                                    $stmt = $conn->prepare($remove_query);
                                    $stmt->bind_param('i', $id);
                                    if ($stmt->execute()) {
                                        echo "<script>alert('Record hidden successfully.');</script>";
                                    } else {
                                        echo "Error hiding record: " . mysqli_error($conn);
                                    }
                                }
                            }

                            // Fetch and display the records
                            if (mysqli_num_rows($result) > 0) {
                                while ($row = mysqli_fetch_assoc($result)) {
                            ?>
                            <tr>
                                <td><?php echo htmlspecialchars($row['id']); ?></td>
                                <td><?php echo htmlspecialchars($row['fName']); ?></td>
                                <td><?php echo htmlspecialchars($row['lName']); ?></td>
                                <td><?php echo htmlspecialchars($row['Age']); ?></td>
                                <td><?php echo htmlspecialchars($row['sex']); ?></td>
                                <td><?php echo htmlspecialchars($row['job_position']); ?></td>
                                <td><?php echo htmlspecialchars($row['email']); ?></td>
                                <td><?php echo htmlspecialchars($row['street']); ?></td>
                                <td><?php echo htmlspecialchars($row['barangay']); ?></td>
                                <td><?php echo htmlspecialchars($row['city_name']); ?></td>
                                <td>
                                                <a href="#" data-bs-toggle="modal" data-bs-target="#imageModal" data-image="hiring/<?php echo htmlspecialchars($row['valid_ids']); ?>">
                                                    <img src="hiring/<?php echo htmlspecialchars($row['valid_ids']); ?>" alt="ID" style="width: 100px;">
                                                </a>
                                            </td>
                                            <td>
                                                <a href="#" data-bs-toggle="modal" data-bs-target="#imageModal" data-image="hiring/<?php echo htmlspecialchars($row['birthcerti']); ?>">
                                                    <img src="hiring/<?php echo htmlspecialchars($row['birthcerti']); ?>" alt="Birth Certificate" style="width: 100px;">
                                                </a>
                                            </td>
                                <td><?php echo htmlspecialchars($row['status']); ?></td>
                                <td><?php echo htmlspecialchars($row['date_uploaded']); ?></td>
                                <td><?php echo htmlspecialchars($row['date_status_updated']); ?></td>
                                <td><?php echo htmlspecialchars($row['suitability_score']); ?></td>
                                <td>
                                    <form method="post">
                                        <input type="hidden" name="id" value="<?php echo $row['id']; ?>">
                                        <?php if ($row['status'] == 'Pending') { ?>
                                            <button type="submit" name="action" value="Approved" class="btn btn-success btn-sm">Approve</button>
                                            <button type="submit" name="action" value="Declined" class="btn btn-danger btn-sm">Decline</button>
                                        <?php } elseif ($row['status'] == 'Approved' || $row['status'] == 'Declined') { ?>
                                            <button type="submit" name="action" value="remove" class="btn btn-warning btn-sm">Remove</button>
                                        <?php } ?>
                                    </form>
                                </td>
                            </tr>
                            <?php
                                }
                            } else {
                                echo "<tr><td colspan='16' class='text-center'>No records found.</td></tr>";
                            }
                            ?>
                        </tbody>
                    </table>
                </div>
            </div>

           
                       

                        <!-- Modal for Approve/Decline with Message -->
                        <div class="modal fade" id="statusModal" tabindex="-1" aria-labelledby="statusModalLabel" aria-hidden="true">
                            <div class="modal-dialog">
                                <div class="modal-content">
                                    <form method="POST" action="notify.php">
                                        <div class="modal-header">
                                            <h5 class="modal-title" id="statusModalLabel">Send Notification</h5>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                        </div>
                                        <div class="modal-body">
                                            <input type="hidden" id="statusId" name="id" value=""> <!-- Hidden input to hold the ID -->
                                            <input type="hidden" id="statusAction" name="action" value=""> <!-- Hidden input for the action -->
                                            <div class="form-group">
                                                <label for="emailInput">Email</label>
                                                <input type="email" class="form-control" id="emailInput" name="email" value="" required>
                                            </div>
                                            <div class="form-group">
                                                <label for="message">Message to Applicant</label>
                                                <textarea class="form-control" id="message" name="message" rows="4" placeholder="Enter your message..." required></textarea>
                                            </div>
                                        </div>
                                        <div class="modal-footer">
                                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                                            <button type="submit" class="btn btn-primary">Send Notification</button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>

                        <!-- Modal for image display -->
                        <div class="modal fade" id="imageModal" tabindex="-1" aria-labelledby="imageModalLabel" aria-hidden="true">
                            <div class="modal-dialog modal-lg">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h5 class="modal-title" id="imageModalLabel">Image Preview</h5>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                    </div>
                                    <div class="modal-body text-center">
                                        <img id="modalImage" src="" alt="Document Image" style="max-width: 100%; height: auto;">
                                    </div>
                                </div>
                            </div>
                        </div>

                        <script>
                            document.addEventListener('DOMContentLoaded', function() {
                                // Handle modal image display
                                var imageModal = document.getElementById('imageModal');
                                imageModal.addEventListener('show.bs.modal', function(event) {
                                    var button = event.relatedTarget; // Button that triggered the modal
                                    var imageUrl = button.getAttribute('data-image'); // Extract image URL from data-* attributes
                                    var modalImage = document.getElementById('modalImage'); // Get the image element inside the modal
                                    modalImage.src = imageUrl; // Set the source of the image in the modal
                                });
                            });
                        </script>
            </main>

            <!-- Footer -->
            <footer class="py-4 bg-light mt-auto bg-dark">
                <div class="container-fluid px-4">
                    <div class="d-flex align-items-center justify-content-between small">
                        <div class="text-muted">Copyright &copy; Your Website 2023</div>
                        <div>
                            <a href="#">Privacy Policy</a>
                            &middot;
                            <a href="#">Terms &amp; Conditions</a>
                        </div>
                    </div>
                </div>
            </footer>
        </div>
    </div>

    <!-- Bootstrap Bundle JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="js/scripts.js"></script>

    <!-- Chart.js Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js@2.9.4/dist/Chart.min.js"></script>


    <!-- Simple DataTables Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/simple-datatables@7.1.2/dist/umd/simple-datatables.min.js"></script>
    <script src="js/datatables-simple-demo.js"></script>

</body>

</html>