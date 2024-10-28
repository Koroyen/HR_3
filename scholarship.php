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
  $email_check_query = "SELECT * FROM images_coe_birthc WHERE email = ?";
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
                alert('You have already applied for a scholarship with this account.');
                window.location.href = 'home.php'; // Redirect back to home page
              </script>";
    exit(); // Stop the rest of the script from executing
  } else {
    // Process the form and store the new submission
    $fName = mysqli_real_escape_string($conn, $_POST['fName']);
    $lName = mysqli_real_escape_string($conn, $_POST['lName']);
    $age = mysqli_real_escape_string($conn, $_POST['Age']);
    $sex = mysqli_real_escape_string($conn, $_POST['sex']);
    $street = mysqli_real_escape_string($conn, $_POST['street']);
    $barangay = mysqli_real_escape_string($conn, $_POST['barangay']);
    $applicationType = mysqli_real_escape_string($conn, $_POST['application_type']);

    // Fetch city_id based on the selected city from the form
    if (isset($_POST['city']) && !empty($_POST['city'])) {
      $city_id = (int)$_POST['city']; // Make sure the city is selected and valid
    } else {
      echo "<script>
                    alert('Please select a city.');
                    window.location.href = 'scholarship.php'; // Redirect back to form
                  </script>";
      exit();
    }

    // Handle Certificate of Enrollment (COE) upload
    $coe_name = $_FILES['coe']['name'];
    $coe_temp = $_FILES['coe']['tmp_name'];
    $coe_folder = 'images-coe-birthc/' . $coe_name;

    // Handle Birth Certificate upload
    $birthc_name = $_FILES['birthc']['name'];
    $birthc_temp = $_FILES['birthc']['tmp_name'];
    $birthc_folder = 'images-coe-birthc/' . $birthc_name;

    // Prepare the insert query with user_id and city_id
    $insert_query = "INSERT INTO images_coe_birthc (user_id, fName, lName, Age, sex, street, barangay, city_id, email, coe, birthc, application_type) 
                         VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
    $stmt_insert = $conn->prepare($insert_query);

    if ($stmt_insert === false) {
      echo "<p>Error preparing the insert query: " . htmlspecialchars($conn->error) . "</p>";
      exit();
    }

    // Bind the form values to the insert query, including user_id and city_id
    $stmt_insert->bind_param('ississsissss', $id, $fName, $lName, $age, $sex, $street, $barangay, $city_id, $userEmail, $coe_name, $birthc_name, $applicationType);

    // Execute the insert query
    if ($stmt_insert->execute()) {
      // Move uploaded files to the folder
      if (move_uploaded_file($coe_temp, $coe_folder) && move_uploaded_file($birthc_temp, $birthc_folder)) {
        echo "<script>
                        alert('Application submitted successfully.');
                        window.location.href = 'home.php'; // Redirect to home page
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
  <title>Scholarship</title>
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
                      <input class="form-control" id="Age" name="Age" type="Age" required placeholder="Enter your Age" />
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
                      <input class="form-control" id="street" name="street" type="street" required placeholder="street" />
                      <label for="street">Street</label>
                    </div>

                    <div class="form-floating mb-3">
                      <input class="form-control" id="barangay/subdivision" name="barangay" type="barangay" required placeholder="barangay" />
                      <label for="barangay">barangay/subdivision</label>
                    </div>

                    <!-- Assuming this is part of your form -->
                    <div class="form-floating mb-3">
                      <select class="form-select" id="city" name="city" required>
                        <option value="">Select a City</option>
                        <?php
                        // Fetch city data from the `cities` table for the dropdown
                        $city_query = "SELECT city_id, city_name FROM cities";
                        $city_result = mysqli_query($conn, $city_query);

                        if (mysqli_num_rows($city_result) > 0) {
                          while ($row = mysqli_fetch_assoc($city_result)) {
                            echo "<option value='" . $row['city_id'] . "'>" . $row['city_name'] . "</option>";
                          }
                        }
                        ?>
                      </select>
                      <label for="city">City</label>
                    </div>


                    <div class="form-floating mb-3">
                      <label for="coe" style="font-size: 1.2rem; position: absolute; top: -10px;">
                        Photo of Certificate of Enrollment
                      </label>
                      <input required value="" class="form-control" id="coe" name="coe" type="file" required accept="image/*"
                        style="height: 100px; font-size: 1.0rem; padding: 50px;" onchange="previewImage('coe', 'coePreview')">
                    </div>
                    <div id="coePreview"></div>


                    <div class="form-floating mb-3">
                      <label for="birthc" style="font-size: 1.2rem; position: absolute; top: -10px;">
                        Photo of Birth Certificate
                      </label>
                      <input required value="" class="form-control" id="birthc" name="birthc" type="file" required accept="image/*"
                        style="height: 100px; font-size: 1.0rem; padding: 50px;" onchange="previewImage('birthc', 'birthcPreview')">
                    </div>
                    <div id="birthcPreview"></div>


                    <input type="hidden" name="application_type" value="scholarship"> <!-- Or "hiring" based on the page -->
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