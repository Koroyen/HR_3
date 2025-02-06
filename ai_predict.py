<?php
if (!isset($_SESSION["id"]) || $_SESSION["role"] != 2) {

	
 header("Location: login.php");

	
 exit();

	
}

	

	
// $employee_id = $_SESSION['id']; // Get the logged-in user's ID

	


Establish the database connection (ensure this is done before any other operations)

	
$conn = mysqli_connect("localhost", "root", "", "db_login");




 Check for connection errors

if (!$conn) {

die("Connection failed: " . mysqli_connect_error());

}

	
//Check if a hiring ID is provided for prediction


(isset($_GET['id'])) {


$hiring_id = intval($_GET['id']);


//Run the Python script for predicting suitability using the hiring_id

$command = escapeshellcmd("C:/xampp/htdocs/mfinance/venv/Scripts/python.exe C:/xampp/htdocs/mfinance/predict_model.py $hiring_id");
$output = shell_exec($command);


// Display the result from Python script


if ($output) {


 // Extract the suitability score from the Python output


 $prediction_score = (float)trim($output);


// Format the output to 3 decimal places as a fallback

	
$formatted_prediction = number_format($prediction_score, 3);


 Update the suitability score in the hiring table for the specific applicant

$update_query = "UPDATE hiring SET suitability_score = $formatted_prediction WHERE id = $hiring_id";

 $result = mysqli_query($conn, $update_query);

 if ($result) {


 // Output message confirming score update


$prediction_result = "Predicted suitability score for Applicant ID " . $hiring_id . ": " . $formatted_prediction;

	
} else {

	
$prediction_result = "Error updating suitability score: " . mysqli_error($conn);


}

} else {

 $prediction_result = "Error running prediction script.";

	
 }

	
} else {

	
$prediction_result = "";


}


// Fetch job applications from the hiring table

	
$query = "SELECT id, fName, lName, job_position, experience, suitability_score FROM hiring";

	
$result = $conn->query($query);

	
// Do NOT close the connection until after all queries are done
/ Close the database connection

mysqli_close($conn);
