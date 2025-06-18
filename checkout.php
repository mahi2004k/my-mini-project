<?php
session_start();

// Optional: Ensure user is logged in to place order
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

// ---------- CART & TOTALS ----------
$cart  = $_SESSION['cart'] ?? [];
$grand = 0;

foreach ($cart as $item) {
    $grand += $item['price'] * $item['quantity'];
}

if (!$cart) {
    header('Location: cart.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Checkout | myshop</title>
    <link rel="stylesheet" href="style.css">
    <style>
        body {
            font-family: 'Segoe UI', sans-serif;
            background-color: #f9f9f9;
            margin: 0;
            padding: 0;
        }
        .container {
            max-width: 800px;
            margin: 40px auto;
            background-color: white;
            padding: 30px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }
        h2 {
            border-bottom: 2px solid #ccc;
            padding-bottom: 10px;
        }
        table {
            width: 100%;
            margin-bottom: 30px;
            border-collapse: collapse;
        }
        th, td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }
        .total-row td {
            font-weight: bold;
        }
        form label {
            display: block;
            margin-top: 15px;
            font-weight: 600;
        }
        form input, form textarea, form select {
            width: 100%;
            padding: 10px;
            margin-top: 5px;
            border: 1px solid #ccc;
            border-radius: 4px;
            font-size: 14px;
        }
        .btn {
            margin-top: 20px;
            background-color: #1f2937;
            color: white;
            padding: 12px 20px;
            border: none;
            border-radius: 4px;
            font-size: 16px;
            cursor: pointer;
        }
        .btn:hover {
            background-color: #374151;
        }
    </style>
</head>
<body>
<div class="container">
    <h2>Review Your Cart</h2>
    <table>
        <tr>
            <th>Product</th>
            <th>Qty</th>
            <th>Price (₹)</th>
            <th>Total (₹)</th>
        </tr>
        <?php foreach ($cart as $item): ?>
        <tr>
            <td><?= htmlspecialchars($item['title']) ?></td>
            <td><?= $item['quantity'] ?></td>
            <td><?= number_format($item['price'], 2) ?></td>
            <td><?= number_format($item['price'] * $item['quantity'], 2) ?></td>
        </tr>
        <?php endforeach; ?>
        <tr class="total-row">
            <td colspan="3">Grand Total:</td>
            <td>₹<?= number_format($grand, 2) ?></td>
        </tr>
    </table>

    <h2>Shipping Details</h2>

    <form action="place_order.php" method="post">
        <input type="hidden" name="grand_total" value="<?= htmlspecialchars($grand) ?>">
        <input type="hidden" name="cart_json" value='<?= htmlspecialchars(json_encode($cart)) ?>'>

        <label for="billing_name">Billing Name*</label>
        <input id="billing_name" name="billing_name" placeholder="e.g. Priya Sharma" required>

        <label for="billing_address">Billing Address*</label>
        <textarea id="billing_address" name="billing_address" rows="4" placeholder="House/Flat no., Street, City, Pincode" required></textarea>

        <label for="contact_phone">Contact Phone*</label>
        <input id="contact_phone" name="contact_phone" type="tel" 
        placeholder="e.g. +91 9876543210" required pattern="[0-9+ ]{7,15}">

        <label for="payment_method">Payment Method*</label>
        <select id="payment_method" name="payment_method" required>
            <option value="">-- Select --</option>
            <option value="COD">Cash on Delivery</option>
            <option value="Online">Online Payment</option>
        </select>

        <button class="btn" type="submit">Place Order</button>
    </form>
</div>
</body>
</html>
