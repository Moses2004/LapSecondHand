<?php
// config.php
$servername = "localhost";
$username = "root"; // Your MySQL username
$password = "";     // Your MySQL password (empty for XAMPP default)
$dbname = "phoneshop_sll"; // Your database name from phpMyAdmin

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Set header for JSON response
header('Content-Type: application/json');
// Allow cross-origin requests (for local development)
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

// Handle preflight requests for CORS
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}
?>