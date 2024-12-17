<?php
$servername = "localhost"; // Change this if your MySQL server is hosted elsewhere
$username = "admin";
$password = "admin";
$database = "secure_file_storage";

// Create connection
$mysqli = new mysqli($servername, $username, $password, $database);

// Check connection
if ($mysqli->connect_error) {
    die("Connection failed: " . $mysqli->connect_error);
}
?>
