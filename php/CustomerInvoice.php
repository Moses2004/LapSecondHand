<?php
session_start();
require_once 'db.php';

header('Content-Type: application/json');

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['error' => 'Unauthorized']);
    exit();
}

$user_id = $_SESSION['user_id'];

// Get invoices for this user's orders
$sql = "
    SELECT i.invoice_id, i.order_id, i.vehicle_id, i.total_amount, i.notes
    FROM invoices i
    INNER JOIN orders o ON i.order_id = o.order_id
    WHERE o.user_id = ?
    ORDER BY i.invoice_id DESC
";

$stmt = $conn->prepare($sql);
$stmt->bind_param('i', $user_id);
$stmt->execute();
$result = $stmt->get_result();

$invoices = [];
while ($row = $result->fetch_assoc()) {
    $invoices[] = $row;
}

echo json_encode($invoices);
?>
