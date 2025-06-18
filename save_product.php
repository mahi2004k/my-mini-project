<?php
// save_product.php
require_once 'db.php';      // $conn (mysqli)

$title = $_POST['title']      ?? '';
$desc  = $_POST['description']?? '';
$price = $_POST['price']      ?? '';
$imagePath = '';

// 1.  File uploaded through <input type="file" name="image">
if (!empty($_FILES['image']['name']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
    $targetDir  = 'uploads/';
    $basename   = basename($_FILES['image']['name']);
    $safeName   = preg_replace('/[^A-Za-z0-9_.-]/', '_', $basename);   // simple sanitise
    $targetFile = $targetDir . time() . '_' . $safeName;

    if (move_uploaded_file($_FILES['image']['tmp_name'], $targetFile)) {
        $imagePath = $targetFile;
    }
}

/* 2.  OR the form might send a <select name="image"> with a path
       (e.g., uploads/existingfile.jpg). Only accept if it really sits
       inside the uploads/ directory to avoid path-traversal. */
if (!$imagePath && !empty($_POST['image'])) {
    $candidate = $_POST['image'];
    if (str_starts_with($candidate, 'uploads/')) {
        $imagePath = $candidate;
    }
}

/* ------- Insert into DB (products: title, description, price, image) ------- */
$sql = 'INSERT INTO products (title, description, price, image) VALUES (?,?,?,?)';
$stmt = $conn->prepare($sql);
$stmt->bind_param('ssds', $title, $desc, $price, $imagePath);
$stmt->execute();

/* ------- Go back to the product list ------- */
header('Location: index.php');
exit;
?>
