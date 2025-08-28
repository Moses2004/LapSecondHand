<?php
session_start();
require 'db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user_id = $_SESSION['user_id'];
    $phone_id = intval($_POST['phone_id']);
    $shipping_address = $_POST['shipping_address'];
    $shipping_city = $_POST['shipping_city'];
    $shipping_zip = $_POST['shipping_zip'];

    // Check stock
    $stmt = $conn->prepare("SELECT stock, price FROM phones WHERE phone_id = ?");
    $stmt->bind_param("i", $phone_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $phone = $result->fetch_assoc();
    $stmt->close();

    if (!$phone || $phone['stock'] <= 0) {
        echo "<script>alert('Sorry, this phone is out of stock!'); window.history.back();</script>";
        exit();
    }

    // Reduce stock
    $stmt = $conn->prepare("UPDATE phones SET stock = stock - 1 WHERE phone_id = ?");
    $stmt->bind_param("i", $phone_id);
    $stmt->execute();
    $stmt->close();

    // Insert order
    $quantity = 1;
    $total_amount = $phone['price'] * $quantity;

    $stmt = $conn->prepare("
        INSERT INTO orders 
        (user_id, phone_id, quantity, delivery_method, order_status, total_amount, shipping_address, shipping_city, shipping_zip) 
        VALUES (?, ?, ?, 'delivery', 'pending', ?, ?, ?, ?)
    ");
    $stmt->bind_param("iiidsss", $user_id, $phone_id, $quantity, $total_amount, $shipping_address, $shipping_city, $shipping_zip);
    $stmt->execute();

    $order_id = $conn->insert_id;
    $stmt->close();
    $conn->close();

    echo "<script>
        alert('Order placed successfully!');
        window.location.href='view_order.php?order_id={$order_id}';
    </script>";
    exit();
}
?>