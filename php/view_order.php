<?php
session_start();
require 'db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Fetch the logged-in user's name
$stmt_user = $conn->prepare("SELECT first_name, last_name FROM users WHERE user_id = ?");
$stmt_user->bind_param("i", $user_id);
$stmt_user->execute();
$result_user = $stmt_user->get_result();
$user = $result_user->fetch_assoc();
$full_name = htmlspecialchars($user['first_name'] . ' ' . $user['last_name']);

// Fetch all orders of this user, oldest first (smallest order_id first)
$stmt = $conn->prepare("
    SELECT o.order_id, o.quantity, o.total_amount, o.order_status, o.ordered_at,
           o.shipping_address, o.shipping_city, o.shipping_zip,
           p.brand, p.model, p.color, p.price
    FROM orders o
    JOIN phones p ON o.phone_id = p.phone_id
    WHERE o.user_id = ?
    ORDER BY o.order_id ASC
");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>My Orders</title>
<style>
body { font-family: Arial; padding: 20px; margin: 0; background: #f0f0f0; }
header {
    display: flex;
    justify-content: flex-end;
    align-items: center;
    padding: 10px 20px;
    background: linear-gradient(90deg, #06047cff, #9c0f11ff);
    color: white;
    box-shadow: 0 3px 8px rgba(0,0,0,0.3);
}
header span {
    margin-right:auto; 
    font-weight:bold; 
    font-size:16px;
}
header a, header form button {
    color: white;
    text-decoration: none;
    margin-left: 15px;
    padding: 8px 16px;
    border: none;
    border-radius: 5px;
    cursor: pointer;
    font-weight: bold;
    transition: 0.3s;
}
header a { background: rgba(255,255,255,0.2); }
header a:hover { background: rgba(255,255,255,0.5); }
header form button { background: rgba(255,255,255,0.2); }
header form button:hover { background: rgba(255,255,255,0.5); }

table { 
    border-collapse: collapse; 
    width: 100%; 
    margin-top: 20px; 
    background: white;
    border-radius: 10px;
    overflow: hidden;
    box-shadow: 0 3px 10px rgba(0,0,0,0.2);
}
th, td { border: 1px solid #ccc; padding: 10px; text-align: left; }
th { background: linear-gradient(90deg, #1e90ff, #ff4e50); color: white; }
tr:nth-child(even) { background: #f4f4f4; }

button {
    padding: 5px 10px;
    border: none;
    border-radius: 5px;
    cursor: pointer;
    color: white;
    font-weight: bold;
}
.edit-btn { background-color: #1e90ff; }
.edit-btn:hover { background-color: #0073e6; }
.delete-btn { background-color: #ff4e50; }
.delete-btn:hover { background-color: #cc0000; }
.receive-btn { background-color: #28a745; }
.receive-btn:hover { background-color: #218838; }
</style>
</head>
<body>

<header>
    <span>Hello, <?php echo $full_name; ?></span>
    <a href="../html/index.html" style="color: white; text-decoration: none; margin-left: 15px; padding: 8px 16px; border: none; border-radius: 5px; cursor: pointer; font-weight: bold; transition: 0.3s; background: #cc0000;" onmouseover="this.style.background='#a30000';" onmouseout="this.style.background='#cc0000';">Back to Shop</a>
</header>

<h2>My Orders</h2>
<table>
    <thead>
        <tr>
            <th>#</th>
            <th>Order ID</th>
            <th>Phone</th>
            <th>Quantity</th>
            <th>Total ($)</th>
            <th>Status</th>
            <th>Ordered At</th>
            <th>Shipping Address</th>
            <th>City</th>
            <th>ZIP</th>
            <th>Actions</th>
        </tr>
    </thead>
    <tbody>
        <?php 
        $counter = 1; // for numbering rows
        while($order = $result->fetch_assoc()): ?>
        <tr>
            <td><?php echo $counter++; ?></td>
            <td><?php echo $order['order_id']; ?></td>
            <td><?php echo htmlspecialchars($order['brand'] . ' ' . $order['model']); ?></td>
            <td><?php echo $order['quantity']; ?></td>
            <td><?php echo $order['total_amount']; ?></td>
            <td><?php echo $order['order_status']; ?></td>
            <td><?php echo $order['ordered_at']; ?></td>
            <td><?php echo htmlspecialchars($order['shipping_address']); ?></td>
            <td><?php echo htmlspecialchars($order['shipping_city']); ?></td>
            <td><?php echo htmlspecialchars($order['shipping_zip']); ?></td>
            <td>
                <?php if ($order['order_status'] === 'pending'): ?>
                <form action="receive.php" method="post" style="display:inline;" onsubmit="return confirm('Are you sure you have received this order?');">
                    <input type="hidden" name="order_id" value="<?php echo $order['order_id']; ?>">
                    <button type="submit" class="receive-btn">Receive</button>
                </form>
                <?php endif; ?>
                <form action="edit_order.php" method="get" style="display:inline;">
                    <input type="hidden" name="order_id" value="<?php echo $order['order_id']; ?>">
                    <button type="submit" class="edit-btn">Edit</button>
                </form>
                <?php if ($order['order_status'] === 'pending'): ?>
                
                <?php endif; ?>
            </td>
        </tr>
        <?php endwhile; ?>
    </tbody>
</table>

</body>
</html>