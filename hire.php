<?php
session_start();
require 'db.php';

// Check if the user is logged in
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
    // Check if the email already exists in the 'hiring' table (i.e., user has already submitted)
    $email_check_query = "SELECT * FROM hiring WHERE email = ?";
    $stmt = $conn->prepare($email_check_query);

    if ($stmt === false) {
        echo "<p>Error preparing the query: " . htmlspecialchars($conn->error) . "</p>";
        exit();
    }

    // Bind the email value to the query
    $stmt->bind_param('s', $userEmail);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        // If a row is found, the user has already applied
        echo "<script>
                alert('You have already submitted the form.');
                window.location.href = 'home.php'; // Redirect back to home page
              </script>";
    } else {
        // Process the form and store the new submission
        $fName = mysqli_real_escape_string($conn, $_POST['fName']);
        $lName = mysqli_real_escape_string($conn, $_POST['lName']);
        $age = mysqli_real_escape_string($conn, $_POST['Age']);
        $sex = mysqli_real_escape_string($conn, $_POST['sex']);
        $job_position = mysqli_real_escape_string($conn, $_POST['job_position']);
        $street = mysqli_real_escape_string($conn, $_POST['street']);
        $barangay = mysqli_real_escape_string($conn, $_POST['barangay']);
        // $city = mysqli_real_escape_string($conn, $_POST['city']);
        $applicationType = mysqli_real_escape_string($conn, $_POST['application_type']);

        // Handle ID image upload
        if (isset($_FILES['valid_ids']) && $_FILES['valid_ids']['error'] == 0) {
            $id_name = $_FILES['valid_ids']['name'];
            $id_temp = $_FILES['valid_ids']['tmp_name'];
            $id_folder = 'hiring/' . $id_name;
            move_uploaded_file($id_temp, $id_folder); // Move uploaded file
        } else {
            echo "<p>Error uploading ID image.</p>";
            exit();
        }

        // Handle Birth Certificate upload
        if (isset($_FILES['birthcerti']) && $_FILES['birthcerti']['error'] == 0) {
            $birthc_name = $_FILES['birthcerti']['name'];
            $birthc_temp = $_FILES['birthcerti']['tmp_name'];
            $birthc_folder = 'hiring/' . $birthc_name;
            move_uploaded_file($birthc_temp, $birthc_folder); // Move uploaded file
        } else {
            echo "<p>Error uploading Birth Certificate.</p>";
            exit();
        }
        // Fetch city_id based on the selected city from the form
        $city_id = (int)$_POST['city'];

        // Insert query including user_id and application_type
        $insert_query = "INSERT INTO hiring (user_id, fName, lName, age, sex, job_position, email, street, barangay, city_id, valid_ids, birthcerti, application_type) 
                         VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt_insert = $conn->prepare($insert_query);

        // Check for errors
        if ($stmt_insert === false) {
            echo "<p>Error preparing the insert query: " . htmlspecialchars($conn->error) . "</p>";
            exit();
        }

        // Bind the form values and user_id to the insert query
        $stmt_insert->bind_param('issssssssisss', $id, $fName, $lName, $age, $sex, $job_position, $userEmail, $street, $barangay, $city_id, $id_name, $birthc_name, $applicationType);

        // Execute the insert query
        if ($stmt_insert->execute()) {
            echo "<script>
                    alert('Form submitted successfully!');
                    window.location.href = 'home.php'; // Redirect after submission
                  </script>";
        } else {
            echo "<p>Error inserting data: " . htmlspecialchars($stmt_insert->error) . "</p>";
        }

        // Close the insert statement
        $stmt_insert->close();
    }

    // Close the email check statement
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
  <title>Job Application</title>
  <link href="css/styles.css" rel="stylesheet" />
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
                  <h3 class="text-center font-weight-light my-4 text-light">Job Application</h3>
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
                      <input class="form-control" id="Age" name="Age" type="text" required placeholder="Enter your Age" />
                      <label for="Age">Age</label>
                    </div>

                    <div class="form-floating mb-3">
                      <input class="form-control" id="email" name="email" type="email" required placeholder="Enter your email" />
                      <label for="email">Email</label>
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
                      <select class="form-control" id="job_position" name="job_position" required>
                        <option value="" disabled selected>Select Job Position</option>
                        <option value="HR assistant">Human Resource assistant</option>
                        <option value="HR specialist">Human Resource specialist</option>
                        <option value="HR coordinator">Human Resource coordinator</option>
                      </select>
                      <label for="job_position">Job position</label>
                    </div>


                    <div class="form-floating mb-3">
                      <input class="form-control" id="street" name="street" type="text" required placeholder="Enter your street" />
                      <label for="street">Street</label>
                    </div>

                    <div class="form-floating mb-3">
                      <input class="form-control" id="barangay" name="barangay" type="text" required placeholder="Barangay/Subdivision" />
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
                    <!-- Update the name attributes for file uploads -->
                    <div class="form-floating mb-3">
                      <label for="valid_ids" style="font-size: 1.2rem; position: absolute; top: -10px;">
                        Upload Resume
                      </label>
                      <input class="form-control" id="valid_ids" name="valid_ids" type="file" required accept="image/*"
                        style="height: 100px; font-size: 1.0rem; padding: 50px;" onchange="previewImage('valid_ids', 'coePreview')">
                    </div>
                    <div id="coePreview"></div>

                    <div class="form-floating mb-3">
                      <label for="birthcerti" style="font-size: 1.2rem; position: absolute; top: -10px;">
                        Upload Your Birth Certificate
                      </label>
                      <input class="form-control" id="birthcerti" name="birthcerti" type="file" required accept="image/*"
                        style="height: 100px; font-size: 1.0rem; padding: 50px;" onchange="previewImage('birthcerti', 'birthcPreview')">
                    </div>
                    <div id="birthcPreview"></div>

                    <input type="hidden" name="application_type" value="hiring">
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

  <script>
    function previewImage(inputId, previewId) {
      var input = document.getElementById(inputId);
      var previewContainer = document.getElementById(previewId);

      previewContainer.innerHTML = '';

      if (input.files && input.files[0]) {
        var reader = new FileReader();

        reader.onload = function(e) {
          var img = document.createElement('img');
          img.src = e.target.result;
          img.style.maxWidth = '500px';
          img.style.height = 'auto';
          previewContainer.appendChild(img);
        };

        reader.readAsDataURL(input.files[0]);
      }
    }
  </script>
</body>

</html>