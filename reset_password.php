<?php
include 'header.php';
include 'db.php';

$success = $error = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'];
    $newPassword = $_POST['new_password'];
    $confirmPassword = $_POST['confirm_password'];

    if ($newPassword !== $confirmPassword) {
        $error = "Passwords do not match.";
    } else {
        $stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result && $result->num_rows === 1) {
            $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
            $update = $conn->prepare("UPDATE users SET password = ? WHERE email = ?");
            $update->bind_param("ss", $hashedPassword, $email);
            $update->execute();
            $success = "Password has been updated. You can now <a href='login.php'>login</a>.";
        } else {
            $error = "Email not found.";
        }
    }
}
?>

<div class="auth-form">
    <h2>Reset Password</h2>

    <?php if (!empty($error)): ?>
        <p class="error"><?= htmlspecialchars($error) ?></p>
    <?php endif; ?>

    <?php if (!empty($success)): ?>
        <p class="success"><?= $success ?></p>
    <?php endif; ?>

    <form action="" method="POST">
        <label for="email">Your Email</label>
        <input type="email" name="email" id="email" required>

        <label for="new_password">New Password</label>
        <input type="password" name="new_password" id="new_password" required>

        <label for="confirm_password">Confirm Password</label>
        <input type="password" name="confirm_password" id="confirm_password" required>

        <input type="submit" value="Reset Password" class="btn">
    </form>
</div>
