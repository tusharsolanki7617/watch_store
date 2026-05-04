<?php
/**
 * Email Mailer Functions
 * Watch Store E-Commerce Website
 * 
 * Note: This implementation uses PHP's mail() function for simplicity.
 * For production, integrate PHPMailer library for SMTP support.
 */

require_once __DIR__ . '/PHPMailer/Exception.php';
require_once __DIR__ . '/PHPMailer/PHPMailer.php';
require_once __DIR__ . '/PHPMailer/SMTP.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
use PHPMailer\PHPMailer\SMTP as PHPMailerSMTP;

/**
 * Send email using SMTP or PHP mail()
 */
function sendEmail($to, $subject, $body, $isHTML = true) {
    $status = false;
    
    // Try PHPMailer if SMTP enabled
    if (defined('USE_SMTP') && USE_SMTP) {
        $mail = new PHPMailer(true);
        try {
            // Server settings
            $mail->isSMTP();
            $mail->Host       = SMTP_HOST;
            $mail->SMTPAuth   = true;
            $mail->Username   = SMTP_USERNAME;
            $mail->Password   = SMTP_PASSWORD;
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS; 
            $mail->Port       = 587; // STARTTLS
            $mail->Timeout    = 20;

            // Recipients
            $mail->setFrom(SMTP_FROM_EMAIL, SMTP_FROM_NAME);
            $mail->addAddress($to);

            // Content
            $mail->isHTML($isHTML);
            $mail->Subject = $subject;
            $mail->Body    = $body;
            $mail->AltBody = strip_tags($body);

            $status = $mail->send();
        } catch (Exception $e) {
            error_log("PHPMailer SMTP Error: " . $mail->ErrorInfo);
        }
    }
    
    // Fallback to mail() if SMTP disabled or failed
    if (!$status) {
        $headers = "MIME-Version: 1.0\r\n";
        if ($isHTML) {
            $headers .= "Content-Type: text/html; charset=UTF-8\r\n";
        } else {
            $headers .= "Content-Type: text/plain; charset=UTF-8\r\n";
        }
        $headers .= "From: =?UTF-8?B?" . base64_encode(SMTP_FROM_NAME) . "?= <" . SMTP_FROM_EMAIL . ">\r\n";
        $headers .= "Date: " . date("r") . "\r\n";
        $headers .= "X-Mailer: PHP/" . phpversion() . "\r\n";
        
        $status = @mail($to, $subject, $body, $headers);
    }
    
    return $status;
}

/**
 * Send account activation email
 */
function sendActivationEmail($email, $name, $token) {
    $activationLink = SITE_URL . "/activate.php?token=" . $token;
    
    $subject = "Activate Your Account - " . SITE_NAME;
    
    $body = "
    <html>
    <head>
        <style>
            body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
            .container { max-width: 600px; margin: 0 auto; padding: 20px; }
            .header { background: #2c3e50; color: #fff; padding: 20px; text-align: center; }
            .content { padding: 30px; background: #f9f9f9; }
            .button { display: inline-block; padding: 12px 30px; background: #3498db; color: #fff; text-decoration: none; border-radius: 5px; margin: 20px 0; }
            .footer { padding: 20px; text-align: center; font-size: 12px; color: #777; }
        </style>
    </head>
    <body>
        <div class='container'>
            <div class='header'>
                <h1>" . SITE_NAME . "</h1>
            </div>
            <div class='content'>
                <h2>Welcome, " . htmlspecialchars($name) . "!</h2>
                <p>Thank you for registering with " . SITE_NAME . ". Please activate your account by clicking the button below:</p>
                <p style='text-align: center;'>
                    <a href='" . $activationLink . "' class='button'>Activate Account</a>
                </p>
                <p>Or copy and paste this link into your browser:</p>
                <p style='word-break: break-all;'>" . $activationLink . "</p>
                <p>This link will expire in 24 hours.</p>
            </div>
            <div class='footer'>
                <p>&copy; " . date('Y') . " " . SITE_NAME . ". All rights reserved.</p>
            </div>
        </div>
    </body>
    </html>
    ";
    
    return sendEmail($email, $subject, $body);
}

/**
 * Send password reset OTP email
 */
function sendPasswordResetEmail($email, $name, $otp) {
    $subject = "Password Reset OTP - " . SITE_NAME;
    
    $body = "
    <html>
    <head>
        <style>
            body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
            .container { max-width: 600px; margin: 0 auto; padding: 20px; }
            .header { background: #2c3e50; color: #fff; padding: 20px; text-align: center; }
            .content { padding: 30px; background: #f9f9f9; }
            .otp-box { background: #fff; border: 2px dashed #3498db; padding: 20px; text-align: center; font-size: 32px; font-weight: bold; letter-spacing: 5px; margin: 20px 0; }
            .footer { padding: 20px; text-align: center; font-size: 12px; color: #777; }
        </style>
    </head>
    <body>
        <div class='container'>
            <div class='header'>
                <h1>" . SITE_NAME . "</h1>
            </div>
            <div class='content'>
                <h2>Password Reset Request</h2>
                <p>Hello " . htmlspecialchars($name) . ",</p>
                <p>We received a request to reset your password. Use the OTP below to proceed:</p>
                <div class='otp-box'>" . $otp . "</div>
                <p>This OTP will expire in 15 minutes.</p>
                <p>If you didn't request this, please ignore this email.</p>
            </div>
            <div class='footer'>
                <p>&copy; " . date('Y') . " " . SITE_NAME . ". All rights reserved.</p>
            </div>
        </div>
    </body>
    </html>
    ";
    
    return sendEmail($email, $subject, $body);
}

/**
 * Send order confirmation email
 */
function sendOrderConfirmationEmail($email, $name, $orderNumber, $orderDetails) {
    $subject = "Order Confirmation #" . $orderNumber . " - " . SITE_NAME;
    
    $itemsHtml = '';
    foreach ($orderDetails['items'] as $item) {
        $itemsHtml .= "
        <tr>
            <td style='padding: 10px; border-bottom: 1px solid #eee;'>" . htmlspecialchars($item['name']) . "</td>
            <td style='padding: 10px; border-bottom: 1px solid #eee; text-align: center;'>" . $item['quantity'] . "</td>
            <td style='padding: 10px; border-bottom: 1px solid #eee; text-align: right;'>" . formatPrice($item['price']) . "</td>
            <td style='padding: 10px; border-bottom: 1px solid #eee; text-align: right;'>" . formatPrice($item['total']) . "</td>
        </tr>
        ";
    }
    
    $body = "
    <html>
    <head>
        <style>
            body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
            .container { max-width: 600px; margin: 0 auto; padding: 20px; }
            .header { background: #27ae60; color: #fff; padding: 20px; text-align: center; }
            .content { padding: 30px; background: #f9f9f9; }
            table { width: 100%; border-collapse: collapse; margin: 20px 0; background: #fff; }
            .total-row { font-weight: bold; background: #f0f0f0; }
            .footer { padding: 20px; text-align: center; font-size: 12px; color: #777; }
        </style>
    </head>
    <body>
        <div class='container'>
            <div class='header'>
                <h1>Order Confirmed!</h1>
            </div>
            <div class='content'>
                <h2>Thank you for your order, " . htmlspecialchars($name) . "!</h2>
                <p>Your order <strong>#" . $orderNumber . "</strong> has been received and is being processed.</p>
                
                <h3>Order Details:</h3>
                <table>
                    <thead>
                        <tr style='background: #34495e; color: #fff;'>
                            <th style='padding: 10px; text-align: left;'>Product</th>
                            <th style='padding: 10px; text-align: center;'>Quantity</th>
                            <th style='padding: 10px; text-align: right;'>Price</th>
                            <th style='padding: 10px; text-align: right;'>Total</th>
                        </tr>
                    </thead>
                    <tbody>
                        " . $itemsHtml . "
                        <tr class='total-row'>
                            <td colspan='3' style='padding: 10px; text-align: right;'>Subtotal:</td>
                            <td style='padding: 10px; text-align: right;'>" . formatPrice($orderDetails['subtotal']) . "</td>
                        </tr>
                        " . ($orderDetails['discount'] > 0 ? "
                        <tr class='total-row'>
                            <td colspan='3' style='padding: 10px; text-align: right;'>Discount:</td>
                            <td style='padding: 10px; text-align: right; color: #27ae60;'>-" . formatPrice($orderDetails['discount']) . "</td>
                        </tr>
                        " : "") . "
                        <tr class='total-row' style='font-size: 18px;'>
                            <td colspan='3' style='padding: 10px; text-align: right;'>Total:</td>
                            <td style='padding: 10px; text-align: right;'>" . formatPrice($orderDetails['total']) . "</td>
                        </tr>
                    </tbody>
                </table>
                
                <p><strong>Payment Method:</strong> " . $orderDetails['payment_method'] . "</p>
                <p><strong>Shipping Address:</strong><br>" . nl2br(htmlspecialchars($orderDetails['shipping_address'])) . "</p>
                
                <p>We'll send you another email when your order ships.</p>
            </div>
            <div class='footer'>
                <p>&copy; " . date('Y') . " " . SITE_NAME . ". All rights reserved.</p>
            </div>
        </div>
    </body>
    </html>
    ";
    
    return sendEmail($email, $subject, $body);
}

/**
 * Send contact form notification to admin
 */
function sendContactNotificationEmail($contactData) {
    $subject = "New Contact Form Submission - " . SITE_NAME;
    
    $body = "
    <html>
    <head>
        <style>
            body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
            .container { max-width: 600px; margin: 0 auto; padding: 20px; }
            .header { background: #34495e; color: #fff; padding: 20px; text-align: center; }
            .content { padding: 30px; background: #f9f9f9; }
            .info-box { background: #fff; padding: 15px; margin: 10px 0; border-left: 4px solid #3498db; }
            .footer { padding: 20px; text-align: center; font-size: 12px; color: #777; }
        </style>
    </head>
    <body>
        <div class='container'>
            <div class='header'>
                <h1>New Contact Inquiry</h1>
            </div>
            <div class='content'>
                <div class='info-box'>
                    <strong>Name:</strong> " . htmlspecialchars($contactData['name']) . "
                </div>
                <div class='info-box'>
                    <strong>Email:</strong> " . htmlspecialchars($contactData['email']) . "
                </div>
                <div class='info-box'>
                    <strong>Subject:</strong> " . htmlspecialchars($contactData['subject']) . "
                </div>
                <div class='info-box'>
                    <strong>Message:</strong><br>" . nl2br(htmlspecialchars($contactData['message'])) . "
                </div>
                <p><small>Submitted on: " . date('F d, Y H:i:s') . "</small></p>
            </div>
            <div class='footer'>
                <p>&copy; " . date('Y') . " " . SITE_NAME . ". All rights reserved.</p>
            </div>
        </div>
    </body>
    </html>
    ";
    
    return sendEmail(SITE_EMAIL, $subject, $body);
}
/**
 * Send order status update email
 */
function sendOrderStatusUpdateEmail($email, $name, $orderNumber, $status) {
    $subject = "Order #$orderNumber Status Update - " . SITE_NAME;
    
    $body = "
    <html>
    <head>
        <style>
            body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
            .container { max-width: 600px; margin: 0 auto; padding: 20px; }
            .header { background: #34495e; color: #fff; padding: 20px; text-align: center; }
            .content { padding: 30px; background: #f9f9f9; }
            .status-badge { display: inline-block; padding: 10px 20px; background: #3498db; color: #fff; border-radius: 20px; font-weight: bold; margin: 15px 0; }
            .footer { padding: 20px; text-align: center; font-size: 12px; color: #777; }
        </style>
    </head>
    <body>
        <div class='container'>
            <div class='header'>
                <h1>Order Status Update</h1>
            </div>
            <div class='content'>
                <h2>Hello, " . htmlspecialchars($name) . "!</h2>
                <p>The status of your order <strong>#$orderNumber</strong> has been updated to:</p>
                <div class='status-badge'>$status</div>
                <p>You can view your order details by logging into your account.</p>
                <p>If you have any questions, please reply to this email.</p>
            </div>
            <div class='footer'>
                <p>&copy; " . date('Y') . " " . SITE_NAME . ". All rights reserved.</p>
            </div>
        </div>
    </body>
    </html>
    ";
    
    return sendEmail($email, $subject, $body);
}
?>
