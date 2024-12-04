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
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Task</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css">
</head>
<body>
    <div class="container mt-5">
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
</body>
</html>
