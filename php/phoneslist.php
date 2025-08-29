<?php


// Include database connection
require_once 'db.php';

// Set content type to JSON
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET');
header('Access-Control-Allow-Headers: Content-Type');

// Response function
function sendResponse($success, $message, $data = null) {
    echo json_encode([
        'success' => $success,
        'message' => $message,
        'data' => $data
    ]);
    exit;
}

// Validate request method
if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    sendResponse(false, 'Invalid request method');
}

// Check database connection
if ($conn->connect_error) {
    error_log("Database connection failed: " . $conn->connect_error);
    sendResponse(false, 'Database connection failed. Please try again later.');
}

// Fetch all phones from the database
$sql = "SELECT brand, model, color, price, stock, description, image_url, created_at FROM phones ORDER BY created_at DESC";
$result = $conn->query($sql);

if ($result) {
    $phones = [];
    while ($row = $result->fetch_assoc()) {
        $phones[] = $row;
    }
    sendResponse(true, 'Phones fetched successfully', $phones);
} else {
    error_log("Query failed: " . $conn->error);
    sendResponse(false, 'Failed to fetch phones from database');
}

// Close database connection
$conn->close();

?>