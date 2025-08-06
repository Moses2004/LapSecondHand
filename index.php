<?php
include 'db.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>LAP Second Hand Phone Service - Orders</title>
    <style>
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
        }
        
        .btn:hover {
            background-color: #3d5afe;
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
            background-color: var(--light);
            color: var(--text-dark);
        }
        
        th, td {
            padding: 12px 15px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }
        
        th {
            background-color: var(--primary);
            color: white;
        }
        
        tr:hover {
            background-color: #f5f5f5;
        }
        
        .status {
            padding: 4px 8px;
            border-radius: 4px;
            font-weight: bold;
            font-size: 14px;
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
        
        .action-btn {
            padding: 5px 10px;
            text-decoration: none;
            border-radius: 4px;
            font-size: 14px;
            margin-right: 5px;
        }
        
        .edit-btn {
            background-color: var(--warning);
            color: white;
        }
        
        .delete-btn {
            background-color: var(--danger);
            color: white;
        }
        
        .customer-info {
            display: flex;
            flex-direction: column;
        }
        
        .customer-email {
            font-size: 14px;
            color: #666;
        }
        
        footer {
            margin-top: 30px;
            text-align: center;
            padding: 15px;
            color: var(--text-light);
            font-size: 14px;
        }
    </style>
</head>
<body>
    <header>
        <h1>LAP Second Hand Phone Service</h1>
        <a href="new_order.php" class="btn">+ Create New Order</a>
    </header>

    <h2>Current Orders</h2>
    
    <table>
        <thead>
            <tr>
                <th>Customer</th>
                <th>Phone Model</th>
                <th>Qty</th>
                <th>Total</th>
                <th>Status</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php
            $sql = "SELECT o.order_id, c.name, c.email, p.brand, p.model, p.price, o.quantity, o.status 
                    FROM orders o
                    JOIN customers c ON o.customer_id = c.customer_id
                    JOIN phones p ON o.phone_id = p.phone_id
                    ORDER BY o.created_at DESC";
            
            $result = $conn->query($sql);
            
            if ($result->num_rows > 0) {
                while($row = $result->fetch_assoc()) {
                    $total = $row['price'] * $row['quantity'];
                    echo "<tr>
                            <td>
                                <div class='customer-info'>
                                    <strong>{$row['name']}</strong>
                                    <span class='customer-email'>{$row['email']}</span>
                                </div>
                            </td>
                            <td>{$row['brand']} {$row['model']}<br><small>\${$row['price']} each</small></td>
                            <td>{$row['quantity']}</td>
                            <td>\${$total}</td>
                            <td><span class='status status-{$row['status']}'>{$row['status']}</span></td>
                            <td>
                                <a href='edit_order.php?id={$row['order_id']}' class='action-btn edit-btn'>Edit</a>
                                <a href='delete_order.php?id={$row['order_id']}' class='action-btn delete-btn'>Delete</a>
                            </td>
                          </tr>";
                }
            } else {
                echo "<tr><td colspan='6'>No orders found</td></tr>";
            }
            ?>
        </tbody>
    </table>

    <footer>
        <p>Contact us: info@lapphones.com | © 2025 LAP Second Hand Phone Service</p>
    </footer>
</body>
</html>