<?php
session_start();
require 'db.php';

$order_id = $_GET['order_id'] ?? 0;
if (!$order_id || !ctype_digit($order_id)) {
    exit('Invalid order.');
}

$order = null;
$stmt = $conn->prepare("SELECT billing_name FROM orders WHERE id = ?");
$stmt->bind_param('i', $order_id);
$stmt->execute();
$result = $stmt->get_result();
$order = $result->fetch_assoc();
$stmt->close();

if (!$order) {
    exit('Order not found.');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $billing_name      = trim($_POST['billing_name'] ?? '');
    $shipping_address  = trim($_POST['shipping_address'] ?? '');
    $contact_phone     = trim($_POST['contact_phone'] ?? '');
    $payment_method    = trim($_POST['payment_method'] ?? '');
    $card_number       = trim($_POST['card_number'] ?? '');
    $card_expiry       = trim($_POST['card_expiry'] ?? '');
    $card_cvc          = trim($_POST['card_cvc'] ?? '');

    if (!$billing_name) {
        $error = "Billing name is required.";
    } elseif (!$shipping_address) {
        $error = "Shipping address is required.";
    } elseif ($payment_method === 'credit_card' && (!$card_number || !$card_expiry || !$card_cvc)) {
        $error = "Credit card details are required.";
    }

    if (!isset($error)) {
        $stmt = $conn->prepare("UPDATE orders SET billing_name = ?, shipping_address = ?, contact_phone = ?, payment_method = ? WHERE id = ?");
        $stmt->bind_param('ssssi', $billing_name, $shipping_address, $contact_phone, $payment_method, $order_id);
        $stmt->execute();
        $stmt->close();

        header("Location: thank_you.php?order_id={$order_id}");
        exit;
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Shipping & Billing</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background: #f5f7fa;
            padding: 40px;
        }
        .form-container {
            max-width: 500px;
            margin: auto;
            background: white;
            padding: 30px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
            border-radius: 8px;
        }
        h2 {
            text-align: center;
            color: #333;
        }
        label {
            display: block;
            margin-top: 15px;
            font-weight: bold;
        }
        input[type="text"],
        textarea,
        select {
            width: 100%;
            padding: 10px;
            margin-top: 5px;
            box-sizing: border-box;
            border: 1px solid #ccc;
            border-radius: 6px;
        }
        textarea {
            resize: vertical;
        }
        .error {
            color: red;
            margin-top: 10px;
        }
        .card-details {
            display: none;
        }
        button {
            margin-top: 20px;
            padding: 12px;
            width: 100%;
            background-color: #4CAF50;
            border: none;
            color: white;
            font-size: 16px;
            border-radius: 6px;
            cursor: pointer;
        }
        button:hover {
            background-color: #45a049;
        }
    </style>
    <script>
        function toggleCardDetails() {
            const paymentMethod = document.querySelector('input[name="payment_method"]:checked').value;
            const cardFields = document.getElementById('card-details');
            cardFields.style.display = paymentMethod === 'credit_card' ? 'block' : 'none';
        }

        window.addEventListener('DOMContentLoaded', () => {
            const paymentOptions = document.querySelectorAll('input[name="payment_method"]');
            paymentOptions.forEach(option => {
                option.addEventListener('change', toggleCardDetails);
            });
            toggleCardDetails(); // Initialize on load
        });
    </script>
</head>
<body>
    <div class="form-container">
        <h2>Shipping & Billing Details</h2>
        <?php if (!empty($error)): ?>
            <div class="error"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>
        <form method="post">
            <label for="billing_name">Billing Name:</label>
            <input type="text" id="billing_name" name="billing_name" required value="<?= htmlspecialchars($_POST['billing_name'] ?? $order['billing_name'] ?? '') ?>">

            <label for="shipping_address">Shipping Address:</label>
            <textarea id="shipping_address" name="shipping_address" rows="4" required><?= htmlspecialchars($_POST['shipping_address'] ?? '') ?></textarea>

            <label for="contact_phone">Contact Phone:</label>
            <input type="text" id="contact_phone" name="contact_phone" value="<?= htmlspecialchars($_POST['contact_phone'] ?? '') ?>">

            <label>Payment Method:</label>
            <label><input type="radio" name="payment_method" value="credit_card" <?= ($_POST['payment_method'] ?? '') === 'credit_card' ? 'checked' : '' ?>> Credit Card</label>
            <label><input type="radio" name="payment_method" value="paypal" <?= ($_POST['payment_method'] ?? '') === 'paypal' ? 'checked' : '' ?>> PayPal</label>
            <label><input type="radio" name="payment_method" value="cod" <?= ($_POST['payment_method'] ?? '') === 'cod' ? 'checked' : '' ?>> Cash on Delivery</label>

            <div id="card-details" class="card-details">
                <label for="card_number">Card Number:</label>
                <input type="text" id="card_number" name="card_number" value="<?= htmlspecialchars($_POST['card_number'] ?? '') ?>">

                <label for="card_expiry">Expiry Date (MM/YY):</label>
                <input type="text" id="card_expiry" name="card_expiry" value="<?= htmlspecialchars($_POST['card_expiry'] ?? '') ?>">

                <label for="card_cvc">CVC:</label>
                <input type="text" id="card_cvc" name="card_cvc" value="<?= htmlspecialchars($_POST['card_cvc'] ?? '') ?>">
            </div>

            <button type="submit">Submit</button>
        </form>
    </div>
</body>
</html>
