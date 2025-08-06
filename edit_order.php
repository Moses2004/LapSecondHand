<?php
include 'db.php';

// Get order ID from URL
$order_id = $_GET['id'] ?? null;

if (empty($order_id)) {
    die("Error: Order ID not provided");
}

// Fetch order details
$sql = "SELECT o.*, c.name AS customer_name, c.email, p.brand, p.model, p.price 
        FROM orders o
        JOIN customers c ON o.customer_id = c.customer_id
        JOIN phones p ON o.phone_id = p.phone_id
        WHERE o.order_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $order_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    die("Error: Order not found");
}

$order = $result->fetch_assoc();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Order - LAP Phone Shop</title>
    <style>
        /* Same styles as new_order.php */
        :root {
            --primary: #1a237e;
            --secondary: #303f9f;
            --accent: #536dfe;
            --light: #e8eaf6;
            --dark: #0d1238;
            --text-light: #fff;
            --text-dark: #212121;
            --success: #4caf50;
            --warning: #ff9800;
            --danger: #f44336;
        }
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: Arial, sans-serif;
        }
        
        body {
            background-color: var(--dark);
            color: var(--text-light);
            padding: 20px;
        }
        
        header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
            padding-bottom: 15px;
            border-bottom: 1px solid var(--accent);
        }
        
        h1 {
            color: var(--accent);
        }
        
        .btn {
            padding: 8px 16px;
            background-color: var(--accent);
            color: white;
            text-decoration: none;
            border-radius: 4px;
            font-weight: bold;
            border: none;
            cursor: pointer;
        }
        
        .btn:hover {
            background-color: #3d5afe;
        }
        
        .btn-secondary {
            background-color: var(--danger);
        }
        
        .btn-secondary:hover {
            background-color: #d32f2f;
        }
        
        .form-container {
            max-width: 600px;
            margin: 0 auto;
            background-color: var(--light);
            padding: 20px;
            border-radius: 8px;
            color: var(--text-dark);
        }
        
        .form-group {
            margin-bottom: 20px;
        }
        
        label {
            display: block;
            margin-bottom: 8px;
            font-weight: bold;
        }
        
        select, input {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 16px;
        }
        
        .form-actions {
            display: flex;
            justify-content: flex-end;
            gap: 10px;
            margin-top: 20px;
        }
        
        footer {
            margin-top: 30px;
            text-align: center;
            padding: 15px;
            color: var(--text-light);
            font-size: 14px;
        }
        
        .status-option {
            padding: 4px 8px;
            border-radius: 4px;
            font-weight: bold;
        }
        
        .status-pending {
            background-color: var(--warning);
            color: white;
        }
        
        .status-processing {
            background-color: var(--accent);
            color: white;
        }
        
        .status-delivered {
            background-color: var(--success);
            color: white;
        }
    </style>
</head>
<body>
    <header>
        <h1>LAP Second Hand Phone Service</h1>
        <a href="index.php" class="btn btn-secondary">Back to Orders</a>
    </header>

    <div class="form-container">
        <h2>Edit Order</h2>
        <form action="update_order.php" method="POST">
            <input type="hidden" name="order_id" value="<?= $order['order_id'] ?>">
            
            <div class="form-group">
                <label>Customer</label>
                <div><?= $order['customer_name'] ?> (<?= $order['email'] ?>)</div>
            </div>
            
            <div class="form-group">
                <label>Phone</label>
                <div><?= $order['brand'] ?> <?= $order['model'] ?> - $<?= $order['price'] ?></div>
            </div>
            
            <div class="form-group">
                <label for="quantity">Quantity</label>
                <input type="number" id="quantity" name="quantity" min="1" value="<?= $order['quantity'] ?>" required>
            </div>
            
            <div class="form-group">
                <label for="status">Status</label>
                <select id="status" name="status" required>
                    <option value="PENDING" <?= $order['status'] == 'PENDING' ? 'selected' : '' ?>>Pending</option>
                    <option value="PROCESSING" <?= $order['status'] == 'PROCESSING' ? 'selected' : '' ?>>Processing</option>
                    <option value="DELIVERED" <?= $order['status'] == 'DELIVERED' ? 'selected' : '' ?>>Delivered</option>
                </select>
            </div>
            
            <div class="form-actions">
                <a href="index.php" class="btn btn-secondary">Cancel</a>
                <button type="submit" class="btn">Update Order</button>
            </div>
        </form>
    </div>

    <footer>
        <p>Contact us: info@lapphones.com | © 2025 LAP Second Hand Phone Service</p>
    </footer>
</body>
</html>