<?php
session_start();
require 'db.php';  // Your database connection

// Ensure the user is logged in and is an instructor
if (!isset($_SESSION['id']) || $_SESSION['role'] != 'Trainer') {
    echo "You are not authorized to view requests.";
    exit();
}

$instructor_id = $_SESSION['id'];  // Get instructor ID from session

// Fetch the instructor's first and last name
$name_query = "SELECT first_name, last_name FROM users WHERE id = ?";
$stmt_name = $conn->prepare($name_query);
$stmt_name->bind_param("i", $instructor_id);
$stmt_name->execute();
$result_name = $stmt_name->get_result();

if ($result_name->num_rows > 0) {
    $instructor = $result_name->fetch_assoc();
    $instructor_name = $instructor['first_name'] . ' ' . $instructor['last_name'];  // Concatenate first and last name
} else {
    $instructor_name = 'Unknown';  // Fallback in case the instructor is not found
}
$stmt_name->close();

// Fetch unread messages count
$unread_query = "SELECT COUNT(*) as unread_count FROM feedback WHERE instructor_id = ? AND status = 'unread'";
$stmt_unread = $conn->prepare($unread_query);
$stmt_unread->bind_param("i", $instructor_id);
$stmt_unread->execute();
$result_unread = $stmt_unread->get_result();
$unread_count = $result_unread->fetch_assoc()['unread_count'];
$stmt_unread->close();

// Fetch messages for the instructor, including the file_path
$query = "
    SELECT feedback.id, feedback.message, feedback.date_sent, feedback.file_path, users.first_name, users.last_name, feedback.status
    FROM feedback
    JOIN users ON feedback.employee_id = users.id
    WHERE feedback.instructor_id = ? 
    ORDER BY feedback.date_sent DESC";  // Show newest messages first
$stmt = $conn->prepare($query);

if ($stmt === false) {
    die("Prepare failed: " . $conn->error);
}

$stmt->bind_param("i", $instructor_id);
$stmt->execute();
$result = $stmt->get_result();

// Mark message as read when clicked
if (isset($_GET['read_id'])) {
    $message_id = $_GET['read_id'];
    $update_query = "UPDATE feedback SET status = 'read' WHERE id = ?";
    $update_stmt = $conn->prepare($update_query);
    $update_stmt->bind_param("i", $message_id);
    $update_stmt->execute();
    $update_stmt->close();
    header("Location: instructor.php");  // Refresh the page after marking as read
    exit();
}

// Delete message
if (isset($_GET['delete_id'])) {
    $message_id = $_GET['delete_id'];
    $delete_query = "DELETE FROM feedback WHERE id = ?";
    $delete_stmt = $conn->prepare($delete_query);
    $delete_stmt->bind_param("i", $message_id);
    $delete_stmt->execute();
    $delete_stmt->close();
    header("Location: instructor.php");  // Refresh the page after deleting
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
    <title>Dashboard - Trainer</title>
    <link href="https://cdn.jsdelivr.net/npm/simple-datatables@7.1.2/dist/style.min.css" rel="stylesheet" />
    <link href="css/styles.css" rel="stylesheet" />
    <script src="https://use.fontawesome.com/releases/v6.3.0/js/all.js" crossorigin="anonymous"></script>
</head>

<body class="sb-nav-fixed">
    <nav class="sb-topnav navbar navbar-expand navbar-dark bg-dark">
        <!-- Navbar Brand-->
        <a class="navbar-brand ps-3" href="instructor.php">Microfinance</a>
        <!-- Sidebar Toggle-->
        <button class="btn btn-link btn-sm order-1 order-lg-0 me-4 me-lg-0" id="sidebarToggle" href="#!"><i class="fas fa-bars"></i></button>
        <!-- Navbar Search-->
        <form class="d-none d-md-inline-block form-inline ms-auto me-0 me-md-3 my-2 my-md-0">
            <div class="input-group">
                <span class="text-white">Unread Messages: <?= $unread_count ?></span>
            </div>
        </form>
        <!-- Navbar-->
        <ul class="navbar-nav ms-auto ms-md-0 me-3 me-lg-4">
            <li class="nav-item dropdown">
                <a class="nav-link dropdown-toggle" id="navbarDropdown" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false"><i class="fas fa-user fa-fw"></i></a>
                <ul class="dropdown-menu dropdown-menu-end bg-dark" aria-labelledby="navbarDropdown">
                    <li><a class="dropdown-item text-muted" href="logout.php">Logout</a></li>
                    <li><a class="dropdown-item text-muted" href="password.php">Change password</a></li>
                </ul>
            </li>
        </ul>
    </nav>
    <div id="layoutSidenav">
        <div id="layoutSidenav_nav">
            <nav class="sb-sidenav accordion sb-sidenav-dark" id="sidenavAccordion">
                <div class="sb-sidenav-menu">
                    <div class="nav">
                        <div class="sb-sidenav-menu-heading"></div>
                        <a class="nav-link" href="instructor.php">
                            <div class="sb-nav-link-icon"><i class="fas fa-tachometer-alt"></i></div>
                            Report Log
                        </a>
                        <a class="nav-link" href="task.php">
                            <div class="sb-nav-link-icon"><i class="fas fa-tasks"></i></div>
                            Task
                        </a>
                        <a class="nav-link" href="quiz.php">
                            <div class="sb-nav-link-icon"><i class="fas fa-book"></i></div>
                            Manage Training
                        </a>
                        <a class="nav-link" href="employee_list.php">
                            <div class="sb-nav-link-icon"><i class="fas fa-users"></i></div>
                            Employee List
                        </a>
                        <a class="nav-link" href="report_app.php">
                            <div class="sb-nav-link-icon"><i class="fas fa-users"></i></div>
                            Report
                        </a>
                    </div>
                </div>
                <div class="sb-sidenav-footer bg-dark">
                    <div class="small">Logged in as:</div>
                    <strong><?php echo htmlspecialchars($instructor_name); ?></strong>
                </div>
            </nav>
        </div>

        <div id="layoutSidenav_content" class="bg-dark">
            <main>
                <div class="container-fluid px-4">
                    <h1 class="mt-4 text-light">Dashboard</h1>
                    <ol class="breadcrumb mb-4">
                        <li class="breadcrumb-item active">Dashboard</li>
                    </ol>

                    <!-- Requests Section -->
                    <div class="container mt-5">
                        <h2 class="text-light">Feedback</h2>
                        <table class="table table-dark table-striped">
                            <thead>
                                <tr>
                                    <th>Employee</th>
                                    <th>Message</th>
                                    <th>Date Sent</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                if ($result->num_rows > 0) {
                                    while ($row = $result->fetch_assoc()) {
                                        $status_display = $row['status'] == 'unread' ? "<strong>(Unread)</strong>" : "(Read)";
                                        $file_icon = !empty($row['file_path']) ? "<i class='fas fa-paperclip'></i>" : ""; // Icon to indicate file attachment

                                        echo "<tr>
                            <td>{$row['first_name']} {$row['last_name']}</td>
                            <td>" . substr($row['message'], 0, 20) . "...</td>
                            <td>{$row['date_sent']}</td>
                            <td>$status_display $file_icon</td>
                            <td>
                                <button class='btn btn-sm btn-info' data-bs-toggle='modal' data-bs-target='#viewMessageModal' 
                                    data-message='{$row['message']}' 
                                    data-employee='{$row['first_name']} {$row['last_name']}' 
                                    data-date='{$row['date_sent']}' 
                                    data-file='{$row['file_path']}'>View</button>
                                <a href='instructor.php?read_id={$row['id']}' class='btn btn-sm btn-success'>Mark as Read</a>
                                <a href='instructor.php?delete_id={$row['id']}' class='btn btn-sm btn-danger'>Delete</a>
                            </td>
                          </tr>";
                                    }
                                } else {
                                    echo "<tr><td colspan='5'>No feedback found.</td></tr>";
                                }
                                ?>
                            </tbody>
                        </table>
                    </div>

                </div>
            </main>
            <footer class="py-4 bg-light mt-auto bg-dark">
                <div class="container-fluid px-4">
                    <div class="d-flex align-items-center justify-content-between small">
                        <div class="text-muted">Copyright &copy; Microfinance 2025</div>
                    </div>
                </div>
            </footer>
        </div>
    </div>


    <!-- Modal to display message -->
    <div class="modal fade" id="viewMessageModal" tabindex="-1" aria-labelledby="viewMessageLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header bg-dark text-light">
                    <h5 class="modal-title" id="viewMessageLabel">Feedback Details</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body bg-dark text-light">
                    <p><strong>Employee: </strong><span id="employeeName"></span></p>
                    <p><strong>Date Sent: </strong><span id="dateSent"></span></p>
                    <p><strong>Message: </strong></p>
                    <p id="messageContent"></p>
                    <div id="fileDownloadSection" style="display:none;">
                        <p><strong>Attached File: </strong><a id="downloadFileLink" href="" target="_blank">Download</a></p>
                    </div>
                </div>
                <div class="modal-footer bg-dark">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>


   
    </div>

    <!-- JavaScript to populate the modal with data -->
    <script>
        var viewMessageModal = document.getElementById('viewMessageModal');
        viewMessageModal.addEventListener('show.bs.modal', function(event) {
            var button = event.relatedTarget; // Button that triggered the modal
            var message = button.getAttribute('data-message'); // Extract info from data-* attributes
            var employee = button.getAttribute('data-employee');
            var date = button.getAttribute('data-date');

            // Update the modal's content
            var modalTitle = viewMessageModal.querySelector('.modal-title');
            var employeeName = viewMessageModal.querySelector('#employeeName');
            var dateSent = viewMessageModal.querySelector('#dateSent');
            var messageContent = viewMessageModal.querySelector('#messageContent');

            employeeName.textContent = employee;
            dateSent.textContent = date;
            messageContent.textContent = message;
        });
    </script>
    <script>
        // Modal data population script
        document.addEventListener('DOMContentLoaded', function() {
            var viewMessageModal = document.getElementById('viewMessageModal');
            viewMessageModal.addEventListener('show.bs.modal', function(event) {
                var button = event.relatedTarget;
                var employee = button.getAttribute('data-employee');
                var date = button.getAttribute('data-date');
                var message = button.getAttribute('data-message');
                var file = button.getAttribute('data-file');

                // Populate the modal with data
                var employeeName = document.getElementById('employeeName');
                var dateSent = document.getElementById('dateSent');
                var messageContent = document.getElementById('messageContent');
                var fileDownloadSection = document.getElementById('fileDownloadSection');
                var downloadFileLink = document.getElementById('downloadFileLink');

                employeeName.textContent = employee;
                dateSent.textContent = date;
                messageContent.textContent = message;

                // Check if a file is attached
                if (file) {
                    fileDownloadSection.style.display = 'block';
                    downloadFileLink.href = file; // Assuming 'file_path' contains the URL to the file
                } else {
                    fileDownloadSection.style.display = 'none';
                }
            });
        });
    </script>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js" crossorigin="anonymous"></script>
    <script src="js/scripts.js"></script>
</body>

</html>