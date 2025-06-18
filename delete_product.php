<?php
session_start();
include 'db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['user_id'];
$product_id = $_GET['id'] ?? null;

if (!$product_id) {
    die("Invalid product ID.");
}

$stmt = $conn->prepare("DELETE FROM products WHERE id = ? AND user_id = ?");
$stmt->bind_param("ii", $product_id, $user_id);
$stmt->execute();
$stmt->close();

header("Location: manage_products.php");
exit;
