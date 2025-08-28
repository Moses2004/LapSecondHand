<?php
session_start();
require 'db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['order_id'])) {
    $order_id = intval($_POST['order_id']);
    $user_id = $_SESSION['user_id'];

    // Check if order belongs to user and is pending
    $stmt = $conn->prepare("SELECT order_status FROM orders WHERE order_id = ? AND user_id = ?");
    $stmt->bind_param("ii", $order_id, $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $order = $result->fetch_assoc();

    if ($order && $order['order_status'] === 'pending') {
        $stmt = $conn->prepare("DELETE FROM orders WHERE order_id = ? AND user_id = ?");
        $stmt->bind_param("ii", $order_id, $user_id);
        $stmt->execute();

        echo "<script>alert('Order deleted successfully!'); window.location.href='view_order.php';</script>";
        exit();
    } else {
        echo "<script>alert('Cannot delete this order!'); window.location.href='ordered_list.php';</script>";
        exit();
    }
}
?>
