<?php 
session_start();
require 'db.php'; 
$_SESSION = [];
session_unset();
session_destroy();
header("Location: login.php");