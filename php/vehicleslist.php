<?php
// vehicleslist.php (your data fetcher)
header('Content-Type: application/json');
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Path to db.php relative to vehicleslist.php
require_once 'db.php';

$sql = "SELECT vehicle_id, driver_name, license_plate, weight_kg FROM vehicles";
$result = $conn->query($sql);

$vehicles = [];
if ($result) {
    if ($result->num_rows > 0) {
        while($row = $result->fetch_assoc()) {
            $vehicles[] = $row;
        }
    }
} else {
    // If SQL query fails, return a specific error
    echo json_encode(["error" => "SQL Query failed: " . $conn->error]);
    $conn->close();
    exit;
}

echo json_encode($vehicles);

$conn->close();
?>