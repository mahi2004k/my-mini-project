<?php
// In production, send this to email or store in DB.
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $name = htmlspecialchars(trim($_POST["name"]));
    $email = htmlspecialchars(trim($_POST["email"]));
    $message = htmlspecialchars(trim($_POST["message"]));

    // Example response
    echo "<h2>Thank you, $name! Your message has been received.</h2>";
    echo "<p>We will get back to you at <strong>$email</strong> as soon as possible.</p>";
} else {
    header("Location: contact.php");
    exit;
}
?>
