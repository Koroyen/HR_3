<?php
session_start();
include('db.php');

// Check if task ID is provided via GET
if (isset($_GET['task_id'])) {
    $task_id = $_GET['task_id'];

    // Fetch the task details
    $task_query = "SELECT * FROM tasks WHERE id = ?";
    $stmt = $conn->prepare($task_query);
    $stmt->bind_param("i", $task_id);
    $stmt->execute();
    $task_result = $stmt->get_result();

    if ($task_result->num_rows == 1) {
        $task = $task_result->fetch_assoc();
    } else {
        echo "<script>alert('Task not found!'); window.location.href = 'task.php';</script>";
        exit();
    }
} else {
    echo "<script>alert('No task ID provided!'); window.location.href = 'task.php';</script>";
    exit();
}

// Handle form submission to update the task
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $task_description = $_POST['task_description'];
    $due_date = $_POST['due_date'];
    $status = $_POST['status'];

    // Update the task in the database
    $update_query = "UPDATE tasks SET task_description = ?, due_date = ?, status = ? WHERE id = ?";
    $stmt = $conn->prepare($update_query);
    $stmt->bind_param("sssi", $task_description, $due_date, $status, $task_id);

    if ($stmt->execute()) {
        echo "<script>alert('Task updated successfully!'); window.location.href = 'task.php';</script>";
    } else {
        echo "<script>alert('Error updating task!');</script>";
    }
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
    <title>Task Management</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" />
    <link href="https://cdn.jsdelivr.net/npm/simple-datatables@7.1.2/dist/style.min.css" rel="stylesheet" />
    <link href="css/styles.css" rel="stylesheet" />
    <script src="https://use.fontawesome.com/releases/v6.3.0/js/all.js" crossorigin="anonymous"></script>
</head>

<body>
<nav class="sb-topnav navbar navbar-expand navbar-dark bg-dark">
        <!-- Navbar Brand-->
        <a class="navbar-brand ps-3" href="quiz.php">Microfinance</a>
        <!-- Sidebar Toggle-->
       
        <!-- Navbar Search-->
        <form class="d-none d-md-inline-block form-inline ms-auto me-0 me-md-3 my-2 my-md-0">
            <div class="input-group">
               
            </div>
        </form>
        <!-- Navbar-->
        <ul class="navbar-nav ms-auto ms-md-0 me-3 me-lg-4">
            <li class="nav-item dropdown">
               
                <ul class="dropdown-menu dropdown-menu-end bg-dark" aria-labelledby="navbarDropdown">
                    
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
                    
                </div>
            </div>
            <div class="sb-sidenav-footer bg-dark">
                
              
            </div>
        </nav>
    </div>    

<div id="layoutSidenav_content" class="bg-dark">
    <div class="container mt-5 bg-dark text-light">
        <h2>Edit Task</h2>
        <form action="edit_task.php?task_id=<?php echo $task_id; ?>" method="POST">
            <div class="form-group">
                <label for="task_description">Task Description:</label>
                <input type="text" class="form-control" id="task_description" name="task_description" 
                       value="<?php echo htmlspecialchars($task['task_description']); ?>" required>
            </div>
            <div class="form-group mt-3">
                <label for="due_date">Due Date:</label>
                <input type="date" class="form-control" id="due_date" name="due_date" 
                       value="<?php echo htmlspecialchars($task['due_date']); ?>" required>
            </div>
            <div class="form-group mt-3">
                <label for="status">Status:</label>
                <select class="form-control" id="status" name="status" required>
                    <option value="pending" <?php if ($task['status'] == 'pending') echo 'selected'; ?>>Pending</option>
                    <option value="in_progress" <?php if ($task['status'] == 'in_progress') echo 'selected'; ?>>In Progress</option>
                    <option value="completed" <?php if ($task['status'] == 'completed') echo 'selected'; ?>>Completed</option>
                </select>
            </div>
            <button type="submit" class="btn btn-primary mt-3">Update Task</button>
            <a href="task.php" class="btn btn-secondary mt-3">Cancel</a>
        </form>
    </div>
</div>
</body>
</html>
