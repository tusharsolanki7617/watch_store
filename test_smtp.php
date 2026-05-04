<?php
require_once 'includes/config.php';
require_once 'includes/mailer.php';

echo "Testing SMTP Connection...\n";
echo "Host: " . SMTP_HOST . "\n";
echo "Port: " . SMTP_PORT . "\n";
echo "User: " . SMTP_USERNAME . "\n";

$to = SMTP_USERNAME; // Send to self
$subject = "SMTP Test " . date('Y-m-d H:i:s');
$body = "This is a test email to verify SMTP configuration.";

if (sendEmail($to, $subject, $body)) {
    echo "SUCCESS: Email sent successfully!\n";
} else {
    echo "FAILURE: Could not send email. Check error logs.\n";
}
?>
