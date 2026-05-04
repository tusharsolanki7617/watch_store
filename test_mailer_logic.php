<?php
require_once 'includes/config.php';
require_once 'includes/mailer.php';

echo "Testing Mailer Logic (USE_SMTP = " . (USE_SMTP ? 'true' : 'false') . ")\n";

$to = SMTP_USERNAME; 
$subject = "Mailer Logic Test " . date('Y-m-d H:i:s');
$body = "<h1>Test Successful</h1><p>This email was sent using the updated mailer logic.</p>";

if (sendEmail($to, $subject, $body)) {
    echo "SUCCESS: Email sent successfully via " . (USE_SMTP ? 'SMTP' : 'mail()') . "!\n";
} else {
    echo "FAILURE: Could not send email. Check error logs.\n";
}
?>
