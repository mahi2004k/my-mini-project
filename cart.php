<?php
/* ==========================================================
   cart.php  –  view / manage the shopping-cart
   ==========================================================*/

session_start(); 
/* ----------------------------------------------------------
   1) Pull the cart and be sure it’s an ARRAY
----------------------------------------------------------*/
$cart = $_SESSION['cart'] ?? [];

/* ----------------------------------------------------------
   2) Strip out any corrupt rows (not arrays or missing keys)
----------------------------------------------------------*/
$cart = array_values(array_filter($cart, function ($row) {
   return is_array($row) &&
       isset($row['title'], $row['price'], $row['quantity']) &&
       is_numeric($row['price']) && $row['price'] >= 0 &&
       is_numeric($row['quantity']) && $row['quantity'] > 0;

}));
$_SESSION['cart'] = $cart;   // save the cleaned cart

/* ----------------------------------------------------------
   3) Handle “remove” action:  cart.php?index=N
----------------------------------------------------------*/
if (isset($_GET['index'])) {
    $idx = $_GET['index'];

    if (ctype_digit($idx) && isset($cart[$idx])) {
        unset($cart[$idx]);
        $cart = array_values($cart);        // re-index
        $_SESSION['cart'] = $cart;
    }

    /* ––– recalc badge ––– */
    $_SESSION['cart_count'] = array_sum(
        array_column($cart, 'quantity')
    );

    header('Location: cart.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Your Cart</title>
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <link rel="stylesheet" href="style.css">
</head>
<body>

<?php include 'header.php'; /* navbar / site header */ ?>

<div class="container">
    <a href="index.php">← Continue Shopping</a>
    <hr>

<?php if ($cart): ?>
    <table class="cart-table">
        <tr>
            <th>Product</th>
            <th>Price&nbsp;(₹)</th>
            <th>Qty</th>
            <th>Total&nbsp;(₹)</th>
            <th></th>
        </tr>

        <?php
        $grand = 0;
        foreach ($cart as $i => $item):
            /* guaranteed safe now */
            $lineTotal = $item['price'] * $item['quantity'];
            $grand    += $lineTotal;
        ?>
        <tr>
            <td><?= htmlspecialchars($item['title']) ?></td>
            <td><?= number_format($item['price'], 2) ?></td>
            <td><?= $item['quantity'] ?></td>
            <td><?= number_format($lineTotal, 2) ?></td>
            <td><a href="cart.php?index=<?= $i ?>" class="remove-item">Remove</a></td>
        </tr>
        <?php endforeach; ?>

        <tr>
            <td colspan="3" align="right"><strong>Grand&nbsp;Total:</strong></td>
            <td colspan="2"><strong>₹<?= number_format($grand, 2) ?></strong></td>
        </tr>
    </table>

    <div class="checkout">
        <a href="checkout.php" class="checkout-btn">Proceed to Checkout</a>
    </div>
<?php else: ?>
    <p>Your cart is empty.</p>
<?php endif; ?>
</div>

</body>
</html>
