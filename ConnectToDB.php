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

// Execute a SELECT statement
$sql = "SELECT * FROM employee";
$result = $conn->query($sql);

// Fetch the results
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        echo $row['ID'] . " " . $row['first_name'] . " " . $row['last_name'] . "<br>";
    }
} else {
    echo "0 results";
}
?>
