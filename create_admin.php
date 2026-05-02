<?php
include('connect.php');

$username = "admin123";
$password = password_hash("admin123", PASSWORD_DEFAULT);

$stmt = $conn->prepare("INSERT INTO user (lastname, firstname, username, password, utype) VALUES (?, ?, ?, ?, ?)");
$lastname = "Admin";
$firstname = "System";
$utype = "admin";

$stmt->bind_param("sssss", $lastname, $firstname, $username, $password, $utype);

$stmt->execute();

echo "Admin created successfully!";
?>