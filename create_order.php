<?php
include 'db.php';

// Get and validate form data
$customer_id = $_POST['customer_id'] ?? null;
$phone_id = $_POST['phone_id'] ?? null;
$quantity = $_POST['quantity'] ?? null;

if (empty($customer_id) || empty($phone_id) || empty($quantity) || $quantity < 1) {
    die("Error: All fields are required and quantity must be at least 1");
}

// Start transaction
$conn->begin_transaction();

try {
    // Get phone price and stock
    $stmt = $conn->prepare("SELECT price, stock FROM phones WHERE phone_id = ? FOR UPDATE");
    $stmt->bind_param("i", $phone_id);
    $stmt->execute();
    $phone = $stmt->get_result()->fetch_assoc();

    if (!$phone) {
        throw new Exception("Phone not found");
    }

    // Check stock
    if ($phone['stock'] < $quantity) {
        throw new Exception("Only {$phone['stock']} units available");
    }

    // Calculate total
    $total_price = $phone['price'] * $quantity;

    // Insert order - MATCHING YOUR ACTUAL COLUMNS
    $stmt = $conn->prepare("INSERT INTO orders 
                          (customer_id, phone_id, quantity, total_price, status) 
                          VALUES (?, ?, ?, ?, 'PENDING')");
    $stmt->bind_param("iiid", $customer_id, $phone_id, $quantity, $total_price);
    
    if (!$stmt->execute()) {
        throw new Exception("Order creation failed: " . $conn->error);
    }

    // Update stock
    $stmt = $conn->prepare("UPDATE phones SET stock = stock - ? WHERE phone_id = ?");
    $stmt->bind_param("ii", $quantity, $phone_id);
    $stmt->execute();

    $conn->commit();
    header("Location: index.php");
    exit();

} catch (Exception $e) {
    $conn->rollback();
    die("Error: " . $e->getMessage());
}
?>