<?php
session_start(); // Start the session at the top of the file

// Check if the user is logged in and if the role is for HR manager (role 1)
if (!isset($_SESSION['id']) || $_SESSION['role'] != 'Manager') { 
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['id']; // Logged-in HR manager's ID

require 'db.php';
// Fetch all trainers (role 3 is assumed for trainers)
$trainers = $conn->query("SELECT id, first_name, last_name FROM users WHERE role = 'Trainer'");

$conn->close(); // Close the database connection
require 'csrf_protection.php';
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />
    <meta name="description" content="" />
    <meta name="author" content="" />
    <title>Send Report</title>
    <script src="https://cdn.jsdelivr.net/npm/simple-datatables@7.1.2/dist/umd/simple-datatables.min.js"></script>
    <link href="https://cdn.jsdelivr.net/npm/simple-datatables@7.1.2/dist/style.min.css" rel="stylesheet" />
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="css/styles.css" rel="stylesheet" />
    <script src="https://use.fontawesome.com/releases/v6.3.0/js/all.js" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script> <!-- Chart.js -->

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
    
    <div id="layoutSidenav">
        <!-- Sidebar -->
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

        <!-- Main Content Area for Messaging Form -->
        <div id="layoutSidenav_content" class="bg-dark">
            <div class="container mt-4">
                <h2 class="text-light">Send Message and File to Trainer</h2>
                <form action="send_job.php" method="POST" enctype="multipart/form-data">
                <?php csrf_token_field(); ?> <!-- CSRF Token Field -->
                    <div class="mb-3">
                        <label for="trainer" class="form-label text-light">Trainer:</label>
                        <select id="trainer" class="form-control" name="trainer_id" required>
                            <option value="" disabled selected>Select Trainer</option>
                            <?php while ($trainer = $trainers->fetch_assoc()): ?>
                                <option value="<?php echo $trainer['id']; ?>">
                                    <?php echo $trainer['first_name'] . ' ' . $trainer['last_name']; ?>
                                </option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="message" class="form-label text-light">Message:</label>
                        <textarea id="message" class="form-control" name="message" rows="5" required></textarea>
                    </div>
                    <div class="mb-3">
                        <label for="file" class="form-label text-light">Attach File (optional):</label>
                        <input type="file" id="file" class="form-control" name="attachment" accept=".pdf,.doc,.docx,.xls,.xlsx">
                    </div>
                    <button type="submit" class="btn btn-primary">Send</button>
                </form>
            </div>
        </div>

    </div>

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


    <!-- Bootstrap Bundle JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="js/scripts.js"></script>

</body>

</html>
