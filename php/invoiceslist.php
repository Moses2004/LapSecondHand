<?php


 header('Access-Control-Allow-Origin: *');
header('Content-Type: application/json');
ini_set('display_errors', 1); // For debugging
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Path to db.php relative to invoiceslist.php (they are in the same folder)
require_once 'db.php';

// Join invoices table with orders table to get total_amount
$sql = "SELECT 
            i.invoice_id, 
            i.order_id, 
            i.vehicle_id, 
            i.notes,
            o.total_amount
        FROM invoices i
        LEFT JOIN orders o ON i.order_id = o.order_id
        ORDER BY i.invoice_id DESC";

$result = $conn->query($sql);

$invoices = [];
if ($result) {
    if ($result->num_rows > 0) {
        while($row = $result->fetch_assoc()) {
            // Format total_amount to 2 decimal places if it exists
            if ($row['total_amount'] !== null) {
                $row['total_amount'] = number_format((float)$row['total_amount'], 2);
            }
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