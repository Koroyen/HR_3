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
  <title>Home Page</title>
  <link href="https://cdn.jsdelivr.net/npm/simple-datatables@7.1.2/dist/style.min.css" rel="stylesheet" />
  <link href="css/styles.css" rel="stylesheet" />
  <script src="https://use.fontawesome.com/releases/v6.3.0/js/all.js" crossorigin="anonymous"></script>
  <!-- Bootstrap CSS -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/css/bootstrap.min.css" rel="stylesheet"
    crossorigin="anonymous">
  <!-- Bootstrap JS (v5.1 or newer) -->
  <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.10.2/dist/umd/popper.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.min.js"></script>
  <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
</head>

<body class="sb-nav-fixed">
    <!-- Top Navigation Bar -->
    <nav class="sb-topnav navbar navbar-expand navbar-dark bg-dark">
        <a class="navbar-brand ps-3" href="scholar_app.php">Microfinance</a>
        <button class="btn btn-link btn-sm order-1 order-lg-0 me-4 me-lg-0" id="sidebarToggle" href="#!"><i class="fas fa-bars"></i></button>
        <ul class="navbar-nav ms-auto">
           <!-- User Dropdown -->
      <li class="nav-item dropdown">
        <a class="nav-link dropdown-toggle" id="navbarDropdown" href="#" role="button"
          data-bs-toggle="dropdown" aria-expanded="false">
          <i class="fas fa-user fa-fw"></i>
        </a>
        <ul class="dropdown-menu dropdown-menu-end bg-dark" aria-labelledby="navbarDropdown">
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
                        <div class="sb-sidenav-menu-heading">Core</div>
                        <!-- <a class="nav-link" href="index.php">
                            <div class="sb-nav-link-icon"><i class="fas fa-tachometer-alt"></i></div>
                            Dashboard
                        </a> -->

                        <a class="nav-link" href="scholar_app.php">
                            <div class="sb-nav-link-icon"><i class="fas fa-chart-area"></i></div>
                            Scholarship applications
                        </a>
                        <a class="nav-link" href="job_app.php">
                            <div class="sb-nav-link-icon"><i class="fas fa-chart-area"></i></div>
                            Job applications
                        </a>
                        <a class="nav-link" href="tesda_app.php">
                            <div class="sb-nav-link-icon"><i class="fas fa-chart-area"></i></div>
                            Tesda applications
                        </a>

                        
                    </div>
                </div>
                <div class="sb-sidenav-footer bg-dark">
                    <div class="small">Logged in as:</div>
                    Start Bootstrap
                </div>
            </nav>
        </div>

        <!-- Content -->
        <div id="layoutSidenav_content" class="bg-dark" style="--bs-bg-opacity: .95;">
            <main>
                <div class="container-fluid px-4">
                    <h1 class="mt-4 text-light">Scholarship</h1>




                    <div class="card mb-4">
                        <div class="card-header">
                            <i class="fas fa-table me-1"></i> Scholarship
                        </div>
                        <div class="card-body table-responsive">
                            <table id="datatablesSimple" class="table table-striped table-bordered">

                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>First Name</th>
                                        <th>Last Name</th>
                                        <th>Age</th>
                                        <th>Email</th>
                                        <th>Street</th>
                                        <th>barangay</th>
                                        <th>City</th>
                                        <th>Certificate of Enrollment</th>
                                        <th>Birth Certificate</th>
                                        <th>Status</th>
                                        <th>Date Uploaded</th>
                                        <th>Date Status Updated</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tfoot>
                                    <tr>
                                        <th>ID</th>
                                        <th>First Name</th>
                                        <th>Last Name</th>
                                        <th>Age</th>
                                        <th>Email</th>
                                        <th>Street</th>
                                        <th>barangay</th>
                                        <th>City</th>
                                        <th>Certificate of Enrollment</th>
                                        <th>Birth Certificate</th>
                                        <th>Status</th>
                                        <th>Date Uploaded</th>
                                        <th>Date Status Updated</th>
                                        <th>Actions</th>
                                    </tr>
                                </tfoot>
                                <tbody>
                                    <?php
                                    // Assuming the correct column name in the `cities` table is `city_id`
                                    // Fetch only visible data from the `images_coe_birthc` table and join it with the `cities` table to get the city name
                                    $query = "SELECT images_coe_birthc.*, cities.city_name 
              FROM images_coe_birthc 
              LEFT JOIN cities ON images_coe_birthc.city_id = cities.city_id 
              WHERE images_coe_birthc.is_visible = 1";

                                    $result = mysqli_query($conn, $query);

                                    if (!$result) {
                                        die('Query failed: ' . mysqli_error($conn)); // This will help identify any further issues
                                    }

                                    // Handle Approve/Decline/Remove requests
                                    if (isset($_POST['action']) && isset($_POST['id'])) {
                                        $id = $_POST['id'];
                                        $action = $_POST['action'];

                                        if ($action == 'Approved') {
                                            // Update the status to 'Approved' and set the date_status_updated
                                            $update_query = "UPDATE images_coe_birthc SET status = 'Approved', date_status_updated = NOW() WHERE id = ?";
                                        } elseif ($action == 'Declined') {
                                            // Update the status to 'Declined', set the date_status_updated, and automatically hide it
                                            $update_query = "UPDATE images_coe_birthc SET status = 'Declined', date_status_updated = NOW(), is_visible = 0 WHERE id = ?";
                                        } elseif ($action == 'remove') {
                                            // Update the `is_visible` to 0 (hide the record)
                                            $remove_query = "UPDATE images_coe_birthc SET is_visible = 0 WHERE id = ?";
                                        }

                                        // Prepare and execute the appropriate query for approval, decline, or remove
                                        if (isset($update_query)) {
                                            $stmt = $conn->prepare($update_query);
                                            $stmt->bind_param('i', $id); // Bind the ID
                                            if ($stmt->execute()) {
                                                // Fetch the email to auto-populate in the modal for approval or decline
                                                $email_query = "SELECT email FROM images_coe_birthc WHERE id = ?";
                                                $stmt_email = $conn->prepare($email_query);
                                                $stmt_email->bind_param('i', $id);
                                                $stmt_email->execute();
                                                $stmt_email->bind_result($email);
                                                $stmt_email->fetch();
                                                $stmt_email->close();

                                                // Pass data to JavaScript for modal interaction
                                                echo "<script>
                    document.addEventListener('DOMContentLoaded', function () {
                        document.getElementById('statusId').value = '$id';
                        document.getElementById('statusAction').value = '$action';
                        document.getElementById('emailInput').value = '$email';
                        var statusModal = new bootstrap.Modal(document.getElementById('statusModal'));
                        statusModal.show();
                    });
                </script>";
                                            } else {
                                                echo "Error updating record: " . mysqli_error($conn);
                                            }
                                        } elseif (isset($remove_query)) {
                                            $stmt = $conn->prepare($remove_query);
                                            $stmt->bind_param('i', $id); // Bind the ID
                                            if ($stmt->execute()) {
                                                echo "<script>alert('Record has been hidden successfully.');</script>";
                                            } else {
                                                echo "Error updating record: " . mysqli_error($conn);
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
                                                <td><?php echo htmlspecialchars($row['email']); ?></td>
                                                <td><?php echo htmlspecialchars($row['street']); ?></td>
                                                <td><?php echo htmlspecialchars($row['barangay']); ?></td>
                                                <td><?php echo htmlspecialchars($row['city_name']); // Fetching city name from the `cities` table 
                                                    ?></td>
                                                <td>
                                                    <a href="#" data-bs-toggle="modal" data-bs-target="#imageModal" data-image="images-coe-birthc/<?php echo htmlspecialchars($row['coe']); ?>">
                                                        <img src="images-coe-birthc/<?php echo htmlspecialchars($row['coe']); ?>" alt="COE" style="width: 100px;">
                                                    </a>
                                                </td>
                                                <td>
                                                    <a href="#" data-bs-toggle="modal" data-bs-target="#imageModal" data-image="images-coe-birthc/<?php echo htmlspecialchars($row['birthc']); ?>">
                                                        <img src="images-coe-birthc/<?php echo htmlspecialchars($row['birthc']); ?>" alt="Birth Certificate" style="width: 100px;">
                                                    </a>
                                                </td>
                                                <td><?php echo htmlspecialchars($row['status']); ?></td>
                                                <td><?php echo htmlspecialchars($row['date_uploaded']); ?></td>
                                                <td><?php echo htmlspecialchars($row['date_status_updated']); ?></td>
                                                <td>
                                                    <?php if ($row['status'] == 'Pending') { ?>
                                                        <form method="POST" action="">
                                                            <button type="submit" name="action" value="Approved" class="btn btn-success btn-sm">Approve</button>
                                                            <button type="submit" name="action" value="Declined" class="btn btn-danger btn-sm">Decline</button>
                                                            <input type="hidden" name="id" value="<?php echo $row['id']; ?>" />
                                                        </form>
                                                    <?php } elseif ($row['status'] == 'Approved' || $row['status'] == 'Declined') { ?>
                                                        <form method="POST" action="">
                                                            <button type="submit" name="action" value="remove" class="btn btn-warning btn-sm">Remove</button>
                                                            <input type="hidden" name="id" value="<?php echo $row['id']; ?>" />
                                                        </form>
                                                    <?php } ?>
                                                </td>
                                            </tr>
                                    <?php
                                        }
                                    } else {
                                        echo "<tr><td colspan='14'>No records found</td></tr>";
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
                                <form method="POST" action="notify.php" id="notificationForm">
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
                                            <textarea class="form-control" id="message" name="message" rows="4" required placeholder="Enter your message..."></textarea>
                                        </div>
                                    </div>
                                    <div class="modal-footer">
                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                                        <button type="submit" class="btn btn-primary" id="sendNotificationButton">Send Notification</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                    <script>
                        document.getElementById('statusModal').addEventListener('hidden.bs.modal', function() {
                            // Reset form when modal is closed
                            document.getElementById('notificationForm').reset();
                            document.getElementById('statusId').value = '';
                            document.getElementById('statusAction').value = '';
                        });
                    </script>






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
                            var datatablesSimple = document.getElementById('datatablesSimple');
                            if (datatablesSimple) {
                                new simpleDatatables.DataTable(datatablesSimple);
                            }

                            // Modal image display
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
                <!-- <div class="container-fluid px-4">
                    <div class="d-flex align-items-center justify-content-between small">
                        <div class="text-muted">Copyright &copy; Your Website 2023</div>
                        <div>
                            <a href="#">Privacy Policy</a>
                            &middot;
                            <a href="#">Terms &amp; Conditions</a>
                        </div>
                    </div>
                </div> -->
            </footer>
        </div>
    </div>

    <!-- Bootstrap Bundle JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="js/scripts.js"></script>

    <!-- Chart.js Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js@2.9.4/dist/Chart.min.js"></script>
    <script src="assets/demo/chart-area-demo.js"></script>
    <script src="assets/demo/chart-bar-demo.js"></script>

    <!-- Simple DataTables Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/simple-datatables@7.1.2/dist/umd/simple-datatables.min.js"></script>
    <script src="js/datatables-simple-demo.js"></script>
</body>

</html>