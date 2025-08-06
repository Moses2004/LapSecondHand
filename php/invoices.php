<?php
// invoices.php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

header('Content-Type: application/json');header('Content-Type: application/json'); // Set header to return JSON

// Include the existing db.php file to establish the database connection
require_once 'db.php'; // Ensure db.php is in the same directory

// The $conn variable is now available from db.php

// Get the raw POST data (JSON payload from JavaScript)
$json_data = file_get_contents('php://input');
$data = json_decode($json_data, true); // Decode JSON into an associative array

// Check if data is received and valid for the new columns
if (empty($data) || !isset($data['order_id'], $data['vehicle_id'])) {
    echo json_encode(['success' => false, 'message' => 'Invalid input data. Please fill all required fields (Order ID, Vehicle ID).']);
    if (isset($conn) && $conn instanceof mysqli) {
        $conn->close();
    }
    exit();
}

// Sanitize and validate input for the new columns
$order_id = filter_var($data['order_id'], FILTER_VALIDATE_INT);
$vehicle_id = filter_var($data['vehicle_id'], FILTER_VALIDATE_INT);

$notes = isset($data['notes']) ? htmlspecialchars(trim($data['notes'])) : null;

// Basic validation
if ($order_id === false || $order_id <= 0) {
    echo json_encode(['success' => false, 'message' => 'Order ID must be a valid positive integer.']);
    if (isset($conn) && $conn instanceof mysqli) {
        $conn->close();
    }
    exit();
}
if ($vehicle_id === false || $vehicle_id <= 0) {
    echo json_encode(['success' => false, 'message' => 'Vehicle ID must be a valid positive integer.']);
    if (isset($conn) && $conn instanceof mysqli) {
        $conn->close();
    }
    exit();
}


// Prepare and bind SQL statement for the new columns
$stmt = $conn->prepare("INSERT INTO invoices (order_id, vehicle_id, notes) VALUES (?, ?, ?)");

if ($stmt === false) {
    echo json_encode(['success' => false, 'message' => 'Failed to prepare statement: ' . $conn->error]);
    if (isset($conn) && $conn instanceof mysqli) {
        $conn->close();
    }
    exit();
}

// 'i' for integer, 's' for string
// Assuming order_id, vehicle_id, total_amount are integers and notes is a string (text)
$stmt->bind_param("iis", $order_id, $vehicle_id, $notes);

// Execute the statement
if ($stmt->execute()) {
    echo json_encode(['success' => true, 'message' => 'Invoice created successfully!']);
} else {
    // Check for specific errors, e.g., foreign key constraint violation
    if ($conn->errno == 1452) { // MySQL error code for foreign key constraint fail
        echo json_encode(['success' => false, 'message' => 'Error: Foreign key constraint failed. Ensure Order ID and Vehicle ID exist in their respective tables.']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Error creating invoice: ' . $stmt->error]);
    }
}

// Close statement and connection
$stmt->close();
if (isset($conn) && $conn instanceof mysqli) {
    $conn->close();
}

?>
