<?php 




$token = $_GET["token"];

$token_hash = hash("sha256", $token);

$conn = require __DIR__ . "/db.php";

$sql = "SELECT * FROM users
    WHERE reset_token_hash =?";
  
$stmt = $conn->prepare($sql);

$stmt->bind_param("s", $token_hash);

$stmt->execute();

$result = $stmt->get_result();
$user = $result->fetch_assoc();

if($user === null) {
    echo  "<script>
    alert('Token not found');
    window.location.href = 'reset-password.php'; // Redirect to login page
    </script>";
}
if(strtotime($user["reset_token_expires_at"]) <= time()){
    echo  "<script>
    alert('Token was expired');
    window.location.href = 'password.php'; // Redirect to login page
    </script>";
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
        <title>Password Reset</title>
        <link href="css/styles.css" rel="stylesheet" />
        <script src="https://use.fontawesome.com/releases/v6.3.0/js/all.js" crossorigin="anonymous"></script>
    </head>
    <body class="bg-dark" style="--bs-bg-opacity: .95;">
        <div id="layoutAuthentication">
            <div id="layoutAuthentication_content">
                <main>
                
                    <div class="container">
                        <div class="row justify-content-center">
                            <div class="col-lg-6">
                                <div class="card shadow-lg border-0 rounded-lg mt-5 bg-dark">
                                    <div class="card-header"><h3 class="text-center text-light font-weight-light my-4">Password Recovery</h3></div>
                                    <div class="card-body">
                                        <div class="small mb-3 text-muted">Enter your New password.</div>
                                        
                                        <form  method="post" action="process-reset-password.php">

                                            <div class="form-floating mb-3 info">
                                                <input type="hidden" name="token" value="<?= htmlspecialchars($token) ?>">

                                                <input class="form-control text-dark" id="password" type="password" name="password" placeholder="name@example.com" />
                                                <label for="password">New password</label>
                                            </div>
                                            <div class="form-floating mb-3 info">
                                                <input class="form-control text-dark" id="confirmpassword" type="password" name="confirmpassword" placeholder="name@example.com" />
                                                <label for="password_confirmation">Repeat Password</label>
                                            </div>

                                            <div class="d-flex align-items-center justify-content-between mt-4 mb-0">
                                                <a class="small text-muted" href="login.php"></a>
                                                <!-- <a class="btn btn-success" >Send</a> -->
                                                <button class="btn btn-success">Send</button>
                                                <!-- <a class="btn btn-success" type="submit" name="submit" id="submit">Submit</a> -->
                                            </div>
                                        </form>
                                    </div>
                                    
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
               
            </main>
            </div>
            <div id="layoutAuthentication_footer">
                <footer class="py-4 bg-light mt-auto bg-dark">
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