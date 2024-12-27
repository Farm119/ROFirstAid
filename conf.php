<?php
// For Host ro.ac.th
$servername = "localhost";
$username = "roacth_rofirstaid";
$password = "v7jUZ5D8C6b3Bd27qksy";
$dbname = "roacth_rofirstaid";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// For localhost
// $servername = "localhost";
// $username = "root";
// $password = "";
// $dbname = "rofirstaid";

// // Create connection
// $conn = new mysqli($servername, $username, $password, $dbname);

// // Check connection
// if ($conn->connect_error) {
//     die("Connection failed: " . $conn->connect_error);
// }
?>

