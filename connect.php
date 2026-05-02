<?php
$servername = "localhost";
$username = "yahyah";
$password = "regidor123";
$dbname = "satisfaction_db";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
  die("Connection failed: " . $conn->connect_error);
}
echo "";
?>