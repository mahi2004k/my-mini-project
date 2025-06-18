<?php
session_start();
require_once 'db.php'; // Database connection

// Check connection
if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

// Fetch products
$sql = "SELECT id, title, price, image, description FROM products ORDER BY id DESC";
$result = mysqli_query($conn, $sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Products - myshop</title>
  <style>
    body { font-family: Arial, sans-serif; margin: 0; padding-top: 60px; background: #f4f4f4; }
    h2 { text-align: center; margin-top: 20px; }
    .product-list {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
      gap: 20px;
      padding: 20px;
    }
    .product {
      border: 1px solid #ccc;
      border-radius: 8px;
      padding: 10px;
      text-align: center;
      background: #fff;
      box-shadow: 2px 2px 8px rgba(0,0,0,0.1);
    }
    .product img {
      max-width: 100%;
      height: 150px;
      object-fit: cover;
      border-radius: 5px;
    }
    .product h3 {
      margin: 10px 0 5px;
      font-size: 18px;
    }
    .product p {
      margin: 5px 0;
      font-size: 14px;
    }
    .product .price {
      font-weight: bold;
      color: green;
    }
    .product a {
      display: inline-block;
      margin-top: 10px;
      text-decoration: none;
      color: #3498db;
      font-weight: bold;
    }
    .product a:hover {
      text-decoration: underline;
    }
  </style>
</head>
<body>

<main>
  <h2>Available Products</h2>

  <div class="product-list">
    <?php if (mysqli_num_rows($result) > 0): ?>
      <?php while ($row = mysqli_fetch_assoc($result)): ?>
        <div class="product">
          <?php if (!empty($row['image']) && file_exists('uploads/' . $row['image'])): ?>
            <img src="uploads/<?php echo htmlspecialchars($row['image']); ?>" alt="Product Image">
          <?php else: ?>
            <img src="placeholder.png" alt="No Image Available"> <!-- fallback image -->
          <?php endif; ?>

          <h3><?php echo htmlspecialchars($row['title']); ?></h3>
          <p class="price">₹<?php echo number_format($row['price'], 2); ?></p>
          <p><?php echo htmlspecialchars($row['description']); ?></p>
          <a href="product_details.php?id=<?php echo (int)$row['id']; ?>">View Details</a>
        </div>
      <?php endwhile; ?>
    <?php else: ?>
      <p style="text-align:center;">No products found.</p>
    <?php endif; ?>
  </div>
</main>

</body>
</html>
