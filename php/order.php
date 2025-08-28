<?php
// order.php
session_start();
require 'db.php';

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Check if phone_id is passed in the URL
if (!isset($_GET['phone_id'])) {
    echo "No phone selected!";
    exit();
}

$phone_id = intval($_GET['phone_id']);

// Fetch phone info from the database
$stmt = $conn->prepare("SELECT * FROM phones WHERE phone_id = ?");
$stmt->bind_param("i", $phone_id);
$stmt->execute();
$result = $stmt->get_result();
$phone = $result->fetch_assoc();
$stmt->close();

if (!$phone) {
    echo "Phone not found!";
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Order Phone</title>
<style>
body {
    font-family: Arial;
    background: #f4f4f4;
    display: flex;
    justify-content: center;
    padding: 50px 0;
}
.phone-card {
    background: white;
    border-radius: 10px;
    padding: 30px;
    display: flex;
    max-width: 900px;
    width: 90%;
    gap: 40px;
    box-shadow: 0 0 20px rgba(0,0,0,0.2);
}
.phone-card img {
    width: 300px;
    border-radius: 10px;
}
.phone-info {
    flex: 1;
}
.phone-info h2 {
    margin-top: 0;
}
.phone-info p {
    margin: 8px 0;
}
.confirm-btn, .back-btn {
    margin-top: 15px;
    padding: 10px 20px;
    border: none;
    border-radius: 8px;
    cursor: pointer;
    font-weight: bold;
    color: white;
}
.confirm-btn {
    background: #1e90ff;
}
.confirm-btn:hover {
    background: #ff4e50;
}
.back-btn {
    background: #555;
}
.back-btn:hover {
    background: #333;
}
</style>
</head>
<body>

<div class="phone-card">
    <img src="../uploadimages/<?php echo htmlspecialchars($phone['image_url']); ?>" alt="Phone">
    <div class="phone-info">
        <h2><?php echo htmlspecialchars($phone['brand'] . ' ' . $phone['model']); ?></h2>
        <p><strong>Color:</strong> <?php echo htmlspecialchars($phone['color']); ?></p>
        <p><strong>Price:</strong> $<?php echo htmlspecialchars($phone['price']); ?></p>
        <p><strong>Stock:</strong> <?php echo htmlspecialchars($phone['stock']); ?></p>
        <p><strong>Description:</strong> <?php echo htmlspecialchars($phone['description']); ?></p>

        <?php if ($phone['stock'] > 0): ?>
        <form method="POST" action="process_order.php">
            <input type="hidden" name="phone_id" value="<?php echo $phone['phone_id']; ?>">

            <p>
                <label for="shipping_address">Shipping Address:</label><br>
                <input type="text" name="shipping_address" id="shipping_address" required>
            </p>
            <p>
                <label for="shipping_city">City:</label><br>
                <input type="text" name="shipping_city" id="shipping_city" required>
            </p>
            <p>
                <label for="shipping_zip">ZIP Code:</label><br>
                <input type="text" name="shipping_zip" id="shipping_zip" required>
            </p>

            <button type="submit" class="confirm-btn">Confirm Order</button>
        </form>
        <?php else: ?>
        <p style="color:red;">Out of Stock</p>
        <form action="../html/index.html" method="get">
            <button type="submit" class="back-btn">&lt;&lt; Back</button>
        </form>
        <?php endif; ?>
    </div>
</div>

</body>
</html>