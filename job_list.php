<?php
session_start();
require 'db.php'; // Include database connection

// Check if user is logged in and is an admin
if (!isset($_SESSION["id"]) || $_SESSION["role"] != 'Manager') {
    header("Location: login.php");
    exit();
}

// Fetch logged-in user data
$user_id = $_SESSION['id'];
$user_query = "SELECT first_name, last_name FROM users WHERE id = ?";
$stmt = $conn->prepare($user_query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$user_result = $stmt->get_result();
$user = $user_result->fetch_assoc();

// Fetch approved hire applications
$approved_query = "SELECT h.id, h.fName, h.lName, h.age, h.email, h.date_uploaded, h.status, h.department,
                          c.city_name AS city, h.job_position, h.interview_date, h.suitability_score, 
                          h.experience_years, h.experience_months,
                          CASE 
                              WHEN h.education != 'Other' THEN h.education
                              ELSE h.otherEducation
                          END AS education
                   FROM hiring h
                   LEFT JOIN cities c ON h.city_id = c.city_id
                   WHERE h.application_type = 'hiring' AND h.status = 'Approved'";
$approved_result = $conn->query($approved_query);

// Fetch declined hire applications
$declined_query = "SELECT h.id, h.fName, h.lName, h.age, h.email, h.date_uploaded, h.status, h.department,
                          c.city_name AS city, h.job_position, h.interview_date, h.suitability_score, 
                          h.experience_years, h.experience_months,
                          CASE 
                              WHEN h.education != 'Other' THEN h.education
                              ELSE h.otherEducation
                          END AS education
                   FROM hiring h
                   LEFT JOIN cities c ON h.city_id = c.city_id
                   WHERE h.application_type = 'hiring' AND h.status = 'Declined'";
$declined_result = $conn->query($declined_query);

// Check for query errors
if (!$approved_result || !$declined_result) {
    die("Query Failed: " . $conn->error);
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
    <title>Job List</title>
    <link href="css/styles.css" rel="stylesheet" />
    <script src="https://use.fontawesome.com/releases/v6.3.0/js/all.js" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/css/bootstrap.min.css" rel="stylesheet" crossorigin="anonymous">
</head>

<body class="sb-nav-fixed bg-dark">
    <!-- Top Navbar -->
    <nav class="sb-topnav navbar navbar-expand navbar-dark bg-dark">
        <a class="navbar-brand ps-3" href="predict_suitability.php">Microfinance</a>

        <!-- Navbar Toggle Button for collapsing navbar -->
        <button class="btn btn-link btn-sm order-1 order-lg-0 me-4 me-lg-0" id="sidebarToggle" href="#"><i class="fas fa-bars"></i></button>

        <!-- Right side of navbar -->
        <ul class="navbar-nav ms-auto bg-dark text-light">
            <!-- Notification Icon with Badge -->
            <a class="nav-link" href="#" id="notificationsDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                <i class="fas fa-bell"></i>
                <span class="badge bg-danger" id="notifCount">0</span> <!-- Dynamic count -->
            </a>

            <!-- Notifications Dropdown -->
            <div class="dropdown-menu dropdown-menu-end p-3 bg-dark text-light" aria-labelledby="notificationsDropdown" style="width: 300px;">
                <!-- Notification Header -->
                <div class="d-flex justify-content-between align-items-center mb-2 bg-dark text-light">
                    <h6 class="m-0">Notifications</h6>
                    <button id="clearAllNotifications" class="btn btn-sm btn-link text-danger">Clear All</button>
                </div>

                <!-- Notifications List -->
                <ul class="list-group bg-dark text-light" id="notificationsList">
                    <li class="list-group-item text-center text-muted" id="noNotifications">No new notifications</li>
                    <!-- Dynamic notifications will be injected here -->
                </ul>
            </div>

            <!-- User dropdown -->
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

    <!-- Sidenav -->
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
                        <a class="nav-link" href="hr_job.php">
                            <div class="sb-nav-link-icon"><i class="fas fa-chart-area"></i></div>
                           Applicant list
                        </a>
                        <a class="nav-link" href="job_list.php">
                            <div class="sb-nav-link-icon"><i class="fas fa-chart-area"></i></div>
                            Application list
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

        <!-- Main Content -->
        <div id="layoutSidenav_content">
            <main>
                <div class="container-fluid px-4">
                    <h1 class="mt-4 text-light">Job Applications List</h1>

                    <!-- Approved Applications Table -->
                    <div class="card mb-4 bg-dark">
                        <div class="card-header text-light">
                            <i class="fas fa-table me-1 text-light"></i>
                            List of Approved Hiring Applications
                        </div>
                        <div class="card-body table-responsive">
                            <table class="table table-striped table-dark">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>First Name</th>
                                        <th>Last Name</th>
                                        <th>Age</th>
                                        <th>Email</th>
                                        <th>City</th>
                                        <th>Department</th>
                                        <th>Job Position</th>
                                        <th>Experience (Years/Months)</th>
                                        <th>Education</th>
                                        <th>Suitability Score</th>
                                        <th>Interview Date</th>
                                        <th>Date Uploaded</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    if ($approved_result->num_rows > 0) {
                                        while ($row = $approved_result->fetch_assoc()) {
                                            // Display experience as Years/Months
                                            $experience = "{$row['experience_years']} Years, {$row['experience_months']} Months";

                                            echo "<tr>
                                <td>{$row['id']}</td>
                                <td>{$row['fName']}</td>
                                <td>{$row['lName']}</td>
                                <td>{$row['age']}</td>
                                <td>{$row['email']}</td>
                                <td>{$row['city']}</td>
                                <td>{$row['department']}</td>
                                <td>{$row['job_position']}</td>
                                <td>{$experience}</td>
                                <td>{$row['education']}</td>
                                <td>{$row['suitability_score']}</td>
                                <td>{$row['interview_date']}</td>
                                <td>{$row['date_uploaded']}</td>
                              </tr>";
                                        }
                                    } else {
                                        echo "<tr><td colspan='12'>No approved applications found.</td></tr>";
                                    }
                                    ?>
                                </tbody>
                            </table>
                        </div>
                    </div>



                    <!-- Button to Download Approved Applications as Excel -->
                    <button id="downloadApprovedBtn" class="btn btn-success mb-3">Download Approved Applications as Excel</button>

                    <!-- Declined Applications Table -->
                    <div class="card mb-4 bg-dark">
                        <div class="card-header text-light">
                            <i class="fas fa-table me-1 text-light"></i>
                            List of Declined Hiring Applications
                        </div>
                        <div class="card-body table-responsive">
                            <table class="table table-striped table-dark">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>First Name</th>
                                        <th>Last Name</th>
                                        <th>Age</th>
                                        <th>Email</th>
                                        <th>City</th>
                                        <th>Department</th>
                                        <th>Job Position</th>
                                        <th>Experience (Years/Months)</th>
                                        <th>Education</th>
                                        <th>Suitability Score</th>
                                        <th>Interview Date</th>
                                        <th>Date Uploaded</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    if ($declined_result->num_rows > 0) {
                                        while ($row = $declined_result->fetch_assoc()) {
                                            // Display experience as Years/Months
                                            $experience = "{$row['experience_years']} Years, {$row['experience_months']} Months";

                                            echo "<tr>
                                <td>{$row['id']}</td>
                                <td>{$row['fName']}</td>
                                <td>{$row['lName']}</td>
                                <td>{$row['age']}</td>
                                <td>{$row['email']}</td>
                                <td>{$row['city']}</td>
                                <td>{$row['department']}</td>
                                <td>{$row['job_position']}</td>
                                <td>{$experience}</td>
                                <td>{$row['education']}</td>
                                <td>{$row['suitability_score']}</td>
                                <td>{$row['interview_date']}</td>
                                <td>{$row['date_uploaded']}</td>
                              </tr>";
                                        }
                                    } else {
                                        echo "<tr><td colspan='12'>No declined applications found.</td></tr>";
                                    }
                                    ?>
                                </tbody>
                            </table>
                        </div>
                    </div>



                    <!-- Button to Download Declined Applications as Excel -->
                    <button id="downloadDeclinedBtn" class="btn btn-danger mb-3">Download Declined Applications as Excel</button>
                </div>
            </main>
            <footer class="bg-dark text-center py-3 mt-5 text-light">
                <div class="container">
                    <small>Copyright © Microfinance 2025</small><br>
                    <button type="button" class="btn btn-link text-light" data-bs-toggle="modal" data-bs-target="#policiesModal">
                        Policies
                    </button>
                </div>
            </footer>
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

    <!-- JavaScript to handle download actions -->
    <script>
        document.getElementById('downloadApprovedBtn').addEventListener('click', function() {
            window.location.href = 'download_approved.php'; // Redirect to download the approved Excel file
        });

        document.getElementById('downloadDeclinedBtn').addEventListener('click', function() {
            window.location.href = 'download_declined.php'; // Redirect to download the declined Excel file
        });
    </script>


          <!-- JavaScript to Handle Notifications (Like Facebook) -->
          <script>
                    document.addEventListener('DOMContentLoaded', function() {
                        // Fetch notifications on page load
                        fetchNotifications();

                        // Mark notifications as seen when you click the "Mark as Read" button
                        document.getElementById('notificationsList').addEventListener('click', function(e) {
                            if (e.target.classList.contains('mark-as-read')) {
                                const notificationId = e.target.getAttribute('data-id');
                                markNotificationAsRead(notificationId);
                            }
                        });

                        // Clear all notifications when the "Clear All" button is clicked
                        const clearAllButton = document.getElementById('clearAllNotifications');
                        clearAllButton.addEventListener('click', function() {
                            clearAllNotifications();
                        });
                    });

                    // Function to fetch notifications via AJAX
                    function fetchNotifications() {
                        fetch('get_notifications.php')
                            .then(response => response.json())
                            .then(data => {
                                const notificationCount = document.getElementById('notifCount');
                                const notificationsList = document.getElementById('notificationsList');

                                if (data.count > 0) {
                                    // Update the badge with the number of new applicants
                                    notificationCount.textContent = data.count;
                                    notificationCount.style.display = 'inline';

                                    // Clear the default "No new notifications" message
                                    notificationsList.innerHTML = '';

                                    // Populate the dropdown with new applicants, including ID and date_uploaded
                                    data.applicants.forEach(applicant => {
                                        const formattedDate = new Date(applicant.date_uploaded).toLocaleString(); // Format the date

                                        const listItem = document.createElement('li');
                                        listItem.classList.add('list-group-item', 'd-flex', 'justify-content-between', 'align-items-start', 'bg-dark');
                                        listItem.innerHTML = `
        <div class="ms-2 me-auto bg-dark text-light">
            <div class="fw-bold text-light">${applicant.name}</div>
            <span>New applicant applied on ${formattedDate} (ID: ${applicant.id}).</span>
        </div>
        <button class="btn btn-sm btn-outline-success mark-as-read" data-id="${applicant.id}">
            Mark as Read
        </button>`;
                                        notificationsList.appendChild(listItem);
                                    });

                                } else {
                                    // Hide the badge if no notifications
                                    notificationCount.style.display = 'none';

                                    // Show "No new notifications" message
                                    notificationsList.innerHTML = '<li class="list-group-item text-center text-muted">No new notifications</li>';
                                }
                            });
                    }

                    // Function to mark a specific notification as read via AJAX
                    function markNotificationAsRead(notificationId) {
                        fetch('mark_notifications_as_seen.php', {
                                method: 'POST',
                                headers: {
                                    'Content-Type': 'application/json'
                                },
                                body: JSON.stringify({
                                    id: notificationId
                                })
                            })
                            .then(response => response.json())
                            .then(data => {
                                if (data.success) {
                                    // Remove the notification from the list
                                    const listItem = document.querySelector(`button[data-id="${notificationId}"]`).parentElement;
                                    listItem.remove();

                                    // Update notification count
                                    const notificationCount = document.getElementById('notifCount');
                                    const newCount = parseInt(notificationCount.textContent) - 1;
                                    if (newCount > 0) {
                                        notificationCount.textContent = newCount;
                                    } else {
                                        notificationCount.style.display = 'none';

                                        // Show "No new notifications" message if all notifications are cleared
                                        const notificationsList = document.getElementById('notificationsList');
                                        notificationsList.innerHTML = '<li class="list-group-item text-center text-muted">No new notifications</li>';
                                    }
                                }
                            });
                    }

                    // Function to clear all notifications via AJAX
                    function clearAllNotifications() {
                        fetch('clear_all_notifications.php')
                            .then(response => response.json())
                            .then(data => {
                                if (data.success) {
                                    // Clear all notifications from the list and update badge
                                    const notificationsList = document.getElementById('notificationsList');
                                    notificationsList.innerHTML = '<li class="list-group-item text-center text-muted">No new notifications</li>';
                                    const notificationCount = document.getElementById('notifCount');
                                    notificationCount.style.display = 'none';
                                }
                            });
                    }
                </script>




    <!-- Bootstrap Bundle and Other Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js" crossorigin="anonymous"></script>
    <script src="js/scripts.js"></script>
</body>

</html>