<?php
require_once 'db.php';

// Get filter parameters
$startDate = $_GET['start_date'] ?? null;
$endDate = $_GET['end_date'] ?? null;
$status = $_GET['status'] ?? null;

$conditions = [];

if ($startDate && $endDate) {
    $conditions[] = "o.ordered_at BETWEEN '$startDate 00:00:00' AND '$endDate 23:59:59'";
}
if ($status && $status !== '') {
    $conditions[] = "o.order_status = '" . $conn->real_escape_string($status) . "'";
}

$whereClause = !empty($conditions) ? "WHERE " . implode(' AND ', $conditions) : "";

// Orders by Status
$sqlOrderByStatus = "SELECT order_status, COUNT(order_id) AS status_count
                     FROM orders
                     $whereClause
                     GROUP BY order_status";
$resultOrderByStatus = $conn->query($sqlOrderByStatus);
$orderByStatus = [];
while ($row = $resultOrderByStatus->fetch_assoc()) {
    $orderByStatus[] = $row;
}

// Recent Orders
$sqlRecentOrders = "SELECT o.order_id, u.first_name AS buyer_name, p.brand, p.model,
                           o.quantity, o.delivery_method, o.shipping_address, o.order_status, o.ordered_at AS ordered_at_datetime
                    FROM orders o
                    JOIN users u ON o.user_id = u.user_id
                    JOIN phones p ON o.phone_id = p.phone_id
                    $whereClause
                    ORDER BY o.ordered_at DESC
                    LIMIT 20";
$resultRecentOrders = $conn->query($sqlRecentOrders);
$recentOrders = [];
while ($row = $resultRecentOrders->fetch_assoc()) {
    $recentOrders[] = $row;
}

$conn->close();

echo json_encode([
    'orderByStatus' => $orderByStatus,
    'recentOrders' => $recentOrders,
]);
?>