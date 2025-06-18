<?php
session_start();
include 'db.php';

// Supported languages
$supported_langs = ['en', 'hi', 'mr', 'kn'];

// Detect language change via GET param
if (isset($_GET['lang']) && in_array($_GET['lang'], $supported_langs)) {
    $_SESSION['lang'] = $_GET['lang'];
}

// Set current language or fallback to English
$lang = $_SESSION['lang'] ?? 'en';

// Load language file
$lang_file = __DIR__ . "/lang/{$lang}.php";
if (file_exists($lang_file)) {
    $t = include $lang_file;
} else {
    $t = include __DIR__ . "/lang/en.php";
}

include 'header.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['user_id'];
$stmt = $conn->prepare("SELECT username FROM users WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$stmt->bind_result($username);
$stmt->fetch();
$stmt->close();

$_SESSION['username'] = $username;
?>

<link rel="stylesheet" href="style.css">

<nav class="navbar" style="display: flex; justify-content: space-between; align-items: center; background: #1f2937; padding: 10px 20px; color: white;">
  <div style="width: 80px;"></div>
  <span style="color:#4CAF50; font-weight:bold; margin-right: 15px;">
      <?= htmlspecialchars($t['welcome']) ?>, <?= htmlspecialchars($username) ?>!
  </span>
  <div style="text-align: center; flex-grow: 1;">
    <a href="index.php" class="logo" style="color: white; font-size: 20px; font-weight: bold; text-decoration: none;">MyShop</a>
  </div>
  <a href="logout.php" class="btn-login" style="background:#e74c3c;"><?= htmlspecialchars($t['logout']) ?></a>
  <div style="display: flex; align-items: center; justify-content: flex-end; gap: 15px; width: 80px;">
    <a href="cart.php" class="cart-icon" style="color: white; text-decoration: none;">
      🛒 <span class="cart-count" id="cart-count"><?= $_SESSION['cart_count'] ?? 0 ?></span>
    </a>
  </div>
</nav>

<!-- Filter/Search/Sort Form -->
<form method="get" style="margin: 20px;">
  <label for="category"><?= htmlspecialchars($t['category']) ?>:</label>
  <select name="category" id="category">
    <option value=""><?= htmlspecialchars($t['all']) ?></option>
    <option value="Electronics" <?= ($_GET['category'] ?? '') === 'Electronics' ? 'selected' : '' ?>><?= htmlspecialchars($t['electronics']) ?></option>
    <option value="Furniture" <?= ($_GET['category'] ?? '') === 'Furniture' ? 'selected' : '' ?>><?= htmlspecialchars($t['furniture']) ?></option>
    <option value="Vehicles" <?= ($_GET['category'] ?? '') === 'Vehicles' ? 'selected' : '' ?>><?= htmlspecialchars($t['vehicles']) ?></option>
    <option value="Fashion" <?= ($_GET['category'] ?? '') === 'Fashion' ? 'selected' : '' ?>><?= htmlspecialchars($t['fashion']) ?></option>
    <option value="Books" <?= ($_GET['category'] ?? '') === 'Books' ? 'selected' : '' ?>><?= htmlspecialchars($t['books']) ?></option>
  </select>

  <label for="search"><?= htmlspecialchars($t['search']) ?>:</label>
  <input type="text" name="search" id="search" placeholder="<?= htmlspecialchars($t['search_placeholder']) ?>" value="<?= htmlspecialchars($_GET['search'] ?? '') ?>">

  <label for="sort"><?= htmlspecialchars($t['sort_by']) ?>:</label>
  <select name="sort" id="sort">
    <option value=""><?= htmlspecialchars($t['default']) ?></option>
    <option value="price_asc" <?= ($_GET['sort'] ?? '') === 'price_asc' ? 'selected' : '' ?>><?= htmlspecialchars($t['price_low_high']) ?></option>
    <option value="price_desc" <?= ($_GET['sort'] ?? '') === 'price_desc' ? 'selected' : '' ?>><?= htmlspecialchars($t['price_high_low']) ?></option>
  </select>

  <button type="submit" class="btn" style="padding: 5px 15px;"><?= htmlspecialchars($t['apply']) ?></button>
</form>

<?php
$category = $_GET['category'] ?? '';
$search = $_GET['search'] ?? '';
$sort = $_GET['sort'] ?? '';

$sql = "SELECT products.*, users.username AS seller_username 
        FROM products 
        JOIN users ON products.user_id = users.id 
        WHERE 1";
$params = [];
$types = '';

if ($category !== '') {
    $sql .= " AND products.category = ?";
    $params[] = $category;
    $types .= 's';
}

if ($search !== '') {
    $sql .= " AND (products.title LIKE ? OR products.description LIKE ?)";
    $like = '%' . $search . '%';
    $params[] = $like;
    $params[] = $like;
    $types .= 'ss';
}

if ($sort === 'price_asc') {
    $sql .= " ORDER BY products.price ASC";
} elseif ($sort === 'price_desc') {
    $sql .= " ORDER BY products.price DESC";
}

$stmt = $conn->prepare($sql);
if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$result = $stmt->get_result();

if ($result && $result->num_rows > 0) {
    echo "<h2>" . htmlspecialchars($t['available_products']) . "</h2>";
    echo "<div class='product-grid'>";
    while ($row = $result->fetch_assoc()) {
  
        $id       = (int)$row['id'];
        $title    = htmlspecialchars($row['title']);
        $desc     = htmlspecialchars($row['description']);
        $price    = number_format($row['price'], 2);
        $imagePath = htmlspecialchars($row['image']);
        $imageSrc = (strpos($imagePath, 'uploads/') === 0) ? $imagePath : 'uploads/' . $imagePath;
        $seller   = htmlspecialchars($row['seller_username']);
        $usd_rate = 83.20;
        $price_usd = number_format($row['price'] / $usd_rate, 2);

        echo "
        <div class='product'>
            <a href='product_details.php?id=$id' class='product-link'>
                <img src='$imageSrc' alt='$title'>
                <h3>$title</h3>
                <p>$desc</p>
                <p>₹ $price INR</p>
                <p>\$ $price_usd USD</p>
                <p><strong>" . htmlspecialchars($t['seller']) . ":</strong> $seller</p>
            </a>
            <div class='product-buttons'>
                <a href='cart_add.php?id=$id' class='btn'>" . htmlspecialchars($t['add_to_cart']) . "</a>
                <a href='buy_now.php?id=$id' class='btn buy-now-btn'>" . htmlspecialchars($t['buy_now']) . "</a>
            </div>
        </div>";
    }
} else {
    echo "<p>" . htmlspecialchars($t['no_products']) . "</p>";
}
?>

<?php include 'footer.php'; ?>

<!-- Sidebar + Toggle Script -->
<script>
  function toggleSidebar() {
    document.getElementById('sidebar').classList.toggle('active');
    document.getElementById('overlay').classList.toggle('active');
  }
</script>

<!-- Sidebar -->
<!-- Sidebar -->
<div class="sidebar" id="sidebar">
  <a href="index.php">Home</a>
  <a href="about.php">About</a>
  <a href="contact.php">Contact</a>
  <a href="products.php">Products</a>
  <a href="add_product.php">➕ Add New Product</a>
  <a href="manage_products.php">🛠️ Manage Products</a>

  <!-- Language Switcher -->
  <form method="get" style="margin-top: 30px; color: white;">
    <label for="lang" style="display:block; margin-bottom: 8px; font-weight: bold;">Language:</label>
    <select name="lang" id="lang" onchange="this.form.submit()" style="width: 100%; padding: 5px; border-radius: 4px; border: none; font-size: 16px;">
      <option value="en" <?= ($lang === 'en') ? 'selected' : '' ?>>English</option>
      <option value="hi" <?= ($lang === 'hi') ? 'selected' : '' ?>>हिन्दी</option>
      <option value="mr" <?= ($lang === 'mr') ? 'selected' : '' ?>>मराठी</option>
      <option value="kn" <?= ($lang === 'kn') ? 'selected' : '' ?>>ಕನ್ನಡ</option>
    </select>
  </form>
</div>


<div class="overlay" id="overlay" onclick="toggleSidebar()"></div>

<!-- Optional Fixed Header -->
<header style="background:rgb(67, 117, 225); color: white; padding: 10px 20px; position: fixed; top: 0; left: 0; width: 100%; z-index: 999;">
  <div style="font-size: 20px; font-weight: bold;">myshop</div>
</header>

<!-- Menu Toggle Button -->
<button class="menu-button" onclick="toggleSidebar()">
  <span></span>
  <span></span>
  <span></span>
</button>

<style>
  .product-grid {
    display: flex;
    flex-wrap: wrap;
    gap: 20px;
    padding: 20px;
    justify-content: center;
  }
  .product {
    border: 1px solid #ddd;
    border-radius: 10px;
    padding: 15px;
    width: 220px;
    min-height: 420px;
    background: #fff;
    display: flex;
    flex-direction: column;
    text-align: center;
    position: relative;
  }
  .product img {
    width: 100%;
    height: auto;
    max-height: 200px;
    object-fit: contain;
    border-radius: 5px;
  }
  .product h3 {
    font-size: 16px;
    margin: 8px 0;
    height: 40px;
    overflow: hidden;
  }
  .product p {
    font-size: 14px;
    margin: 4px 0;
    height: 32px;
    overflow: hidden;
  }
  .btn, .buy-now-btn {
    display: inline-block;
    margin: 5px 3px;
    padding: 6px 12px;
    font-size: 14px;
    background: #2ecc71;
    color: white;
    border: none;
    border-radius: 4px;
    text-decoration: none;
  }
  .product-buttons {
    margin-top: auto;
    padding-top: 10px;
  }
  .product-link {
    text-decoration: none;
    color: inherit;
    flex-grow: 1;
  }
  @media (max-width: 600px) {
    .product-grid {
      flex-direction: column;
      align-items: center;
    }
  }

  .menu-button {
    position: fixed;
    bottom: 20px;
    left: 20px;
    top: 13px;
    width: 50px;
    height: 40px;
    display: flex;
    flex-direction: column;
    justify-content: space-around;
    align-items: center;
    padding: 8px;
    background: rgb(41, 112, 211);
    border: none;
    border-radius: 10px;
    cursor: pointer;
    z-index: 1000;
    box-shadow: 0 2px 10px rgba(0,0,0,0.2);
  }
  .menu-button span {
    display: block;
    width: 70%;
    height: 4px;
    background: white;
    border-radius: 2px;
  }

  .sidebar {
    position: fixed;
    top: 20px;
    left: -250px;
    width: 250px;
    height: 100%;
    background-color: rgb(75, 136, 221);
    color: white;
    padding: 20px;
    box-shadow: 2px 0 10px rgba(0,0,0,0.3);
    transition: left 0.3s ease;
    z-index: 998;
  }
  .sidebar.active {
    left: 0;
  }
  .sidebar a {
    display: block;
    margin: 15px 0;
    color: white;
    text-decoration: none;
    font-weight: bold;
  }

  .overlay {
    display: none;
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(189, 141, 141, 0.5);
    z-index: 997;
  }
  .overlay.active {
    display: block;
  }
</style>



