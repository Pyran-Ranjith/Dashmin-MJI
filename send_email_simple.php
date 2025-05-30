<?php
// Simple Email Sender - No Database Required
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Configure these values
$sender_email = 'ranjithimas@gmail.com';     // Your Gmail address From
// $app_password = 'your_app_password';  // 16-digit App Password
$recipient = 'ranjithimas@gmail.com';   // Where to send
$subject = 'Test from PHP Mailer';
$message = 'Hello! This email was sent using PHP and Gmail SMTP.';

// Prepare email headers
$headers = [
    'From' => $sender_email,
    'Reply-To' => $sender_email,
    'X-Mailer' => 'PHP/' . phpversion(),
    'MIME-Version' => '1.0',
    'Content-type' => 'text/html; charset=utf-8'
];

// Flatten headers
$headers_string = '';
foreach ($headers as $key => $value) {
    $headers_string .= "$key: $value\r\n";
}

// Configure mail settings (works in XAMPP/WAMP)
ini_set('SMTP', 'smtp.gmail.com');
ini_set('smtp_port', 587);
ini_set('sendmail_from', $sender_email);

// Send the email
$mail_sent = mail($recipient, $subject, $message, $headers_string);

// Display result
echo $mail_sent 
    ? "<h2>Email Sent Successfully!</h2>"
    : "<h2>Failed to Send Email</h2><p>Error: " . print_r(error_get_last(), true) . "</p>";

// For debugging (check these locations if mail fails)
echo "<h3>Debug Info:</h3>";
echo "<p>If using XAMPP, check <code>mailoutput</code> folder for .eml files</p>";
echo "<p>PHP Mail Settings: " . ini_get('SMTP') . ":" . ini_get('smtp_port') . "</p>";
?>