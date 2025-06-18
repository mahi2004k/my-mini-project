<?php
/* =========================================
   place_order.php – receives POST from checkout
   =========================================*/

session_start();
require 'db.php'; // gives $conn (MySQLi)

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: index.php');
    exit;
}

/* -------------------------------------------------
   1. Validate required checkout fields
-------------------------------------------------*/
$required = ['billing_name', 'billing_address', 'contact_phone', 'payment_method'];
foreach ($required as $f) {
    if (empty($_POST[$f])) {
        exit("Missing field: {$f}");
    }
}

/* -------------------------------------------------
   2. Sanitise input
-------------------------------------------------*/
$billing_name    = trim($_POST['billing_name']);
$billing_address = trim($_POST['billing_address']);
$contact_phone   = trim($_POST['contact_phone']);
$payment_method  = $_POST['payment_method']; // e.g. COD / CARD

/* -------------------------------------------------
   3. Compute grand-total from the cart
-------------------------------------------------*/
$cart  = $_SESSION['cart'] ?? [];
if (!$cart) {
    exit("Your cart is empty.");
}

$grand = 0.0;
foreach ($cart as $line) {
    $grand += (float)$line['price'] * (int)$line['quantity'];
}

/* -------------------------------------------------
   4. Insert into orders
-------------------------------------------------*/
$orderSql = "
    INSERT INTO orders (billing_name, billing_address, contact_phone, payment_method, grand_total)
    VALUES (?, ?, ?, ?,?)
";
$ordStmt = $conn->prepare($orderSql);
if (!$ordStmt) {
    exit("Prepare failed: " . $conn->error);
}
$ordStmt->bind_param('ssssd', $billing_name, $billing_address, $contact_phone, $payment_method, $grand);
if (!$ordStmt->execute()) {
    exit('Order insert failed: ' . $conn->error);
}
$order_id = $ordStmt->insert_id;
$ordStmt->close();

/* -------------------------------------------------
   5. Insert each cart line into order_items
-------------------------------------------------*/
if ($order_id && $cart) {
    $itemSql = "
        INSERT INTO order_items (order_id, product_id, title, price, quantity)
        VALUES (?, ?, ?, ?, ?)
    ";
    $itemStmt = $conn->prepare($itemSql);
    if (!$itemStmt) {
        exit("Prepare failed: " . $conn->error);
    }

    foreach ($cart as $line) {
        $itemStmt->bind_param(
            'iisdi',
            $order_id,
            $line['id'],
            $line['title'],
            $line['price'],
            $line['quantity']
        );
        if (!$itemStmt->execute()) {
            exit('Item insert failed: ' . $conn->error);
        }
    }
    $itemStmt->close();
}

/* -------------------------------------------------
   6. Clear cart & badge
-------------------------------------------------*/
unset($_SESSION['cart'], $_SESSION['cart_count']);

/* -------------------------------------------------
   7. Show thank-you page
-------------------------------------------------*/

header("Location: thank_you.php?order_id={$order_id}");
exit;
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
<title>Thank you!</title>
<meta name="viewport" content="width=device-width,initial-scale=1">
<style>
body{font-family:Arial,Helvetica,sans-serif;background:#f7f7f7;margin:0}
.box{max-width:600px;margin:60px auto;background:#fff;padding:40px;border-radius:8px;
     box-shadow:0 2px 8px rgba(0,0,0,.1);text-align:center}
h1{color:#4caf50;margin-top:0}
a.btn{display:inline-block;margin-top:30px;padding:12px 28px;background:#4caf50;
      color:#fff;text-decoration:none;border-radius:6px;font-weight:600}
a.btn:hover{opacity:.9}
</style>
</head>
<body>
<form action="place_order.php" method="post">
    <input type="hidden" name="grand_total" value="<?= $grand ?>">
    <input type="hidden" name="cart_json" value='<?= json_encode($cart) ?>'>

    <label for="billing_name">Billing Name*</label>
    <input id="billing_name" name="billing_name" placeholder="e.g. Priya Sharma" required>

    <label for="billing_address">Billing Address*</label>
    <textarea id="billing_address" name="billing_address" rows="4" placeholder="House/Flat no., Street, City, Pincode" required></textarea>

    <label for="contact_phone">Contact Phone*</label>
    <input id="contact_phone" name="contact_phone" type="tel" placeholder="e.g. +91 9876543210" required pattern="[0-9+ ]{7,15}">

    <label for="payment_method">Payment Method*</label>
    <select id="payment_method" name="payment_method" required>
        <option value="">-- Select --</option>
        <option value="COD">Cash on Delivery</option>
        <option value="Online">Online Payment</option>
    </select>

    <button class="btn" type="submit">Place Order</button>
</form>


</body>
</html>


