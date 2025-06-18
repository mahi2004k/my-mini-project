<?php
// header.php
                           // keep cart & login data
$_SESSION['cart_count'] = $_SESSION['cart_count'] ?? 0;
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>My Store</title>
    <link rel="stylesheet" href="style.css">


    <!-- Responsive layout -->
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- Main stylesheet -->
    <link rel="stylesheet" href="/assets/css/style.css">
</head>

<!-- header.php -->
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>MyShop</title>
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <link rel="stylesheet" href="style.css">
</head>
<body>



<!-- ===== Navbar (sticks to top) ===== -->
<nav class="navbar">
    <div class="navbar-left">
        <a href="index.php" class="logo">MyShop</a>
    </div>

    <div class="navbar-right">
        <a href="cart.php" class="cart-icon" aria-label="View Cart">
            🛒
            <span class="cart-count" id="cart-count">
                <?= $_SESSION['cart_count'] ?? 0 ?>
            </span>
        </a>
    </div>
</nav>

<!-- page-specific content starts after header.php is included -->

<body>

<main>
