<?php
// cart_add.php
session_start();
require 'db.php';        // so we can pull price/title from DB

/* ----------------------------------------------------------
   1. Accept input (GET or POST)
----------------------------------------------------------*/
$id = $_GET['id']  ?? $_POST['id']  ?? '';
$id = ctype_digit($id) ? (int)$id : 0;

if (!$id) {
    header('Location: index.php');
    exit;
}

/* ----------------------------------------------------------
   2. Fetch product details from database
      – never trust data coming from the browser
----------------------------------------------------------*/
$stmt = $conn->prepare(
    "SELECT id, title, price FROM products WHERE id=? LIMIT 1"
);
$stmt->bind_param('i', $id);
$stmt->execute();
$product = $stmt->get_result()->fetch_assoc();

if (!is_numeric($product['price']) || $product['price'] < 0) {
    header('Location: index.php');
    exit;
}


/* ----------------------------------------------------------
   3. Initialise cart structure in session
----------------------------------------------------------*/
if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

/* ----------------------------------------------------------
   4. Add item or bump quantity
----------------------------------------------------------*/
$found = false;
foreach ($_SESSION['cart'] as &$line) {
    if ($line['id'] == $product['id']) {
        $line['quantity']++;          // already in cart → +1
        $found = true;
        break;
    }
}
unset($line);                         // break reference

if (!$found) {                        // first time we see it
    $_SESSION['cart'][] = [
        'id'       => $product['id'],
        'title'    => $product['title'],
        'price'    => (float)$product['price'],
        'quantity' => 1
    ];
}

/* ----------------------------------------------------------
   5. Badge counter for navbar
----------------------------------------------------------*/
$_SESSION['cart_count'] = array_sum(
    array_column($_SESSION['cart'], 'quantity')
);

/* ----------------------------------------------------------
   6. Bounce back to previous page
----------------------------------------------------------*/
header('Location: ' . ($_SERVER['HTTP_REFERER'] ?? 'index.php'));
exit;
