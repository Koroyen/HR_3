<?php
require 'db.php';
require 'csrf_protection.php';

// Check if a session is already started before starting it
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Generate CSRF token for the form
generate_csrf_token();

if (isset($_POST["submit"])) {
    // Validate CSRF token
    if (!validate_csrf_token($_POST['csrf_token'])) {
        die('Invalid CSRF token');
    }

    // Sanitize and trim input
    $email = trim($_POST["email"]);
    $password = trim($_POST["password"]);

    // Use prepared statements to prevent SQL injection
    $stmt = $conn->prepare("SELECT * FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();

    if ($row) {
        // Verify the password hash
        if (password_verify($password, $row["password_hash"])) {
            // Set session variables
            $_SESSION["login"] = true;
            $_SESSION["id"] = $row["id"];
            $_SESSION["role"] = $row["role"];
            $_SESSION["user_name"] = $row["fName"];

            // Redirect based on role
            switch ($row["role"]) {
                case 1:
                    echo "<script>alert('Welcome! HR Manager'); window.location.href = 'predict_suitability.php';</script>";
                    break;
                case 0:
                    echo "<script>alert('Welcome!'); window.location.href = 'home.php';</script>";
                    break;
                case 3:
                    echo "<script>alert('Welcome, Trainer!'); window.location.href = 'instructor.php';</script>";
                    break;
                case 2:
                    echo "<script>alert('Welcome, Employee!'); window.location.href = 'employee.php';</script>";
                    break;
                default:
                    echo "<script>alert('Invalid role!');</script>";
                    break;
            }
            exit();
        } else {
            echo "<script>alert('Wrong email or password!');</script>";
        }
    } else {
        echo "<script>alert('User not registered!');</script>";
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
    <title>Login</title>
    <link href="css/styles.css" rel="stylesheet" />
    <script src="https://use.fontawesome.com/releases/v6.3.0/js/all.js" crossorigin="anonymous"></script>
</head>
<body class="bg-dark" style="--bs-bg-opacity: .95;">
    <div id="layoutAuthentication">
        <div id="layoutAuthentication_content">
            <main>
                <div class="container">
                    <div class="row justify-content-center">
                        <div class="col-lg-5">
                            <div class="card shadow-lg border-0 rounded-lg mt-5 bg-dark">
                                <div class="card-header">
                                    <h3 class="text-center font-weight-muted my-4 text-light">Login</h3>
                                </div>
                                <div class="card-body">
                                    <form action="" method="post" autocomplete="off">
                                        <!-- CSRF token input -->
                                        <?php csrf_token_field(); ?>
                                        <div class="form-floating mb-3">
                                            <input class="form-control text-dark" id="email" name="email" type="text" required placeholder="Email" />
                                            <label for="email">Email</label>
                                        </div>
                                        <div class="form-floating mb-3">
                                            <input class="form-control text-dark" id="password" name="password" type="password" required placeholder="Password" />
                                            <label for="password">Password</label>
                                        </div>
                                        <div class="d-flex align-items-center justify-content-between mt-4 mb-0">
                                            <a class="small text-muted" href="password.php">Forgot Password?</a>
                                            <button class="btn btn-success" type="submit" name="submit">Login</button>
                                        </div>
                                    </form>
                                </div>
                                <div class="card-footer text-center py-3">
                                    <div class="small"><a href="register.php" class="text-muted">Need an account? Sign up!</a></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </main>
        </div>
        <div id="layoutAuthentication_footer">
            <footer class="py-4 bg-dark mt-auto">
                <div class="container-fluid px-4">
                    <div class="d-flex align-items-center justify-content-between small">
                        <div class="text-muted">Copyright &copy; Your Website 2023</div>
                        <div>
                            <a href="#" class="text-muted">Privacy Policy</a>
                            &middot;
                            <a href="#" class="text-muted">Terms &amp; Conditions</a>
                        </div>
                    </div>
                </div>
            </footer>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js" crossorigin="anonymous"></script>
    <script src="js/scripts.js"></script>
</body>
</html>
