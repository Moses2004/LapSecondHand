<?php
// invoices.php (your data fetcher)
header('Content-Type: application/json');
ini_set('display_errors', 1); // For debugging
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Path to db.php relative to invoices.php (they are in the same folder)
require_once 'db.php';

$sql = "SELECT invoice_id, order_id, vehicle_id,  notes FROM invoices";
$result = $conn->query($sql);

$invoices = [];
if ($result) {
    if ($result->num_rows > 0) {
        while($row = $result->fetch_assoc()) {
            $invoices[] = $row;
        }
    }
} else {
    // If SQL query fails, return a specific error
    echo json_encode(["error" => "SQL Query failed: " . $conn->error]);
    $conn->close();
    exit;
}

echo json_encode($invoices);

$conn->close();
?>