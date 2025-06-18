<?php
session_start();
require 'db.php'; // gives $conn

// 1. Validate order_id from GET
$order_id = $_GET['order_id'] ?? '';
if (!ctype_digit($order_id)) {
    header('Location: index.php');
    exit;
}
$order_id = (int)$order_id;

// 2. Fetch order details
$stmt = $conn->prepare("
    SELECT billing_name, grand_total 
    FROM orders 
    WHERE id = ?
");
$stmt->bind_param('i', $order_id);
$stmt->execute();
$order = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$order) {
    exit('Order not found.');
}

// 3. Fetch order items (optional)
$itemStmt = $conn->prepare("
    SELECT product_id, title, price, quantity 
    FROM order_items 
    WHERE order_id = ?
");
$itemStmt->bind_param('i', $order_id);
$itemStmt->execute();
$order_items = $itemStmt->get_result()->fetch_all(MYSQLI_ASSOC);
$itemStmt->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
<title>Thank You for Your Order</title>
<meta name="viewport" content="width=device-width,initial-scale=1">
<style>
body { font-family: Arial, Helvetica, sans-serif; background: #f7f7f7; margin: 0; }
.box { max-width: 700px; margin: 60px auto; background: #fff; padding: 40px; border-radius: 8px;
      box-shadow: 0 2px 8px rgba(0,0,0,.1); }
h1 { color: #4caf50; margin-top: 0; }
table { width: 100%; border-collapse: collapse; margin-top: 20px; }
th, td { padding: 12px; border-bottom: 1px solid #ddd; text-align: left; }
.total { font-weight: bold; }
a.btn {
    display: inline-block;
    margin-top: 30px;
    padding: 12px 28px;
    background: #4caf50;
    color: #fff;
    text-decoration: none;
    border-radius: 6px;
    font-weight: 600;
}
a.btn:hover { opacity: 0.9; }
</style>
</head>
<body>
<div class="box">
    <h1>Thank you, <?= htmlspecialchars($order['billing_name']) ?>!</h1>
    <p>Your order has been placed successfully.</p>
    <p><strong>Order ID:</strong> #<?= $order_id ?></p>

    <h2>Order Details</h2>
    <table>
        <thead>
            <tr>
                <th>Product</th>
                <th>Price (₹)</th>
                <th>Quantity</th>
                <th>Subtotal (₹)</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($order_items as $item): ?>
            <tr>
                <td><?= htmlspecialchars($item['title']) ?></td>
                <td><?= number_format($item['price'], 2) ?></td>
                <td><?= (int)$item['quantity'] ?></td>
                <td><?= number_format($item['price'] * $item['quantity'], 2) ?></td>
            </tr>
            <?php endforeach; ?>
            <tr class="total">
                <td colspan="3" style="text-align:right">Total Paid:</td>
                <td>₹ <?= number_format($order['grand_total'], 2) ?></td>
            </tr>
        </tbody>
    </table>

    <a class="btn" href="index.php">Back to Home</a>
</div>
</body>
</html>
