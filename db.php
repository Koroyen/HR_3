<?php

$servername = "localhost";
$username = "hr3_mfinance";
$password = "hr3_mfinance";
$dbname   = "hr3_mfinance";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname, );

// Check connection
if (mysqli_connect_error()) {
    die("Database connection failed: " . mysqli_connect_error());
  }
else{
    
}

// burat
return $conn;