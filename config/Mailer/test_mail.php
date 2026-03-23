<?php
require 'sendmail.php';

$message = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $to = trim($_POST['email']);
    $subject = trim($_POST['subject']);
    $body = trim($_POST['message']);

    $result = sendMail($to, $subject, nl2br($body));

    if ($result['success']) {
        $message = '<span style="color:green;">Email sent successfully!</span>';
    } else {
        $message = '<span style="color:red;">Error: ' . htmlspecialchars($result['message']) . '</span>';
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>PHP Mail Test</title>
    <style>
        body { font-family: Arial, sans-serif; background: #f4f4f4; padding: 30px; }
        form { background: #fff; padding: 20px; border-radius: 8px; max-width: 500px; margin: auto; box-shadow: 0 0 10px rgba(0,0,0,0.1); }
        input, textarea { box-sizing: border-box;width: 100%; padding: 10px; margin-bottom: 15px; border-radius: 4px; border: 1px solid #ccc; }
        button { padding: 10px 20px; background: #4CAF50; color: white; border: none; border-radius: 4px; cursor: pointer; }
        button:hover { background: #45a049; }
    </style>
</head>
<body>

<h2 style="text-align:center;">Test Mail Form</h2>



<form method="post">
    <label for="email">Recipient Email:</label>
    <input type="email" name="email" id="email" required placeholder="recipient@example.com">

    <label for="subject">Subject:</label>
    <input type="text" name="subject" id="subject" required placeholder="Email subject">

    <label for="message">Message:</label>
    <textarea name="message" id="message" rows="6" required placeholder="Write your message here..."></textarea>

    <button type="submit">Send Email</button>
    <!-- <?php echo $message; ?> -->
</form>

</body>
</html>