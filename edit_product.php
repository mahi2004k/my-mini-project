<?php
session_start();
include 'header.php';
include 'db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['user_id'];
$product_id = $_GET['id'] ?? null;

if (!$product_id) {
    die("Product ID missing.");
}

// Handle delete request for additional image
if (isset($_GET['delete_image'])) {
    $image_id = intval($_GET['delete_image']);
    $stmt = $conn->prepare("SELECT image_path FROM product_images WHERE id = ? AND product_id = ?");
    $stmt->bind_param("ii", $image_id, $product_id);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($img = $result->fetch_assoc()) {
        unlink('uploads/' . $img['image_path']); // delete file
        $conn->query("DELETE FROM product_images WHERE id = $image_id");
    }
    header("Location: edit_product.php?id=$product_id");
    exit;
}

// Handle update form
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = $_POST['title'];
    $desc = $_POST['description'];
    $price = $_POST['price'];

    if (!empty($_FILES['image']['name'])) {
        $main_filename = basename($_FILES['image']['name']);
        $main_sanitized = time() . "_" . preg_replace("/[^a-zA-Z0-9_\.-]/", "_", $main_filename);
        $target = "uploads/" . $main_sanitized;
        move_uploaded_file($_FILES['image']['tmp_name'], $target);

        $stmt = $conn->prepare("UPDATE products SET title=?, description=?, price=?, image=? WHERE id=? AND user_id=?");
        $stmt->bind_param("ssdsii", $title, $desc, $price, $main_sanitized, $product_id, $user_id);
    } else {
        $stmt = $conn->prepare("UPDATE products SET title=?, description=?, price=? WHERE id=? AND user_id=?");
        $stmt->bind_param("ssdii", $title, $desc, $price, $product_id, $user_id);
    }

    $stmt->execute();
    $stmt->close();

    // Handle new additional images
    if (!empty($_FILES['multi_images']['name'][0])) {
        foreach ($_FILES['multi_images']['tmp_name'] as $index => $tmp_name) {
            if ($_FILES['multi_images']['error'][$index] === 0) {
                $extra_name = basename($_FILES['multi_images']['name'][$index]);
                $final_extra = time() . "_extra_" . $index . "_" . preg_replace("/[^a-zA-Z0-9_\.-]/", "_", $extra_name);
                $extra_path = "uploads/" . $final_extra;

                if (move_uploaded_file($tmp_name, $extra_path)) {
                    $stmt_img = $conn->prepare("INSERT INTO product_images (product_id, image_path) VALUES (?, ?)");
                    $stmt_img->bind_param("is", $product_id, $final_extra);
                    $stmt_img->execute();
                    $stmt_img->close();
                }
            }
        }
    }

    header("Location: manage_products.php");
    exit;
}

// Fetch product
$stmt = $conn->prepare("SELECT * FROM products WHERE id = ? AND user_id = ?");
$stmt->bind_param("ii", $product_id, $user_id);
$stmt->execute();
$result = $stmt->get_result();
$product = $result->fetch_assoc();
if (!$product) {
    die("Product not found or unauthorized access.");
}

// Fetch additional images
$extra_images = [];
$res = $conn->query("SELECT id, image_path FROM product_images WHERE product_id = $product_id");
while ($row = $res->fetch_assoc()) {
    $extra_images[] = $row;
}
?>

<div class="edit-container">
    <h2>Edit Product</h2>
    <form method="post" enctype="multipart/form-data" class="edit-form">
        <label>Title:</label>
        <input type="text" name="title" value="<?= htmlspecialchars($product['title']) ?>" required>

        <label>Description:</label>
        <textarea name="description" rows="5" required><?= htmlspecialchars($product['description']) ?></textarea>

        <label>Price (₹):</label>
        <input type="number" name="price" step="0.01" value="<?= htmlspecialchars($product['price']) ?>" required>

        <label>Main Image (leave empty to keep current):</label>
        <input type="file" name="image">
        <?php if (!empty($product['image'])): ?>
            <div class="current-image">
                <strong>Current Image:</strong><br>
                <img src="uploads/<?= htmlspecialchars($product['image']) ?>" alt="Main Image">
            </div>
        <?php endif; ?>

        <label>Additional Images:</label>
        <input type="file" name="multi_images[]" multiple>

        <?php if (!empty($extra_images)): ?>
            <div class="extra-images">
                <strong>Current Additional Images:</strong><br>
                <?php foreach ($extra_images as $img): ?>
                    <div class="extra-img-box">
                        <img src="uploads/<?= htmlspecialchars($img['image_path']) ?>" alt="Extra">
                        <a href="?id=<?= $product_id ?>&delete_image=<?= $img['id'] ?>" onclick="return confirm('Delete this image?');">❌</a>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <button type="submit" class="btn-submit">Update Product</button>
    </form>
</div>

<!-- Styling -->
<style>
.edit-container {
    max-width: 600px;
    margin: 40px auto;
    background: #f8f9fa;
    padding: 25px 30px;
    border-radius: 10px;
    box-shadow: 0 4px 12px rgba(0,0,0,0.1);
}
.edit-container h2 {
    margin-bottom: 20px;
    text-align: center;
    color: #333;
}
.edit-form label {
    display: block;
    margin-top: 15px;
    font-weight: bold;
    color: #555;
}
.edit-form input[type="text"],
.edit-form input[type="number"],
.edit-form textarea,
.edit-form input[type="file"] {
    width: 100%;
    padding: 10px;
    margin-top: 6px;
    border: 1px solid #ccc;
    border-radius: 6px;
    font-size: 16px;
}
.edit-form .btn-submit {
    margin-top: 20px;
    width: 100%;
    background: #4CAF50;
    color: white;
    padding: 12px;
    border: none;
    border-radius: 6px;
    font-size: 16px;
    cursor: pointer;
    transition: background 0.3s;
}
.edit-form .btn-submit:hover {
    background: #45a049;
}
.current-image,
.extra-images {
    margin-top: 15px;
}
.current-image img,
.extra-images img {
    max-width: 100%;
    height: auto;
    margin-top: 8px;
    border: 1px solid #ccc;
    border-radius: 8px;
}
.extra-img-box {
    position: relative;
    display: inline-block;
    margin: 10px;
}
.extra-img-box img {
    max-width: 100px;
    height: auto;
    border: 1px solid #ccc;
    border-radius: 6px;
}
.extra-img-box a {
    position: absolute;
    top: -8px;
    right: -8px;
    background: red;
    color: white;
    padding: 2px 6px;
    border-radius: 50%;
    font-size: 12px;
    text-decoration: none;
}
</style>
