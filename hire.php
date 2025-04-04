<?php
session_start();
require 'db.php';

// Generate and store the CSRF token in the session
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

if (isset($_POST['submit'])) {
    // Validate the CSRF token
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        echo "<p>Invalid CSRF token. Please try again.</p>";
        exit();
    }

    // Check if the email already exists in the 'hiring' table and is not eligible for reapply (i.e., is_reapplying = 0)
    $userEmail = mysqli_real_escape_string($conn, $_POST['email']);
    $email_check_query = "SELECT * FROM hiring WHERE email = ? AND is_reapplying = 0";
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
        // If the applicant is not allowed to reapply (is_reapplying = 0)
        echo "<script>
                alert('You have already submitted the form and cannot reapply at this time.');
                window.location.href = 'home.php';
              </script>";
        $stmt->close();
        exit();
    }

    // Process the form and store the new submission
    $fName = mysqli_real_escape_string($conn, $_POST['fName']);
    $lName = mysqli_real_escape_string($conn, $_POST['lName']);
    $age = mysqli_real_escape_string($conn, $_POST['Age']);
    $sex = mysqli_real_escape_string($conn, $_POST['sex']);
    $skills = mysqli_real_escape_string($conn, $_POST['skills']);
    $job_position = mysqli_real_escape_string($conn, $_POST['job_position']);
    $street = mysqli_real_escape_string($conn, $_POST['street']);
    $barangay = mysqli_real_escape_string($conn, $_POST['barangay']);
    $applicationType = mysqli_real_escape_string($conn, $_POST['application_type']);

    // Experience fields (years and months)
    $experience_years = mysqli_real_escape_string($conn, $_POST['experience_years']);
    $experience_months = mysqli_real_escape_string($conn, $_POST['experience_months']);
    $former_company = mysqli_real_escape_string($conn, $_POST['former_company']);
    $department = mysqli_real_escape_string($conn, $_POST['department']);

    // Education fields
    $education = mysqli_real_escape_string($conn, $_POST['education']);
    $otherEducation = '';
    if ($education === 'Other') {
        $otherEducation = mysqli_real_escape_string($conn, $_POST['otherEducation']);
    }

    // Fetch city_id based on the selected city
    $city_id = (int)$_POST['city'];

    // Insert query including experience_years, experience_months, education, and otherEducation
    $insert_query = "INSERT INTO hiring (fName, lName, age, sex, skills, job_position, email, street, barangay, city_id, application_type, experience_years, experience_months, education, otherEducation, former_company, is_reapplying, is_notified, department) 
VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

    // Prepare the statement
    $stmt_insert = $conn->prepare($insert_query);

    // Check for errors
    if ($stmt_insert === false) {
        echo "<p>Error preparing the insert query: " . htmlspecialchars($conn->error) . "</p>";
        exit();
    }

    $is_reapplying = 0; // Set the appropriate value for is_reapplying (e.g., 1 for allowing reapplying)
    $is_notified = 0;   // Set the initial value of is_notified to 0 (not yet notified)

    $stmt_insert->bind_param(
        'ssissssssisiissssss', 
        $fName,        // varchar(255) -> s
        $lName,        // varchar(255) -> s
        $age,          // int(100)     -> i
        $sex,          // varchar(255) -> s
        $skills,       // varchar(255) -> s
        $job_position, // varchar(255) -> s
        $userEmail,    // varchar(100) -> s
        $street,       // varchar(255) -> s
        $barangay,     // varchar(255) -> s
        $city_id,      // int(11)      -> i
        $applicationType, // varchar(255) -> s
        $experience_years,  // int(11)   -> i
        $experience_months, // int(11)   -> i
        $education,       // varchar(255) -> s
        $otherEducation,  // varchar(255) -> s
        $former_company,  // varchar(255) -> s
        $is_reapplying,   // int(11)     -> i
        $is_notified,     // int(11)     -> i
        $department       // varchar(100) -> s
    );

    // Execute the query
    if ($stmt_insert->execute()) {
        echo "<script>
              alert('Form submitted successfully!');
              window.location.href = 'home.php';
              </script>";
    } else {
        echo "<p>Error inserting data: " . htmlspecialchars($stmt_insert->error) . "</p>";
    }

    $stmt_insert->close();
    $stmt->close();

  // AI Prediction logic
  $applicantId = $conn->insert_id;
  $command = escapeshellcmd('python3 ai_predict.py ' . escapeshellarg($applicantId));
  $output = shell_exec($command);

  if ($output === null) {
    $suitabilityScore = 0.0;
  } else {
    $suitabilityScore = (float)$output;
  }

  // Update the hiring table with the suitability score
  $update_query = "UPDATE hiring SET suitability_score = ? WHERE id = ?";
  $stmt = $conn->prepare($update_query);
  $stmt->bind_param("di", $suitabilityScore, $applicantId);

  if (!$stmt->execute()) {
    echo "<p>Error updating suitability score: " . htmlspecialchars($stmt->error) . "</p>";
  }

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
  <!-- Tagify CSS -->
  <link href="https://unpkg.com/@yaireo/tagify/dist/tagify.css" rel="stylesheet" type="text/css" />

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
                    <!-- CSRF Token -->
                    <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">

                    <!-- First Name -->
                    <div class="form-floating mb-3">
                      <input type="text" class="form-control" id="fName" name="fName" required placeholder="Enter your first name" />
                      <label for="fName">First Name</label>
                    </div>

                    <!-- Last Name -->
                    <div class="form-floating mb-3">
                      <input type="text" class="form-control" id="lName" name="lName" required placeholder="Enter your last name" />
                      <label for="lName">Last Name</label>
                    </div>

                    <!-- Age -->
                    <div class="form-floating mb-3">
                      <input type="number" class="form-control" id="Age" name="Age" required placeholder="Enter your age" />
                      <label for="Age">Age</label>
                    </div>

                    <!-- Sex -->
                    <div class="form-floating mb-3">
                      <select class="form-select" id="sex" name="sex" required>
                        <option value="">Select Sex</option>
                        <option value="Male">Male</option>
                        <option value="Female">Female</option>
                      </select>
                      <label for="sex">Sex</label>
                    </div>

                    <!-- Updated skills input structure -->
                    <div class="col-md-12 mb-3">
                      <div class="form-group">
                        <input type="text" class="form-control" id="skills" name="skills" placeholder="Enter skills">
                      </div>
                    </div>


                    <div class="form-floating mb-3">
                      <select class="form-control" id="job_position" name="job_position" required>
                        <option value="" disabled selected>Select Job Position</option>
                        <option value="Human Resource assistant">Human Resource assistant</option>
                        <option value="Human Resource specialist">Human Resource specialist</option>
                        <option value="Human Resource coordinator">Human Resource coordinator</option>
                      </select>
                      <label for="job_position">Job position</label>
                    </div>

                    <div class="form-floating mb-3">
                      <select class="form-control" id="department" name="department" required>
                        <option value="" disabled selected>Select Department</option>
                        <option value="HR">HR</option>
                      </select>
                      <label for="department">Department</label>
                    </div>

                    <!-- Experience -->
                    <div class="form-floating mb-3">
                      <input type="number" class="form-control" name="experience_years" id="experience_years" min="0" placeholder="Enter years of experience">
                      <label for="experience_years" class="form-label">Years of Experience</label>
                    </div>

                    <!-- New Months of Experience Dropdown -->
                    <div class="form-floating mb-3">
                      <select class="form-select" name="experience_months" id="experience_months">
                        <option value="">Select months of experience</option>
                        <option value="6">6 months</option>
                        <option value="7">7 months</option>
                        <option value="8">8 months</option>
                        <option value="9">9 months</option>
                        <option value="10">10 months</option>
                        <option value="11">11 months</option>
                      </select>
                      <label for="experience_months" class="form-label"></label>
                    </div>


                    <div class="form-floating mb-3">
                      <input type="text" class="form-control" id="former_company" name="former_company" required placeholder="Enter your Former Company" />
                      <label for="former_company">Former Company</label>
                    </div>


                    <!-- Street -->
                    <div class="form-floating mb-3">
                      <input type="text" class="form-control" id="street" name="street" required placeholder="Enter your street" />
                      <label for="street">Street</label>
                    </div>

                    <!-- Barangay -->
                    <div class="form-floating mb-3">
                      <input type="text" class="form-control" id="barangay" name="barangay" required placeholder="Enter your barangay" />
                      <label for="barangay">Barangay</label>
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

                    <!-- Email -->
                    <div class="form-floating mb-3">
                      <input type="email" class="form-control" id="email" name="email" required placeholder="Enter your email" />
                      <label for="email">Email</label>
                    </div>

                    <!-- Education Dropdown -->
                    <div class="form-floating mb-3">
                      <select class="form-control" id="education" name="education" onchange="toggleOtherEducationField()" required>
                        <option value="">Select your education</option>
                        <option value="University of the Philippines Diliman">University of the Philippines Diliman</option>
                        <option value="Ateneo de Manila University"> Ateneo de Manila University</option>
                        <option value="De La Salle University"> De La Salle University</option>
                        <option value="University of Santo Tomas"> University of Santo Tomas</option>
                        <option value="Polytechnic University of the Philippines"> Polytechnic University of the Philippines</option>
                        <option value="Other">Other</option>
                      </select>
                      <label for="education">Education</label>
                    </div>

                    <!-- Other Education Input (hidden by default) -->
                    <div class="form-floating mb-3" id="otherEducationField" style="display: none;">
                      <input type="text" class="form-control" id="otherEducation" name="otherEducation" placeholder="Enter your education">
                      <label for="otherEducation">Other Education</label>
                    </div>





                    <input type="hidden" name="application_type" value="hiring">
                    <div class="mt-4 mb-0 text-center">
                      <button type="submit" name="submit" class="btn btn-success btn-block">Submit</button>
                    </div>
                  </form>


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
            <div class="text-muted">Copyright &copy; Your Website 2025</div>
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

  <script>
    function toggleOtherEducationField() {
      var educationSelect = document.getElementById('education');
      var otherEducationField = document.getElementById('otherEducationField');

      if (educationSelect.value === 'Other') {
        otherEducationField.style.display = 'block';
      } else {
        otherEducationField.style.display = 'none';
      }
    }
  </script>


  <!-- Include Tagify library for the skills input -->
  <script src="https://unpkg.com/@yaireo/tagify"></script>
  <script>
    document.addEventListener("DOMContentLoaded", function() {
      var input = document.querySelector('#skills');
      var tagify = new Tagify(input);

      // When the form is submitted, extract the values as a comma-separated string
      var form = document.querySelector('form'); // Select your form element
      form.addEventListener('submit', function(event) {
        var rawSkills = tagify.value.map(function(tag) {
          return tag.value; // Extract only the 'value' (skill name)
        });

        // Set the input value to a comma-separated string of skills
        input.value = rawSkills.join(',');
      });
    });
  </script>
</body>

</html>