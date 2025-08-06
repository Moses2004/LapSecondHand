<?php
include 'db.php';

// Initialize variables
$error = '';
$success = '';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $customer_id = $_POST['customer_id'] ?? '';
    $phone_id = $_POST['phone_id'] ?? '';
    $quantity = $_POST['quantity'] ?? 1;

    // Validate inputs
    if (empty($customer_id) || empty($phone_id) || empty($quantity)) {
        $error = "All fields are required";
    } else {
        // Check stock availability
        $stock_check = $conn->prepare("SELECT stock, price FROM phones WHERE phone_id = ?");
        $stock_check->bind_param("i", $phone_id);
        $stock_check->execute();
        $phone = $stock_check->get_result()->fetch_assoc();

        if ($phone['stock'] < $quantity) {
            $error = "Only {$phone['stock']} units available for this phone";
        } else {
            // Create order
            $total_price = $phone['price'] * $quantity;
            $stmt = $conn->prepare("INSERT INTO orders (customer_id, phone_id, quantity, total_price) VALUES (?, ?, ?, ?)");
            $stmt->bind_param("iiid", $customer_id, $phone_id, $quantity, $total_price);

            if ($stmt->execute()) {
                // Update stock
                $conn->query("UPDATE phones SET stock = stock - $quantity WHERE phone_id = $phone_id");
                $success = "Order created successfully!";
                header("Location: index.php"); // Redirect after success
                exit();
            } else {
                $error = "Error creating order: " . $conn->error;
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create New Order - LAP Phone Shop</title>
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

        .alert {
            padding: 10px;
            margin-bottom: 20px;
            border-radius: 4px;
        }
        
        .alert-error {
            background-color: #ffebee;
            color: var(--danger);
        }
        
        .alert-success {
            background-color: #e8f5e9;
            color: var(--success);
        }

        .total-display {
            font-weight: bold;
            margin-top: 10px;
            color: var(--primary);
        }
    </style>
</head>
<body>
    <header>
        <h1>LAP Second Hand Phone Service</h1>
        <a href="index.php" class="btn btn-secondary">Back to Orders</a>
    </header>

    <div class="form-container">
        <h2>Create New Order</h2>
        
        <?php if ($error): ?>
            <div class="alert alert-error"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>
        
        <?php if ($success): ?>
            <div class="alert alert-success"><?= htmlspecialchars($success) ?></div>
        <?php endif; ?>

        <form action="new_order.php" method="POST">
            <div class="form-group">
                <label for="customer_id">Customer</label>
                <select id="customer_id" name="customer_id" required>
                    <option value="">Select Customer</option>
                    <?php
                    $sql = "SELECT * FROM customers ORDER BY name ASC";
                    $result = $conn->query($sql);
                    
                    if ($result->num_rows > 0) {
                        while($row = $result->fetch_assoc()) {
                            $selected = ($_POST['customer_id'] ?? '') == $row['customer_id'] ? 'selected' : '';
                            echo "<option value='{$row['customer_id']}' $selected>
                                    {$row['name']} ({$row['email']})
                                  </option>";
                        }
                    }
                    ?>
                </select>
            </div>
            
            <div class="form-group">
                <label for="phone_id">Phone</label>
                <select id="phone_id" name="phone_id" required>
                    <option value="">Select Phone</option>
                    <?php
                    $sql = "SELECT * FROM phones WHERE stock > 0 ORDER BY brand, model";
                    $result = $conn->query($sql);
                    
                    if ($result->num_rows > 0) {
                        while($row = $result->fetch_assoc()) {
                            $selected = ($_POST['phone_id'] ?? '') == $row['phone_id'] ? 'selected' : '';
                            echo "<option value='{$row['phone_id']}' 
                                    data-price='{$row['price']}'
                                    data-stock='{$row['stock']}'
                                    $selected>
                                    {$row['brand']} {$row['model']} - \${$row['price']} (Stock: {$row['stock']})
                                  </option>";
                        }
                    }
                    ?>
                </select>
            </div>
            
            <div class="form-group">
                <label for="quantity">Quantity</label>
                <input type="number" id="quantity" name="quantity" min="1" value="<?= htmlspecialchars($_POST['quantity'] ?? 1) ?>" required>
                <div id="stock-message" style="font-size: 0.8em; color: #666;"></div>
                <div class="total-display">Total: $<span id="total-price">0.00</span></div>
            </div>
            
            <div class="form-actions">
                <a href="index.php" class="btn btn-secondary">Cancel</a>
                <button type="submit" class="btn">Create Order</button>
            </div>
        </form>
    </div>

    <footer>
        <p>Contact us: info@lapphones.com | © 2025 LAP Second Hand Phone Service</p>
    </footer>

    <script>
        // Calculate total price and check stock
        function updateOrderDetails() {
            const phoneSelect = document.getElementById('phone_id');
            const quantityInput = document.getElementById('quantity');
            const totalDisplay = document.getElementById('total-price');
            const stockMessage = document.getElementById('stock-message');
            const selectedOption = phoneSelect.options[phoneSelect.selectedIndex];
            
            if (selectedOption && selectedOption.value !== "") {
                const price = parseFloat(selectedOption.getAttribute('data-price'));
                const stock = parseInt(selectedOption.getAttribute('data-stock'));
                const quantity = parseInt(quantityInput.value) || 0;
                
                // Update total
                const total = (price * quantity).toFixed(2);
                totalDisplay.textContent = total;
                
                // Update stock message
                if (quantity > stock) {
                    stockMessage.textContent = `Warning: Only ${stock} available`;
                    stockMessage.style.color = 'red';
                } else {
                    stockMessage.textContent = `${stock} available`;
                    stockMessage.style.color = 'green';
                }
            } else {
                totalDisplay.textContent = '0.00';
                stockMessage.textContent = '';
            }
        }

        // Event listeners
        document.getElementById('phone_id').addEventListener('change', updateOrderDetails);
        document.getElementById('quantity').addEventListener('input', updateOrderDetails);
        
        // Initialize on page load
        document.addEventListener('DOMContentLoaded', updateOrderDetails);
    </script>
</body>
</html>