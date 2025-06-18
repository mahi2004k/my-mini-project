<?php
include 'header.php';

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    include 'db.php';

    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $pass = $_POST['password'];

    // Basic validation
    if (empty($username)) {
        $error = "Username is required.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Invalid email format.";
    } else {
        $hashed_pass = password_hash($pass, PASSWORD_DEFAULT);

        // Prepare statement to prevent SQL injection
        $stmt = $conn->prepare("INSERT INTO users (username, email, password) VALUES (?, ?, ?)");
        $stmt->bind_param("sss", $username, $email, $hashed_pass);

        if ($stmt->execute()) {
            header("Location: login.php");
            exit;
        } else {
            $error = "Registration failed. Email may already be in use.";
        }
    }
}
?>

<div class="auth-form">
    <h2>Register</h2>

    <?php if (!empty($error)): ?>
        <p class="error"><?= htmlspecialchars($error) ?></p>
    <?php endif; ?>

    <form action="" method="POST">
        <label for="username">Username</label>
        <input type="text" name="username" id="username" placeholder="Choose a username" required value="<?= isset($username) ? htmlspecialchars($username) : '' ?>">

        <label for="email">Email</label>
        <input type="email" name="email" id="email" placeholder="Enter your email" required value="<?= isset($email) ? htmlspecialchars($email) : '' ?>">

        <label for="password">Password</label>
        <input type="password" name="password" id="password" placeholder="Create a password" required>

        <input type="submit" value="Register" class="btn">
    </form>

    <a href="login.php">Already have an account? Login</a>
</div>
