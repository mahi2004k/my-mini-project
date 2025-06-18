<?php
// remove_from_cart.php
session_start();

/* -----------------------------------------------------------
   Validate the query-string
------------------------------------------------------------*/
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    http_response_code(400);               // Bad Request
    exit('Invalid product id.');
}

$product_id = (int) $_GET['id'];

/* -----------------------------------------------------------
   Remove item if it exists
------------------------------------------------------------*/
if (isset($_SESSION['cart'][$product_id])) {
    unset($_SESSION['cart'][$product_id]);
}

/* Re-calculate the badge count */
$_SESSION['cart_count'] = isset($_SESSION['cart'])
    ? array_sum(array_column($_SESSION['cart'], 'quantity'))
    : 0;

/* -----------------------------------------------------------
   Send the user back to the cart
------------------------------------------------------------*/
header('Location: cart.php');
exit;
?>
