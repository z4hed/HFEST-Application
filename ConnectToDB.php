<?php
// Database configuration
$host = "jac353.encs.concordia.ca";
$username = "jac353_4";
$password = "TAVF401X";
$database = "jac353_4";

// Create connection
$conn = new mysqli($host, $username, $password, $database);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

echo "Connected successfully";

?>
