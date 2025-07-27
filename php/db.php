<?php
$host = 'localhost';
$db = 'phoneshop_sll';  // make sure this matches your uploaded DB name
$user = 'root';         // default for XAMPP
$pass = '';             // default for XAMPP

$conn = new mysqli($host, $user, $pass, $db);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>