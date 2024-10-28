<?php
session_start();
require 'db.php';

// Check if user is logged in
if (!isset($_SESSION["id"])) {
    header("Location: login.php");
    exit();
} else {
    // Fetch the logged-in user's ID and email from the session
    $id = $_SESSION["id"];
    $result = mysqli_query($conn, "SELECT email FROM users WHERE id = $id");
    $user = mysqli_fetch_assoc($result);
    $userEmail = $user['email'];
}

if (isset($_POST['submit'])) {
    // Check if the user's email has already been used for an application
    $email_check_query = "SELECT * FROM certificate WHERE email = ?";
    $stmt = $conn->prepare($email_check_query);

    if ($stmt === false) {
        echo "<p>Error preparing the query: " . htmlspecialchars($conn->error) . "</p>";
        exit();
    }

    // Bind the email value to the query
    $stmt->bind_param('s', $userEmail);

    // Execute the query
    $stmt->execute();

    // Get the result
    $result = $stmt->get_result();

    // If a row is found, the user has already applied
    if ($result->num_rows > 0) {
        // Close the statement and show alert to the user
        $stmt->close();
        echo "<script>
                alert('You have already submitted the form. You cannot submit again.');
                window.location.href = 'home.php'; // Redirect back to home page
              </script>";
        exit(); // Stop the rest of the script from executing
    } else {
        // Process the form and store the new submission
        $fName = mysqli_real_escape_string($conn, $_POST['fName']);
        $lName = mysqli_real_escape_string($conn, $_POST['lName']);
        $sex = mysqli_real_escape_string($conn, $_POST['sex']);
        $age = mysqli_real_escape_string($conn, $_POST['Age']);
        $street = mysqli_real_escape_string($conn, $_POST['street']);
        $barangay = mysqli_real_escape_string($conn, $_POST['barangay']);
        $applicationType = mysqli_real_escape_string($conn, $_POST['application_type']);

        // Handle Certificate of Enrollment (ID) upload
        $id_name = $_FILES['ids']['name'];
        $id_temp = $_FILES['ids']['tmp_name'];
        $id_folder = 'certi/' . $id_name;

        // Handle Birth Certificate upload
        $birthc_name = $_FILES['birthc']['name'];
        $birthc_temp = $_FILES['birthc']['tmp_name'];
        $birthc_folder = 'certi/' . $birthc_name;

        // Fetch city_id based on the selected city from the form
        $city_id = (int)$_POST['city'];

        // Prepare the insert query with user_id and city_id
        $insert_query = "INSERT INTO certificate (user_id, fName, lName, sex, age, email, street, barangay, city_id, ids, birthc, application_type) 
                         VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt_insert = $conn->prepare($insert_query);

        if ($stmt_insert === false) {
            echo "<p>Error preparing the insert query: " . htmlspecialchars($conn->error) . "</p>";
            exit();
        }

        // Bind the form values to the insert query, including user_id and city_id
        $stmt_insert->bind_param('isssisssssss', $id, $fName, $lName, $sex, $age, $userEmail, $street, $barangay, $city_id, $id_name, $birthc_name, $applicationType);

        // Execute the insert query
        if ($stmt_insert->execute()) {
            // Move uploaded files to the folder
            if (move_uploaded_file($id_temp, $id_folder) && move_uploaded_file($birthc_temp, $birthc_folder)) {
                echo "<script>
                        alert('Form submitted successfully!');
                        window.location.href = 'home.php'; // Redirect after submission
                      </script>";
            } else {
                echo "<script>
                        alert('File upload failed.');
                        window.location.href = 'home.php'; // Redirect to home page
                      </script>";
            }
        } else {
            echo "<p>Error inserting data: " . htmlspecialchars($stmt_insert->error) . "</p>";
        }

        // Close the insert statement
        $stmt_insert->close();
    }

    // Close the original statement
    $stmt->close();
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
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/css/bootstrap.min.css" rel="stylesheet" crossorigin="anonymous">
</head>

<body class="sb-nav-fixed bg-dark">

    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-7">
                <div class="card shadow-lg border-0 rounded-lg mt-5 bg-dark">
                    <div class="card-header">
                        <h3 class="text-center font-weight-light my-4 text-light">Fill up</h3>
                    </div>
                    <div class="card-body">
                        <form action="" method="post" enctype="multipart/form-data" autocomplete="off">
                            <div class="form-floating mb-3">
                                <input class="form-control" id="fName" name="fName" type="text" required placeholder="Enter your first name" />
                                <label for="fName">First name</label>
                            </div>
                            <div class="form-floating mb-3">
                                <input class="form-control" id="lName" name="lName" type="text" required placeholder="Enter your last name" />
                                <label for="lName">Last name</label>
                            </div>

                            <div class="form-floating mb-3">
                                <select class="form-control" id="sex" name="sex" required>
                                    <option value="" disabled selected>Select your sex</option>
                                    <option value="Male">Male</option>
                                    <option value="Female">Female</option>
                                </select>
                                <label for="sex">Sex</label>
                            </div>

                            <div class="form-floating mb-3">
                                <input class="form-control" id="Age" name="Age" type="age" required placeholder="Enter your Age" />
                                <label for="Age">Age</label>
                            </div>

                            <div class="form-floating mb-3">
                                <input class="form-control" id="email" name="email" type="email" required placeholder="Enter your email" />
                                <label for="email">Email</label>
                            </div>

                            <div class="form-floating mb-3">
                                <input class="form-control" id="street" name="street" type="text" required placeholder="Enter your Street" />
                                <label for="street">Street</label>
                            </div>

                            <div class="form-floating mb-3">
                                <input class="form-control" id="barangay" name="barangay" type="text" required placeholder="Enter your Barangay/Subdivision" />
                                <label for="barangay">Barangay/Subdivision</label>
                            </div>
                            
                            <div class="form-floating mb-3">
    <select class="form-control" id="city" name="city" required>
        <option value="">Select City</option>
        <?php
        $city_query = "SELECT city_id, city_name FROM cities";
        $city_result = mysqli_query($conn, $city_query);

        while ($city = mysqli_fetch_assoc($city_result)) {
            echo "<option value='" . $city['city_id'] . "'>" . $city['city_name'] . "</option>";
        }
        ?>
    </select>
    <label for="city">City</label>
</div>

                            <div class="form-floating mb-3">
                                <label for="ids" style="font-size: 1.2rem; position: absolute; top: -10px;">
                                    Upload your ID
                                </label>
                                <input class="form-control" id="ids" name="ids" type="file" required accept="image/*" style="height: 100px; padding: 50px;">
                            </div>

                            <div class="form-floating mb-3">
                                <label for="birthc" style="font-size: 1.2rem; position: absolute; top: -10px;">
                                    Upload your Birth Certificate
                                </label>
                                <input class="form-control" id="birthc" name="birthc" type="file" required accept="image/*" style="height: 100px; padding: 50px;">
                            </div>
                            <input type="hidden" name="application_type" value="certificate"> <!-- Or "hiring" based on the page -->
                            <div class="mt-4 mb-0 text-center">
                                <button type="submit" name="submit" class="btn btn-success btn-block">Submit</button>
                            </div>
                        </form>
                    </div>
                    <div class="card-footer text-center py-3">
                        <div class="small"><a href="home.php" class="text-muted">Go Back</a></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js" crossorigin="anonymous"></script>
    <script src="js/scripts.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/2.8.0/Chart.min.js" crossorigin="anonymous"></script>
    <script src="assets/demo/chart-area-demo.js"></script>
    <script src="assets/demo/chart-bar-demo.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/simple-datatables@7.1.2/dist/umd/simple-datatables.min.js" crossorigin="anonymous"></script>
    <script src="js/datatables-simple-demo.js"></script>

</body>

</html>