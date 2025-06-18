<?php
session_start();
require 'db.php';

/* 1. Validate incoming product ID */
$id = $_GET['id'] ?? '';
if (!ctype_digit($id)) {
    header('Location: index.php');
    exit;
}
$id = (int)$id;

/* 2. Fetch product details from DB */
$stmt = $conn->prepare("SELECT id, title, price FROM products WHERE id = ?");
if (!$stmt) {
    exit("Database error: " . $conn->error);
}
$stmt->bind_param('i', $id);
$stmt->execute();
$product = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$product) {
    header('Location: index.php');
    exit;
}

/* 3. Collect order details from session */
$user_id        = $_SESSION['user_id'] ?? null;
$billing_name   = trim($_SESSION['billing_name'] ?? '');
$contact_phone  = trim($_SESSION['contact_phone'] ?? '');
$email          = trim($_SESSION['email'] ?? '');
$payment_method = $_SESSION['payment_method'] ?? 'COD';
$shipping_addr  = '- Buy Now -';
$grand_total    = $product['price'];

/* 4. Validate session data */
if ($billing_name === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $safe_id = htmlspecialchars($id);
    header("Location: enter_billing.php?redirect=buy_now&id={$safe_id}");
    exit;
}

/* 5. Insert order */
$orderSql = "
    INSERT INTO orders
        (user_id, billing_name, shipping_address, payment_method, grand_total, contact_phone, email)
    VALUES (?, ?, ?, ?, ?, ?, ?)
";
$ordStmt = $conn->prepare($orderSql);
if (!$ordStmt) {
    exit("Prepare failed: " . $conn->error);
}
$ordStmt->bind_param(
    'isssdss',
    $user_id,
    $billing_name,
    $shipping_addr,
    $payment_method,
    $grand_total,
    $contact_phone,
    $email
);
if (!$ordStmt->execute()) {
    exit("Order insert failed: " . $ordStmt->error);
}
$order_id = $ordStmt->insert_id;
$ordStmt->close();

/* 6. Insert order item */
$itemSql = "
    INSERT INTO order_items
        (order_id, product_id, title, price, quantity)
    VALUES (?, ?, ?, ?, 1)
";
$itemStmt = $conn->prepare($itemSql);
if (!$itemStmt) {
    exit("Prepare failed: " . $conn->error);
}
$itemStmt->bind_param(
    'iisd',
    $order_id,
    $product['id'],
    $product['title'],
    $product['price']
);
if (!$itemStmt->execute()) {
    exit("Item insert failed: " . $itemStmt->error);
}
$itemStmt->close();

/* 7. Clear cart-related session data if applicable */
unset($_SESSION['cart'], $_SESSION['cart_count']);

/* 8. Redirect to shipping details */
$safe_order_id = htmlspecialchars($order_id);
header("Location: shipping_details.php?order_id={$safe_order_id}");
exit;
?>