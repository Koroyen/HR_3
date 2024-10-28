<?php

$servername = "localhost";
$username = "root";
$password = "";
$dbname   = "db_login";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname, );

// Check connection
if (mysqli_connect_error()) {
    die("Database connection failed: " . mysqli_connect_error());
  }
else{
    
}


return $conn;