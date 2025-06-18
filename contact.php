<?php include 'header.php'; ?>
<main style="max-width: 800px; margin: 40px auto; padding: 0 20px;">
    <h1>Contact Us</h1>
    <p style="font-size: 16px; line-height: 1.6;">
        We'd love to hear from you! Whether you have a question, feedback, or need assistance, feel free to reach out.
    </p>
    
    <form action="send_contact.php" method="POST" style="margin-top: 20px;">
        <label for="name">Name:</label><br>
        <input type="text" id="name" name="name" required style="width: 100%; padding: 8px;"><br><br>

        <label for="email">Email:</label><br>
        <input type="email" id="email" name="email" required style="width: 100%; padding: 8px;"><br><br>

        <label for="message">Message:</label><br>
        <textarea id="message" name="message" rows="6" required style="width: 100%; padding: 8px;"></textarea><br><br>

        <button type="submit" style="background-color: #1f2937; color: white; padding: 10px 20px; border: none; cursor: pointer;">
            Send Message
        </button>
    </form>
</main>
<?php include 'footer.php'; ?>
