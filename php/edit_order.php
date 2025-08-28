<?php
session_start();
require 'db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

if (!isset($_GET['order_id'])) {
    header("Location: ordered_list.php");
    exit();
}

$order_id = intval($_GET['order_id']);

// Fetch order info
$stmt = $conn->prepare("SELECT * FROM orders WHERE order_id = ? AND user_id = ?");
$stmt->bind_param("ii", $order_id, $user_id);
$stmt->execute();
$result = $stmt->get_result();
$order = $result->fetch_assoc();

if (!$order) {
    echo "<script>alert('Order not found!'); window.location.href='ordered_list.php';</script>";
    exit();
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $shipping_address = $_POST['shipping_address'];
    $shipping_city = $_POST['shipping_city'];
    $shipping_zip = $_POST['shipping_zip'];
   

    $stmt = $conn->prepare("UPDATE orders SET shipping_address=?, shipping_city=?, shipping_zip=?, quantity=? WHERE order_id=? AND user_id=?");
    $stmt->bind_param("sssiii", $shipping_address, $shipping_city, $shipping_zip, $quantity, $order_id, $user_id);
    $stmt->execute();

    echo "<script>alert('Done!'); window.location.href='view_order.php';</script>";
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Edit Order #<?php echo $order_id; ?></title>
<style>
body { font-family: Arial; padding: 20px; }
input, button { padding: 8px; margin: 5px 0; width: 100%; }
button { background: #1e90ff; color: white; border: none; cursor: pointer; }
button:hover { background: #ff4e50; }
form { max-width: 400px; margin: auto; background: #f4f4f4; padding: 20px; border-radius: 10px; }
</style>
</head>
<body>

<h2>Edit Order #<?php echo $order_id; ?></h2>
<form method="post">
    <label>Shipping Address:</label>
    <input type="text" name="shipping_address" value="<?php echo htmlspecialchars($order['shipping_address']); ?>" required>

    <label>City:</label>
    <input type="text" name="shipping_city" value="<?php echo htmlspecialchars($order['shipping_city']); ?>" required>

    <label>ZIP:</label>
    <input type="text" name="shipping_zip" value="<?php echo htmlspecialchars($order['shipping_zip']); ?>" required>


    <button type="submit">Update Order</button>
</form>

</body>
</html>
