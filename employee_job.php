<?php
session_start();
require 'db.php'; // Include database connection

// Check if user is logged in and is an Employee
if (!isset($_SESSION["id"]) || $_SESSION["role"] != 'Staff') {
    header("Location: login.php");
    exit();
}

// Generate a CSRF token and store it in the session
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32)); // Generates a random 32-character token
}

$csrf_token = $_SESSION['csrf_token'];


?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />
    <meta name="description" content="" />
    <meta name="author" content="" />
    <title>Applicant List</title>

    <link href="https://cdn.jsdelivr.net/npm/simple-datatables@7.1.2/dist/style.min.css" rel="stylesheet" />
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="css/styles.css" rel="stylesheet" />
    <script src="https://use.fontawesome.com/releases/v6.3.0/js/all.js" crossorigin="anonymous"></script>
</head>

<body class="sb-nav-fixed bg-dark">
    <nav class="sb-topnav navbar navbar-expand navbar-dark bg-dark">
        <a class="navbar-brand ps-3" href="employee_job.php">Microfinance</a>
        <button class="btn btn-link btn-sm order-1 order-lg-0 me-4 me-lg-0" id="sidebarToggle" href="#"><i class="fas fa-bars"></i></button>
        <ul class="navbar-nav ms-auto">
            <li class="nav-item dropdown">
                <a class="nav-link dropdown-toggle" id="navbarDropdown" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
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
                <div class="sb-sidenav-footer bg-dark">
                    <div class="small">Logged in as: <?php echo htmlspecialchars($_SESSION['user_name']); ?></div>
                </div>
            </nav>
        </div>

        <div id="layoutSidenav_content" class="bg-dark-low">

            <div class="container-fluid px-4 bg-dark-low">
                <h1 class="mt-4 text-light bg-dark-low">Job Hiring Details</h1>
                <div class="card mb-4 bg-dark-low">
                    <div class="card-header text-light bg-dark-low">
                        <i class="fas fa-table me-1"></i> Pending Hiring Applications
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
                                    <th>Status</th>
                                    <th>Date Uploaded</th>
                                    <th>Experience (Months)</th>
                                    <th>Experience (Years)</th>
                                    <th>Former Company</th>
                                    <th>Education</th>
                                    <th>Other Education</th>
                                    <th>Interview Day</th>
                                    <th>Action</th> <!-- New Action Column for Notifying -->
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                // Fetch only pending applications from the `hiring` table
                                $query = "SELECT hiring.*,  hiring.suitability_score 
                                          FROM hiring
                                          WHERE hiring.status = 'pending'"; // Fetch pending applications only

                                $result = mysqli_query($conn, $query);

                                if (!$result) {
                                    die('Query failed: ' . mysqli_error($conn));
                                }

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
                                            <td><?php echo htmlspecialchars($row['status']); ?></td>
                                            <td><?php echo htmlspecialchars($row['date_uploaded']); ?></td>
                                            <td><?php echo htmlspecialchars($row['experience_months']); ?></td>
                                            <td><?php echo htmlspecialchars($row['experience_years']); ?></td>
                                            <td><?php echo htmlspecialchars($row['former_company']); ?></td>
                                            <td><?php echo htmlspecialchars($row['education']); ?></td>
                                            <td><?php echo htmlspecialchars($row['otherEducation']); ?></td>
                                            <td><?php echo htmlspecialchars($row['interview_date']); ?></td>
                                            <td>
                                                <!-- Notify Button -->
                                                <button class="btn btn-primary notify-btn" data-email="<?php echo htmlspecialchars($row['email']); ?>" data-bs-toggle="modal" data-bs-target="#notifyModal">Notify</button>

                                            </td>
                                        </tr>
                                <?php
                                    }
                                } else {
                                    echo "<tr><td colspan='17' class='text-center'>No records found.</td></tr>";
                                }
                                ?>
                            </tbody>
                        </table>
                    </div>

                </div>
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

        <!-- Modal for Notifying -->
        <div class="modal fade" id="notifyModal" tabindex="-1" aria-labelledby="notifyModalLabel" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="notifyModalLabel">Send Email Notification</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <form action="send_email.php" method="POST">
                        <div class="modal-body">
                            <div class="mb-3">
                                <label for="applicantEmail" class="form-label">Applicant Email</label>
                                <input type="email" class="form-control" id="applicantEmail" name="applicant_email" readonly>
                            </div>
                            <div class="mb-3">
                                <label for="message" class="form-label">Message</label>
                                <textarea class="form-control" id="message" name="message" rows="4" required></textarea>
                            </div>
                            <!-- Include CSRF token in the form as a hidden input field -->
                            <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                            <button type="submit" class="btn btn-primary">Send Email</button>
                        </div>
                    </form>
                </div>
            </div>
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

        <script>
            document.addEventListener('DOMContentLoaded', function() {
                // Use event delegation to handle clicks on dynamically added or manipulated notify buttons
                document.addEventListener('click', function(event) {
                    if (event.target && event.target.classList.contains('notify-btn')) {
                        // Get the email from the data attribute and set it in the modal input
                        const email = event.target.getAttribute('data-email');
                        const applicantEmailInput = document.getElementById('applicantEmail');
                        applicantEmailInput.value = email;
                    }
                });
            });
        </script>



        <!-- Bootstrap Bundle JS -->
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

        <script src="https://cdn.jsdelivr.net/npm/simple-datatables@7.1.2/dist/umd/simple-datatables.min.js"></script>
        <script src="js/datatables-simple-demo.js"></script>
</body>

</html>