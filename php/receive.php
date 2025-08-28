<?php
session_start();
require 'db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['order_id'])) {
    $order_id_to_update = $_POST['order_id'];

    // Update the order status to 'delivered' for the specific user and order ID
    $stmt_update = $conn->prepare("UPDATE orders SET order_status = 'delivered' WHERE order_id = ? AND user_id = ?");
    $stmt_update->bind_param("ii", $order_id_to_update, $user_id);
    $stmt_update->execute();
    $stmt_update->close();
}

// Redirect back to the orders page to show the updated status
header("Location: view_order.php");
exit();
?>