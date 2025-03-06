<?php
session_start();
require 'db.php'; // Include database connection

// Check if user is logged in and is an Employee
if (!isset($_SESSION["id"]) || $_SESSION["role"] != 1) {
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
    <title>Applicant list</title>
    <link href="https://cdn.jsdelivr.net/npm/simple-datatables@7.1.2/dist/style.min.css" rel="stylesheet" />
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" />
    <link href="css/styles.css" rel="stylesheet" />
    <script src="https://use.fontawesome.com/releases/v6.3.0/js/all.js" crossorigin="anonymous"></script>
</head>


<body class="sb-nav-fixed bg-dark">
    <!-- Top Navbar -->
    <nav class="sb-topnav navbar navbar-expand navbar-dark bg-dark">
        <a class="navbar-brand ps-3" href="predict_suitability.php">Microfinance</a>

        <!-- Navbar Toggle Button for collapsing navbar -->
        <button class="btn btn-link btn-sm order-1 order-lg-0 me-4 me-lg-0" id="sidebarToggle" href="#"><i class="fas fa-bars"></i></button>

        <!-- Right side of navbar (moved dropdown to the far right) -->
        <ul class="navbar-nav ms-auto">
            <li class="nav-item dropdown">
                <a class="nav-link dropdown-toggle" id="navbarDropdown" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                    <i class="fas fa-user fa-fw"></i>
                </a>
                <ul class="dropdown-menu dropdown-menu-end bg-dark" aria-labelledby="navbarDropdown">
                    <li><a class="dropdown-item text-muted" href="logout.php">Logout</a></li>

                </ul>
            </li>
        </ul>
    </nav>

    <div id="layoutSidenav">
        <div id="layoutSidenav_nav">
            <nav class="sb-sidenav accordion sb-sidenav-dark" id="sidenavAccordion">
                <div class="sb-sidenav-menu">
                    <div class="nav">
                        <div class="sb-sidenav-menu-heading">Analytics</div>
                        <a class="nav-link" href="predict_suitability.php">
                            <div class="sb-nav-link-icon"><i class="fas fa-chart-area"></i></div>
                            Job Charts
                        </a>
                        <div class="sb-sidenav-menu-heading"> Lists</div>
                        <a class="nav-link" href="job_list.php">
                            <div class="sb-nav-link-icon"><i class="fas fa-chart-area"></i></div>
                            Application list
                        </a>
                        <a class="nav-link" href="hr_job.php">
                            <div class="sb-nav-link-icon"><i class="fas fa-chart-area"></i></div>
                            Job applicants
                        </a>
                        <a class="nav-link" href="reports.php">
                            <div class="sb-nav-link-icon"><i class="fas fa-file-alt"></i></div>
                            Reports
                        </a>
                        <a class="nav-link" href="job.php">
                            <div class="sb-nav-link-icon"><i class="fas fa-file-alt"></i></div>
                            Manage Job
                        </a>
                    </div>
                </div>
                <div class="sb-sidenav-footer bg-dark">
                    <div class="small">Logged in as: <?php echo htmlspecialchars($_SESSION['user_name']); ?></div>
                </div>
            </nav>
        </div>

        <div id="layoutSidenav_content" class="bg-dark-low ">
            <div class="container-fluid px-4 bg-dark-low">
                <h1 class="mt-4 text-light bg-dark-low">Job Hiring Details</h1>
                <div class="card mb-4 bg-dark-low">
                    <div class="card-header text-light bg-dark-low">
                        <i class="fas fa-table me-1"></i> Hiring Applications DataTable
                    </div>
                    <div class="card-body table-responsive bg-dark-low">
                        <table id="datatablesSimple" class="table table-striped table-bordered text-light bg-dark-low">
                            <thead class="bg-dark-low text-light">
                                <tr>
                                    <th>ID</th>
                                    <th>First Name</th>
                                    <th>Last Name</th>
                                    <th>Age</th>
                                    <th>Sex</th>
                                    <th>Skills</th>
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
                                    <th>AI Suitability Score</th>
                                    <th>Experience(Months)</th>
                                    <th>Experience(Years)</th>
                                    <th>Former Company</th>
                                    <th>Education</th>
                                    <th>Other Education</th>
                                    <th>Interview Day</th>
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

                                if (isset($_POST['action']) && isset($_POST['id'])) {
                                    $id = $_POST['id'];
                                    $action = $_POST['action'];
                                    $message = isset($_POST['message']) ? $_POST['message'] : null;
                                    $interview_date = isset($_POST['interview_date']) ? $_POST['interview_date'] : null;

                                    if ($action == 'Approved') {
                                        $update_query = "UPDATE hiring SET status = 'Approved', date_status_updated = NOW(), interview_date = ?, message = ? WHERE id = ?";
                                        $stmt = $conn->prepare($update_query);
                                        $stmt->bind_param('ssi', $interview_date, $message, $id);
                                    } elseif ($action == 'Declined') {
                                        $update_query = "UPDATE hiring SET status = 'Declined', date_status_updated = NOW(), message = ?, is_visible = 0 WHERE id = ?";
                                        $stmt = $conn->prepare($update_query);
                                        $stmt->bind_param('si', $message, $id);
                                    } elseif ($action == 'remove') {
                                        $remove_query = "UPDATE hiring SET is_visible = 0 WHERE id = ?";
                                        $stmt = $conn->prepare($remove_query);
                                        $stmt->bind_param('i', $id);
                                    }

                                    if ($stmt->execute()) {
                                        echo "<script>alert('Action completed successfully.');</script>";
                                    } else {
                                        echo "Error updating record: " . mysqli_error($conn);
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
                                            <td><?php echo htmlspecialchars($row['skills']); ?></td>
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
                                            <td><?php echo htmlspecialchars($row['experience_months']); ?></td>
                                            <td><?php echo htmlspecialchars($row['experience_years']); ?></td>
                                            <td><?php echo htmlspecialchars($row['former_company']); ?></td>
                                            <td><?php echo htmlspecialchars($row['education']); ?></td>
                                            <td><?php echo htmlspecialchars($row['otherEducation']); ?></td>
                                            <td><?php echo htmlspecialchars($row['interview_date']); ?></td>

                                            <td>
                                                <form method="post" class="d-flex justify-content-around">
                                                    <input type="hidden" name="id" value="<?php echo $row['id']; ?>">

                                                    <?php if ($row['status'] == 'Pending') { ?>
                                                        <!-- Approve Button -->
                                                        <button type="button" class="btn btn-link p-0" title="Approve" data-bs-toggle="modal" data-bs-target="#statusModal"
                                                            data-id="<?php echo $row['id']; ?>" data-email="<?php echo $row['email']; ?>" data-action="Approved">
                                                            <i class="fas fa-check-circle text-success"></i>
                                                        </button>

                                                        <!-- Decline Button -->
                                                        <button type="button" name="action" value="Declined" class="btn btn-link p-0" title="Decline" data-bs-toggle="modal" data-bs-target="#statusModal"
                                                            data-id="<?php echo $row['id']; ?>" data-email="<?php echo $row['email']; ?>" data-action="Declined">
                                                            <i class="fas fa-times-circle text-danger"></i>
                                                        </button>

                                                    <?php } elseif ($row['status'] == 'Approved' || $row['status'] == 'Declined') { ?>
                                                        <!-- Remove Button -->
                                                        <button type="submit" name="action" value="remove" class="btn btn-link p-0" title="Remove">
                                                            <i class="fas fa-trash-alt text-warning"></i>
                                                        </button>
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
                <!-- Status Modal -->
                <div class="modal fade" id="statusModal" tabindex="-1" aria-labelledby="statusModalLabel" aria-hidden="true">
                    <div class="modal-dialog">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title" id="statusModalLabel">Update Status</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                            </div>
                            <div class="modal-body">
                                <form id="statusForm" method="post" action="notify.php">
                                    <input type="hidden" name="id" id="modal-id">
                                    <input type="hidden" name="action" id="modal-action">

                                    <!-- Interview Date and Time (Only visible if Approved) -->
                                    <div class="mb-3" id="interview-date-group" style="display: none;">
                                        <label for="interview_date" class="form-label">Interview Date and Time</label>
                                        <input type="datetime-local" class="form-control" name="interview_date" id="interview_date">
                                    </div>

                                    <!-- Message Input (Always Visible) -->
                                    <div class="mb-3">
                                        <label for="message" class="form-label">Message to Applicant</label>
                                        <textarea class="form-control" name="message" id="message" rows="3" placeholder="Enter your message here..."></textarea>
                                    </div>

                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                                    <button type="submit" class="btn btn-primary">Send</button>
                            </div>
                            </form>
                        </div>
                    </div>
                </div>


                <!-- JavaScript to Handle Modal Data -->
                <script>
                    // Listen for the modal show event to populate data
                    var statusModal = document.getElementById('statusModal');
                    statusModal.addEventListener('show.bs.modal', function(event) {
                        var button = event.relatedTarget; // Button that triggered the modal
                        var id = button.getAttribute('data-id');
                        var action = button.getAttribute('data-action');

                        // Update the modal's hidden input fields
                        var modalIdInput = document.getElementById('modal-id');
                        var modalActionInput = document.getElementById('modal-action');
                        modalIdInput.value = id;
                        modalActionInput.value = action;

                        // Show or hide the interview date input based on the action
                        var interviewDateGroup = document.getElementById('interview-date-group');
                        if (action === 'Approved') {
                            interviewDateGroup.style.display = 'block'; // Show interview date field if Approved
                        } else {
                            interviewDateGroup.style.display = 'none'; // Hide interview date field if Declined or other actions
                        }
                    });
                </script>


                <!-- Modal for image display -->
                <div class="modal fade" id="imageModal" tabindex="-1" aria-labelledby="imageModalLabel" aria-hidden="true">
                    <div class="modal-dialog modal-lg">
                        <div class="modal-content bg-dark">
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


            </div>
                            <!-- Footer -->
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

        <!-- Chart.js Scripts -->
        <script src="https://cdn.jsdelivr.net/npm/chart.js@2.9.4/dist/Chart.min.js"></script>


        <!-- Simple DataTables Scripts -->
        <script src="https://cdn.jsdelivr.net/npm/simple-datatables@7.1.2/dist/umd/simple-datatables.min.js"></script>
        <script src="js/datatables-simple-demo.js"></script>

</body>

</html>