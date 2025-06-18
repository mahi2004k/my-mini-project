<?php
session_start();
include 'header.php';
include 'db.php';

// Redirect if not logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['user_id'];

// Fetch username
$stmt = $conn->prepare("SELECT username FROM users WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$stmt->bind_result($username);
$stmt->fetch();
$stmt->close();

$_SESSION['username'] = $username;
?>

<link rel="stylesheet" href="style.css">

<!-- Navbar -->
<nav class="navbar">
    <a href="index.php" class="logo">MyShop</a>
    <div>
        <span style="color:#4CAF50; font-weight:bold; margin-right: 15px;">
            Welcome, <?= htmlspecialchars($username) ?>!
        </span>
        <a href="logout.php" class="btn-login" style="background:#e74c3c;">Logout</a>
        <a href="cart.php" class="cart-icon">
            🛒 <span class="cart-count" id="cart-count"><?= $_SESSION['cart_count'] ?? 0 ?></span>
        </a>
    </div>
</nav>

<div class="welcome-box">
    <h2>Your Product Management</h2>
</div>

<main>
<?php
$sql = "SELECT products.*, users.username FROM products 
        JOIN users ON products.user_id = users.id 
        WHERE products.user_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result && $result->num_rows > 0) {
    echo "<h2 style='padding-left:20px;'>Your Products</h2>";
    echo "<div class='product-grid'>";

    while ($row = $result->fetch_assoc()) {
        $id = (int)$row['id'];
        $title = htmlspecialchars($row['title']);
        $desc = htmlspecialchars($row['description']);
        $price = number_format($row['price'], 2);
        $category = htmlspecialchars($row['category']);
        $seller = htmlspecialchars($row['username']);
        $imageFilename = htmlspecialchars($row['image']);
        $imagePath = 'uploads/' . $imageFilename;
        $serverImagePath = __DIR__ . '/uploads/' . $imageFilename;

        $imageTag = (!empty($imageFilename) && file_exists($serverImagePath))
            ? "<img src=\"$imagePath\" alt=\"$title\">"
            : "<div style='height:150px;background:#eee;line-height:150px;border-radius:8px;'>No Image</div>";

        echo "
        <div class='product'>
            $imageTag
            <h3>$title</h3>
            <p>$desc</p>
            <p><strong>Price:</strong> ₹ $price</p>
            <p><strong>Category:</strong> $category</p>
            <p><strong>Seller:</strong> $seller</p>
            <a href='edit_product.php?id=$id' class='btn' style='background-color:#f39c12;'>Edit</a>
            <a href='delete_product.php?id=$id' class='btn' style='background-color:#e74c3c;'>Delete</a>
        </div>";
    }

    echo "</div>";
} else {
    echo "<p style='padding-left:20px;'>No products found. <a href='add_product.php'>Add one now</a>.</p>";
}
?>
</main>

<!-- Styles -->
<style>
.product-grid {
    display: flex;
    flex-wrap: wrap;
    gap: 20px;
    padding: 20px;
}
.product {
    border: 1px solid #ddd;
    border-radius: 10px;
    padding: 15px;
    width: 220px;
    text-align: center;
    background: #fff;
    box-shadow: 0 2px 5px rgba(0,0,0,0.1);
}
.product img {
    max-width: 100%;
    height: auto;
    border-radius: 8px;
    margin-bottom: 10px;
}
.btn {
    display: inline-block;
    margin: 5px 0;
    padding: 6px 12px;
    color: white;
    text-decoration: none;
    border-radius: 5px;
}
.welcome-box {
    padding: 15px 12px;
}
</style>

<?php include 'footer.php'; ?>
