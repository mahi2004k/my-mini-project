<?php
session_start();
include 'header.php';
include 'db.php';

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    echo "<p>Invalid product ID.</p>";
    exit;
}

$product_id = (int)$_GET['id'];
$stmt = $conn->prepare("SELECT p.*, u.username, u.email FROM products p JOIN users u ON p.user_id = u.id WHERE p.id = ?");
$stmt->bind_param("i", $product_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    echo "<p>Product not found.</p>";
    exit;
}

$product = $result->fetch_assoc();

// Fetch additional images
$img_stmt = $conn->prepare("SELECT image_path FROM product_images WHERE product_id = ?");
$img_stmt->bind_param("i", $product_id);
$img_stmt->execute();
$img_result = $img_stmt->get_result();
$extra_images = [];
while ($row = $img_result->fetch_assoc()) {
    $extra_images[] = $row['image_path'];
}
$img_stmt->close();
$stmt->close();
?>

<link rel="stylesheet" href="style.css">

<div class="product-details-container">
    <div class="image-section">
        <?php
        $main_img = htmlspecialchars($product['image']);
        $main_path = (strpos($main_img, 'uploads/') === 0) ? $main_img : 'uploads/' . $main_img;
        ?>
        <div class="main-image">
            <img id="mainProductImage" src="<?= $main_path ?>" alt="<?= htmlspecialchars($product['title']) ?>" class="product-image">
        </div>

        <?php if (!empty($extra_images)): ?>
            <div class="image-gallery" id="imageGallery">
                <img src="<?= $main_path ?>" class="gallery-image active" onclick="changeMainImage(this)">
                <?php foreach ($extra_images as $img): 
                    $path = (strpos($img, 'uploads/') === 0) ? $img : 'uploads/' . $img;
                ?>
                    <img src="<?= htmlspecialchars($path) ?>" class="gallery-image" onclick="changeMainImage(this)">
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>

    <div class="details-section">
        <h2><?= htmlspecialchars($product['title']) ?></h2>
        <p><strong>Description:</strong><br><?= nl2br(htmlspecialchars($product['description'])) ?></p>
        <p><strong>Price:</strong> ₹ <?= number_format($product['price'], 2) ?></p>
        <p><strong>Seller:</strong> <?= htmlspecialchars($product['username']) ?></p>

        <div class="action-buttons">
            <a href="mailto:<?= htmlspecialchars($product['email']) ?>" class="btn message-btn">💬 Message Seller</a>

            <?php if (isset($_SESSION['user_id'])): ?>
                <a href="cart_add.php?id=<?= $product_id ?>" class="btn cart-btn">🛒 Add to Cart</a>
                <a href="buy_now.php?id=<?= $product_id ?>" class="btn buy-btn">⚡ Buy Now</a>
            <?php else: ?>
                <p class="note">Please <a href="login.php">login</a> to purchase.</p>
            <?php endif; ?>
        </div>
    </div>
</div>

<script>
function changeMainImage(img) {
    const main = document.getElementById('mainProductImage');
    main.src = img.src;

    document.querySelectorAll('.gallery-image').forEach(el => el.classList.remove('active'));
    img.classList.add('active');
}
</script>

<style>
/* Layout */
.product-details-container {
    display: flex;
    flex-wrap: wrap;
    gap: 30px;
    padding: 40px 20px;
    max-width: 1100px;
    margin: auto;
    background-color: #fefefe;
    box-shadow: 0 0 10px rgba(0,0,0,0.08);
    border-radius: 12px;
}

.image-section {
    flex: 1;
    min-width: 300px;
}

.details-section {
    flex: 1;
    min-width: 300px;
}

/* Main image */
.main-image img {
    width: 100%;
    max-width: 400px;
    border-radius: 12px;
    box-shadow: 0 4px 8px rgba(0,0,0,0.1);
}

/* Gallery */
.image-gallery {
    margin-top: 15px;
    display: flex;
    flex-wrap: wrap;
    gap: 10px;
}

.gallery-image {
    width: 80px;
    height: 80px;
    object-fit: cover;
    border: 2px solid transparent;
    border-radius: 8px;
    cursor: pointer;
    transition: all 0.3s ease;
}

.gallery-image:hover {
    transform: scale(1.05);
    border-color: #4CAF50;
}

.gallery-image.active {
    border-color: #4CAF50;
}

/* Text and buttons */
.details-section h2 {
    margin-bottom: 15px;
    color: #333;
}

.details-section p {
    margin: 10px 0;
    font-size: 16px;
    color: #555;
}

.action-buttons {
    margin-top: 20px;
}

.btn {
    display: inline-block;
    margin-right: 15px;
    margin-bottom: 10px;
    padding: 10px 16px;
    font-size: 16px;
    border-radius: 8px;
    text-decoration: none;
    color: white;
    background-color: #4CAF50;
    transition: background-color 0.3s ease;
}

.btn:hover {
    background-color: #388e3c;
}

.message-btn {
    background-color: #6a1b9a;
}

.message-btn:hover {
    background-color: #4a148c;
}

.cart-btn {
    background-color: #0288d1;
}

.cart-btn:hover {
    background-color: #0277bd;
}

.buy-btn {
    background-color: #e53935;
}

.buy-btn:hover {
    background-color: #c62828;
}

.note {
    font-style: italic;
    margin-top: 15px;
}
</style>

<?php include 'footer.php'; ?>
