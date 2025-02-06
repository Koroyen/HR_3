<?php

$servername = "localhost";
$username = "root";
$password = "";
$database   = "db_login";

// Create connection
$conn = new mysqli($servername, $username, $password, $database);
// Check connection
if (mysqli_connect_error()) {
    die("Database connection failed: " . mysqli_connect_error());
  }
else{
    
}

return $conn;