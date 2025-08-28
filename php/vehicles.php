<?php
// vehicles.php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

header('Content-Type: application/json');

// Include the existing db.php file to establish the database connection
require_once 'db.php';

// Get the raw POST data (JSON payload from JavaScript)
$json_data = file_get_contents('php://input');
$data = json_decode($json_data, true);

// Check if data is received and valid
if (empty($data) || !isset($data['driver_name'], $data['license_plate'], $data['weight_kg'])) {
    echo json_encode(['success' => false, 'message' => 'Invalid input data. Please fill all required fields.']);
    if (isset($conn) && $conn instanceof mysqli) {
        $conn->close();
    }
    exit();
}

// Sanitize and validate input
$driver_name = htmlspecialchars(trim($data['driver_name']));
$license_plate = htmlspecialchars(trim($data['license_plate']));
$weight_kg = filter_var($data['weight_kg'], FILTER_VALIDATE_INT);

// Basic validation
if ($weight_kg === false || $weight_kg <= 0) {
    echo json_encode(['success' => false, 'message' => 'Weight must be a valid positive integer.']);
    if (isset($conn) && $conn instanceof mysqli) {
        $conn->close();
    }
    exit();
}

// Prepare and bind SQL statement. We are NOT providing the vehicle_id here,
// as the database will handle it with AUTO_INCREMENT.
$stmt = $conn->prepare("INSERT INTO vehicles (driver_name, license_plate, weight_kg) VALUES (?, ?, ?)");

if ($stmt === false) {
    echo json_encode(['success' => false, 'message' => 'Failed to prepare statement: ' . $conn->error]);
    if (isset($conn) && $conn instanceof mysqli) {
        $conn->close();
    }
    exit();
}

// 's' for string, 'i' for integer
$stmt->bind_param("ssi", $driver_name, $license_plate, $weight_kg);

// Execute the statement
if ($stmt->execute()) {
    echo json_encode(['success' => true, 'message' => 'Vehicle created successfully!', 'new_id' => $conn->insert_id]);
} else {
    // If the database returns an error (like Duplicate key), we can handle it here.
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $stmt->error]);
}

// Close statement and connection
$stmt->close();
if (isset($conn) && $conn instanceof mysqli) {
    $conn->close();
}
?>
