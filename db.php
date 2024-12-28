<?php


// 
$servername = "hr3_mfinance";
$username = "hr3_mfinance";
$password = "";
$dbname   = "hr3_mfinance";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname, );
// Check connection
if (mysqli_connect_error()) {
    die("Database connection failed: " . mysqli_connect_error());
  }
else{
    
}

return $conn;