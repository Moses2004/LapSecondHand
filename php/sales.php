<?php
require_once 'db.php';

// Get filter parameters
$startDate = $_GET['start_date'] ?? null;
$endDate = $_GET['end_date'] ?? null;
$brand = $_GET['brand'] ?? null;

$conditions = ["o.order_status IN ('delivered', 'ready_for_pickup')"]; // Count sales when delivered or ready for pickup

if ($startDate && $endDate) {
    $conditions[] = "o.ordered_at BETWEEN '$startDate 00:00:00' AND '$endDate 23:59:59'";
}
if ($brand) {
    $conditions[] = "p.brand LIKE '%" . $conn->real_escape_string($brand) . "%'";
}

$whereClause = implode(' AND ', $conditions);

// Total Phones Sold
$sqlTotalSold = "SELECT SUM(o.quantity) AS total_sold FROM orders o JOIN phones p ON o.phone_id = p.phone_id WHERE $whereClause";
$resultTotalSold = $conn->query($sqlTotalSold);
$totalSold = $resultTotalSold->fetch_assoc()['total_sold'] ?? 0;

// Sales by Brand
$sqlSalesByBrand = "SELECT p.brand, SUM(o.quantity) AS sales_count
                    FROM orders o
                    JOIN phones p ON o.phone_id = p.phone_id
                    WHERE $whereClause
                    GROUP BY p.brand
                    ORDER BY sales_count DESC";
$resultSalesByBrand = $conn->query($sqlSalesByBrand);
$salesByBrand = [];
while ($row = $resultSalesByBrand->fetch_assoc()) {
    $salesByBrand[] = $row;
}

// Sales by Month
$sqlSalesByMonth = "SELECT DATE_FORMAT(o.ordered_at, '%Y-%m') as month, SUM(o.quantity) as sales_count
                    FROM orders o
                    JOIN phones p ON o.phone_id = p.phone_id
                    WHERE $whereClause
                    GROUP BY month
                    ORDER BY month ASC";
$resultSalesByMonth = $conn->query($sqlSalesByMonth);
$salesByMonth = [];
while ($row = $resultSalesByMonth->fetch_assoc()) {
    $salesByMonth[] = $row;
}

$conn->close();

echo json_encode([
    'totalSold' => (int)$totalSold,
    'salesByBrand' => $salesByBrand,
    'salesByMonth' => $salesByMonth,
]);
?>