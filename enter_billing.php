<?php
session_start();

$redirect = $_GET['redirect'] ?? 'index.php';
$product_id = $_GET['id'] ?? '';
$error = '';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $billing_name   = trim($_POST['billing_name'] ?? '');
    $contact_phone  = trim($_POST['contact_phone'] ?? '');
    $email          = trim($_POST['email'] ?? '');
    $payment_method = $_POST['payment_method'] ?? 'COD';

    if ($billing_name === '' || strtolower($billing_name) === 'guest') {
        $error = "Please enter a valid billing name (not 'Guest').";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Please enter a valid email address.";
    } elseif (!in_array($payment_method, ['COD', 'Credit Card', 'PayPal'])) {
        $error = "Invalid payment method selected.";
    } else {
        // Save billing info to session
        $_SESSION['billing_name'] = $billing_name;
        $_SESSION['contact_phone'] = $contact_phone;
        $_SESSION['email'] = $email;
        $_SESSION['payment_method'] = $payment_method;

        // Redirect to original page
        header("Location: /buy_now.php?id={$product_id}");
        exit;


    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Enter Billing Info</title>
    <style>
        body {
            font-family: 'Segoe UI', sans-serif;
            background: #f0f2f5;
            padding: 30px;
        }
        form {
            background: #fff;
            padding: 25px;
            max-width: 450px;
            margin: auto;
            border-radius: 10px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }
        h2 {
            text-align: center;
            margin-bottom: 20px;
            color: #333;
        }
        label {
            display: block;
            font-weight: 500;
            margin-bottom: 6px;
        }
        input, select {
            width: 100%;
            padding: 10px;
            margin-bottom: 16px;
            border: 1px solid #ccc;
            border-radius: 6px;
            transition: border 0.3s ease;
        }
        input:focus, select:focus {
            border-color: #007bff;
            outline: none;
        }
        button {
            width: 100%;
            padding: 12px;
            background: #007bff;
            color: white;
            border: none;
            border-radius: 6px;
            font-size: 16px;
            cursor: pointer;
            transition: background 0.3s ease;
        }
        button:hover {
            background: #0056b3;
        }
        .error {
            color: red;
            margin-bottom: 15px;
            font-weight: 500;
        }
    </style>
</head>
<body>
    <form method="post">
        <h2>Billing & Payment Details</h2>
        <?php if ($error): ?>
            <div class="error"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <label for="billing_name">Billing Name</label>
        <input type="text" id="billing_name" name="billing_name" required
               value="<?= htmlspecialchars($_POST['billing_name'] ?? '') ?>">

        <label for="email">Email Address</label>
        <input type="email" id="email" name="email" required
               value="<?= htmlspecialchars($_POST['email'] ?? '') ?>">

        <label for="contact_phone">Contact Phone (optional)</label>
        <input type="text" id="contact_phone" name="contact_phone"
               value="<?= htmlspecialchars($_POST['contact_phone'] ?? '') ?>">

        <label for="payment_method">Payment Method</label>
        <select id="payment_method" name="payment_method" required>
            <option value="COD" <?= (($_POST['payment_method'] ?? '') === 'COD') ? 'selected' : '' ?>>Cash on Delivery</option>
            <option value="Credit Card" <?= (($_POST['payment_method'] ?? '') === 'Credit Card') ? 'selected' : '' ?>>Credit Card</option>
            <option value="PayPal" <?= (($_POST['payment_method'] ?? '') === 'PayPal') ? 'selected' : '' ?>>PayPal</option>
        </select>

        <button type="submit">Continue</button>
    </form>
</body>
</html>
