<?php
include 'db.php';

// Get order ID from URL
$order_id = $_GET['id'] ?? null;

if (empty($order_id)) {
    die("Error: Order ID not provided");
}

// Delete order
$sql = "DELETE FROM orders WHERE order_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $order_id);

if ($stmt->execute()) {
    // Success - redirect back to orders page
    header("Location: index.php");
} else {
    echo "Error: " . $conn->error;
}

$conn->close();
?>