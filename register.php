<?php 
session_start();
require 'db.php';

if (!empty($_SESSION["id"])) {
    header("Location: index.php");
    exit();
}

if (isset($_POST["submit"])) {
    $fName = $_POST["fName"];
    $lName = $_POST["lName"];
    $email = $_POST["email"];
    $password = $_POST["password"];
    $confirmpassword = $_POST["confirmpassword"];

    // Validate input fields (basic validation)
    if (empty($fName) || empty($lName) || empty($email) || empty($password) || empty($confirmpassword)) {
        echo "<script>alert('All fields are required!');</script>";
        exit();
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        echo "<script>alert('Invalid email format!');</script>";
        exit();
    }

    // Validate password length (at least 8 characters)
    if (strlen($password) < 8) {
        echo "<script>alert('Password must be at least 8 characters long!');</script>";
        exit();
    }

    if ($password !== $confirmpassword) {
        echo "<script>alert('Passwords do not match!');</script>";
        exit();
    }

    // Check for duplicate email
    $stmt = $conn->prepare("SELECT * FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        echo "<script>alert('Email already exists!');</script>";
    } else {
        // Hash the password
        $password_hash = password_hash($password, PASSWORD_DEFAULT);

        // Handle profile picture upload
        $profile_pic_name = $_FILES['profile_pic']['name'];
        $profile_pic_temp = $_FILES['profile_pic']['tmp_name'];
        $profile_pic_folder = 'profile_pics/' . $profile_pic_name;

        // Check if the upload was successful
        if (move_uploaded_file($profile_pic_temp, $profile_pic_folder)) {
            // Insert user data securely with the hashed password and profile pic
            $stmt = $conn->prepare("INSERT INTO users (fName, lName, email, password_hash, profile_pic) VALUES (?, ?, ?, ?, ?)");
            $stmt->bind_param("sssss", $fName, $lName, $email, $password_hash, $profile_pic_name);

            if ($stmt->execute()) {
                echo "<script>
                        alert('Registration successful! You will now be redirected to the login page.');
                        window.location.href = 'login.php'; // Redirect to login page
                      </script>";
            } else {
                echo "<script>alert('An error occurred during registration.');</script>";
            }
        } else {
            echo "<script>alert('Failed to upload profile picture.');</script>";
        }
    }

    $stmt->close(); // Close the statement
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
    <title>Register - SB Admin</title>
    <link href="css/styles.css" rel="stylesheet" />
    <script src="https://unpkg.com/just-validate@latest/dist/just-validate.production.min.js" defer></script>
</head>

<body class="bg-dark" style="--bs-bg-opacity: .95;">
    <div id="layoutAuthentication">
        <div id="layoutAuthentication_content">
            <main>
                <div class="container">
                    <div class="row justify-content-center">
                        <div class="col-lg-7">
                            <div class="card shadow-lg border-0 rounded-lg mt-5 bg-dark">
                                <div class="card-header">
                                    <h3 class="text-center font-weight-light my-4 text-light">Create Account</h3>
                                </div>

                                <div class="card-body">
                                    <form action="" method="post" enctype="multipart/form-data" autocomplete="off">
                                        <div class="row mb-3">
                                            <div class="col-md-6">
                                                <div class="form-floating mb-3 mb-md-0">
                                                    <input class="form-control" id="fName" name="fName" type="text"  required placeholder="Enter your first name" />
                                                    <label for="fName">First name</label>
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="form-floating">
                                                    <input class="form-control" id="lName" name="lName" type="text" required placeholder="Enter your last name" />
                                                    <label for="lName">Last name</label>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="form-floating mb-3">
                                            <input class="form-control" id="email" name="email" type="email" required placeholder="name@example.com" />
                                            <label for="email">Email address</label>
                                        </div>

                                        <div class="row mb-3">
                                            <div class="col-md-6">
                                                <div class="form-floating mb-3 mb-md-0">
                                                    <input class="form-control" id="password" name="password" required type="password" placeholder="Create a password" />
                                                    <label for="password">Password</label>
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="form-floating mb-3 mb-md-0">
                                                    <input class="form-control" id="confirmpassword" name="confirmpassword" type="password" placeholder="Confirm password" />
                                                    <label for="confirmpassword">Confirm Password</label>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Profile Picture Upload -->
                                        <div class="form-floating mb-3">
                                            <label for="profile_pic" style="font-size: 1.2rem; position: absolute; top: -10px;">
                                                Upload Profile Picture
                                            </label>
                                            <input class="form-control" id="profile_pic" name="profile_pic" type="file" required accept="image/*" 
                                                   style="height: 100px; font-size: 1.0rem; padding: 50px;">
                                        </div>

                                        <div class="mt-4 mb-0">
                                            <button type="submit" name="submit" class="btn btn-success btn-block ">Create Account</button>
                                        </div>
                                    </form>
                                </div>

                                <div class="card-footer text-center py-3">
                                    <div class="small"><a href="login.php" class="text-muted">Have an account? Go to login</a></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </main>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js" crossorigin="anonymous"></script>
    <script src="js/scripts.js"></script>
</body>
</html>
