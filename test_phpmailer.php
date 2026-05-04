<?php
require_once 'includes/config.php';
require_once 'includes/mailer.php';

// Force debug output for this test
function sendEmailWithDebug($to, $subject, $body, $isHTML = true) {
    echo "Starting PHPMailer Debug Test...\n";
    
    $mail = new PHPMailer\PHPMailer\PHPMailer(true);
    try {
        // Server settings
        $mail->SMTPDebug  = PHPMailer\PHPMailer\SMTP::DEBUG_SERVER; 
        $mail->isSMTP();
        $mail->Host       = SMTP_HOST;
        $mail->SMTPAuth   = true;
        $mail->Username   = SMTP_USERNAME;
        $mail->Password   = SMTP_PASSWORD;
        $mail->SMTPSecure = PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port       = 587;
        $mail->Timeout    = 20;

        // Recipients
        $mail->setFrom(SMTP_FROM_EMAIL, SMTP_FROM_NAME);
        $mail->addAddress($to);

        // Content
        $mail->isHTML($isHTML);
        $mail->Subject = $subject;
        $mail->Body    = $body;

        if ($mail->send()) {
            echo "\nSUCCESS: Email sent successfully!\n";
            return true;
        }
    } catch (Exception $e) {
        echo "\nFAILURE: " . $mail->ErrorInfo . "\n";
        return false;
    }
}

$to = SMTP_USERNAME; 
$subject = "PHPMailer Test - " . date('Y-m-d H:i:s');
$body = "<h1>PHPMailer is working!</h1><p>This is a test email sent using PHPMailer library.</p>";

sendEmailWithDebug($to, $subject, $body);
?>
