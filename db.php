<?php
// Database settings
$host = "localhost";       // Database host (usually localhost)
$user = "root";            // MySQL username (default in XAMPP)
$pass = "";                // MySQL password (leave empty for XAMPP default)
$dbname = "lap_phone_shop"; // Your database name

// Create connection
$conn = new mysqli($host, $user, $pass, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>
