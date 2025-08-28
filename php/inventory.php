<?php
require_once 'db.php';

// Get filter parameters
$brand = $_GET['brand'] ?? null;
$color = $_GET['color'] ?? null;

$conditions = [];


$baseQuery = "SELECT p.phone_id, p.brand, p.model, p.color, p.stock
              FROM phones p
              LEFT JOIN orders o ON p.phone_id = o.phone_id AND o.order_status IN ('pending', 'processing', 'shipped', 'ready_for_pickup', 'delivered')
              WHERE o.order_id IS NULL AND p.stock > 0";

// Add filters
if ($brand) {
    $conditions[] = "p.brand LIKE '%" . $conn->real_escape_string($brand) . "%'";
}
if ($color) {
    $conditions[] = "p.color LIKE '%" . $conn->real_escape_string($color) . "%'";
}

$whereClause = !empty($conditions) ? " AND " . implode(' AND ', $conditions) : "";

// Total Available Phones
$sqlTotalAvailable = "SELECT SUM(p.stock) AS total_available FROM phones p LEFT JOIN orders o ON p.phone_id = o.phone_id AND o.order_status IN ('pending', 'processing', 'shipped', 'ready_for_pickup', 'delivered') WHERE o.order_id IS NULL AND p.stock > 0 $whereClause";
$resultTotalAvailable = $conn->query($sqlTotalAvailable);
$totalAvailable = $resultTotalAvailable->fetch_assoc()['total_available'] ?? 0;


// Phones by Brand (Available)
$sqlPhonesByBrand = "SELECT p.brand, SUM(p.stock) AS count
                     FROM phones p
                     LEFT JOIN orders o ON p.phone_id = o.phone_id AND o.order_status IN ('pending', 'processing', 'shipped', 'ready_for_pickup', 'delivered')
                     WHERE o.order_id IS NULL AND p.stock > 0 $whereClause
                     GROUP BY p.brand
                     ORDER BY count DESC";
$resultPhonesByBrand = $conn->query($sqlPhonesByBrand);
$phonesByBrand = [];
while ($row = $resultPhonesByBrand->fetch_assoc()) {
    $phonesByBrand[] = $row;
}

// Phones by Color (Available)
$sqlPhonesByColor = "SELECT p.color, SUM(p.stock) AS count
                     FROM phones p
                     LEFT JOIN orders o ON p.phone_id = o.phone_id AND o.order_status IN ('pending', 'processing', 'shipped', 'ready_for_pickup', 'delivered')
                     WHERE o.order_id IS NULL AND p.stock > 0 $whereClause
                     GROUP BY p.color
                     ORDER BY count DESC";
$resultPhonesByColor = $conn->query($sqlPhonesByColor);
$phonesByColor = [];
while ($row = $resultPhonesByColor->fetch_assoc()) {
    $phonesByColor[] = $row;
}

$conn->close();

echo json_encode([
    'totalAvailable' => (int)$totalAvailable,
    'phonesByBrand' => $phonesByBrand,
    'phonesByColor' => $phonesByColor,
]);
?>