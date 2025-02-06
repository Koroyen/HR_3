<?php
require 'db.php';

// Retrieve all form values using $_POST
$fName = isset($_POST['fName']) ? $_POST['fName'] : '';
$lName = isset($_POST['lName']) ? $_POST['lName'] : '';
$Age = isset($_POST['Age']) ? $_POST['Age'] : '';
$sex = isset($_POST['sex']) ? $_POST['sex'] : '';
$job_position = isset($_POST['job_position']) ? $_POST['job_position'] : '';
$experience = isset($_POST['experience']) ? $_POST['experience'] : '';
$street = isset($_POST['street']) ? $_POST['street'] : '';
$barangay = isset($_POST['barangay']) ? $_POST['barangay'] : '';
$city = isset($_POST['city']) ? $_POST['city'] : '';
$email = isset($_POST['email']) ? $_POST['email'] : '';

// Get the education input
$education = $_POST['education'];
$otherEducation = isset($_POST['otherEducation']) ? $_POST['otherEducation'] : null;

// If 'Other' is selected, use the input from 'otherEducation', otherwise use the selected option
if ($education === 'Other' && !empty($otherEducation)) {
    $education = $otherEducation;
}

// Now you can use the variables to insert into the database

$query = "INSERT INTO hiring (fName, lName, Age, sex, education, job_position, experience, street, barangay, city, email)
          VALUES ('$fName', '$lName', '$Age', '$sex', '$education', '$job_position', '$experience', '$street', '$barangay', '$city', '$email')";

// Execute your query using your database connection (not shown here)...
?>
