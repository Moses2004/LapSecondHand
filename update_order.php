<?php
include 'db.php';

// Get form data
$order_id = $_POST['order_id'];
$quantity = $_POST['quantity'];
$status = $_POST['status'];

// Validate inputs
if (empty($order_id) || empty($quantity) || empty($status)) {
    die("Error: All fields are required");
}

// Update order
$sql = "UPDATE orders SET quantity = ?, status = ? WHERE order_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("isi", $quantity, $status, $order_id);

if ($stmt->execute()) {
    // Success - redirect back to orders page
    header("Location: index.php");
} else {
    echo "Error: " . $conn->error;
}

$conn->close();
?>