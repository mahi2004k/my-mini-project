<?php
session_start();
include 'header.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

include 'db.php';

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['title']);
    $description = trim($_POST['description']);
    $price = floatval($_POST['price']);
    $category = trim($_POST['category']);
    $seller_id = $_SESSION['user_id'];

    $image = '';

    // Upload main image
    if (isset($_FILES['image']) && $_FILES['image']['error'] === 0) {
        $target_dir = "uploads/";
        if (!is_dir($target_dir)) {
            mkdir($target_dir, 0777, true);
        }

        $filename = basename($_FILES["image"]["name"]);
        $sanitized_filename = preg_replace("/[^a-zA-Z0-9_\.-]/", "_", $filename);
        $final_filename = time() . "_" . $sanitized_filename;
        $target_file = $target_dir . $final_filename;
        $imageFileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));
        $allowed_types = ['jpg', 'jpeg', 'png', 'gif'];

        if (!in_array($imageFileType, $allowed_types)) {
            $error = "Only JPG, JPEG, PNG & GIF files are allowed.";
        } elseif (!move_uploaded_file($_FILES["image"]["tmp_name"], $target_file)) {
            $error = "Failed to upload main image.";
        } else {
            $image = $final_filename;
        }
    }

    // If no errors, insert into database
    if (empty($error) && !empty($image)) {
        if (!empty($title) && !empty($description) && $price > 0 && !empty($category)) {
            $stmt = $conn->prepare("INSERT INTO products (user_id, title, description, price, image, category) VALUES (?, ?, ?, ?, ?, ?)");
            $stmt->bind_param("issdss", $seller_id, $title, $description, $price, $image, $category);

            if ($stmt->execute()) {
                $product_id = $stmt->insert_id;

                // Handle multiple additional images
                if (!empty($_FILES['multi_images']['name'][0])) {
                    foreach ($_FILES['multi_images']['tmp_name'] as $index => $tmp_name) {
                        if ($_FILES['multi_images']['error'][$index] === 0) {
                            $extra_filename = basename($_FILES['multi_images']['name'][$index]);
                            $sanitized_extra = preg_replace("/[^a-zA-Z0-9_\.-]/", "_", $extra_filename);
                            $final_extra = time() . "_extra_" . $index . "_" . $sanitized_extra;
                            $extra_target = $target_dir . $final_extra;

                            if (move_uploaded_file($tmp_name, $extra_target)) {
                                $stmt_img = $conn->prepare("INSERT INTO product_images (product_id, image_path) VALUES (?, ?)");
                                $stmt_img->bind_param("is", $product_id, $final_extra);
                                $stmt_img->execute();
                                $stmt_img->close();
                            }
                        }
                    }
                }

                $success = "Product and images added successfully!";
            } else {
                $error = "Error adding product.";
            }

            $stmt->close();
        } else {
            $error = "Please fill in all required fields with valid values.";
        }
    }
}
?>

<link rel="stylesheet" href="style.css">

<div class="container form-container" style="max-width:600px; margin:30px auto; padding:20px; border:1px solid #ccc; border-radius:10px; background:#f9f9f9;">
    <h2 style="text-align:center;">Add New Product</h2>

    <?php if (!empty($error)): ?>
        <p style="color:red; text-align:center;"><?= htmlspecialchars($error) ?></p>
    <?php elseif (!empty($success)): ?>
        <p style="color:green; text-align:center;"><?= htmlspecialchars($success) ?></p>
    <?php endif; ?>

    <form action="" method="POST" enctype="multipart/form-data">
        <label>Product Title</label>
        <input type="text" name="title" required style="width:100%; padding:8px; margin:6px 0;" value="<?= htmlspecialchars($_POST['title'] ?? '') ?>">

        <label>Description</label>
        <textarea name="description" required style="width:100%; padding:8px; margin:6px 0;"><?= htmlspecialchars($_POST['description'] ?? '') ?></textarea>

        <label>Price (₹)</label>
        <input type="number" name="price" step="0.01" required style="width:100%; padding:8px; margin:6px 0;" value="<?= htmlspecialchars($_POST['price'] ?? '') ?>">

        <label>Category</label>
        <select name="category" required style="width:100%; padding:8px; margin:6px 0;">
            <option value="">--Select--</option>
            <option value="Electronics" <?= ($_POST['category'] ?? '') === 'Electronics' ? 'selected' : '' ?>>Electronics</option>
            <option value="Furniture" <?= ($_POST['category'] ?? '') === 'Furniture' ? 'selected' : '' ?>>Furniture</option>
            <option value="Vehicles" <?= ($_POST['category'] ?? '') === 'Vehicles' ? 'selected' : '' ?>>Vehicles</option>
            <option value="Fashion" <?= ($_POST['category'] ?? '') === 'Fashion' ? 'selected' : '' ?>>Fashion</option>
            <option value="Books" <?= ($_POST['category'] ?? '') === 'Books' ? 'selected' : '' ?>>Books</option>
        </select>

        <label>Main Product Image</label>
        <input type="file" name="image" accept="image/*" required style="margin:6px 0;">

        <label>Additional Images</label>
        <input type="file" name="multi_images[]" accept="image/*" multiple style="margin:6px 0;">

        <input type="submit" value="Add Product" class="btn" style="padding:10px 20px; background:#2ecc71; color:white; border:none; border-radius:5px;">
    </form>
</div>

<?php include 'footer.php'; ?>
