<?php
require_once 'includes/config.php';
require_once 'includes/mailer.php';

// Ensure user is logged in
if (!isLoggedIn()) {
    redirect(SITE_URL . '/login.php');
}

$razorpayOrderId  = isset($_POST['razorpay_order_id'])  ? sanitizeInput($_POST['razorpay_order_id'])  : '';
$razorpayPaymentId = isset($_POST['razorpay_payment_id']) ? sanitizeInput($_POST['razorpay_payment_id']) : '';
$razorpaySignature = isset($_POST['razorpay_signature'])  ? sanitizeInput($_POST['razorpay_signature'])  : '';

if (empty($razorpayOrderId) || empty($razorpayPaymentId) || empty($razorpaySignature)) {
    setFlashMessage('danger', 'Invalid payment verification request.');
    redirect(SITE_URL . '/checkout.php');
}

// 1. Verify Signature
$expectedSignature = hash_hmac('sha256', $razorpayOrderId . '|' . $razorpayPaymentId, RAZORPAY_KEY_SECRET);

if ($expectedSignature === $razorpaySignature) {
    try {
        $conn->beginTransaction();
        
        // 2. Find Order
        $stmt = $conn->prepare("SELECT * FROM orders WHERE razorpay_order_id = ?");
        $stmt->execute([$razorpayOrderId]);
        $order = $stmt->fetch();
        
        if (!$order) {
            throw new Exception("Order not found in Nexus Archive.");
        }
        
        // 3. Update Order Status
        $stmt = $conn->prepare("UPDATE orders SET 
            payment_status = 'Completed', 
            razorpay_payment_id = ?, 
            razorpay_signature = ? 
            WHERE id = ?");
        $stmt->execute([$razorpayPaymentId, $razorpaySignature, $order['id']]);
        
        // 4. Clear Cart
        $stmt = $conn->prepare("DELETE FROM cart WHERE user_id = ?");
        $stmt->execute([$order['user_id']]);
        unset($_SESSION['cart']);
        unset($_SESSION['coupon_code']);
        unset($_SESSION['discount_amount']);
        unset($_SESSION['pending_razorpay']);
        
        // 5. Send Confirmation Email
        // Need to reconstruct $orderDetails for the mailer
        $stmt = $conn->prepare("SELECT * FROM order_items WHERE order_id = ?");
        $stmt->execute([$order['id']]);
        $items = $stmt->fetchAll();
        
        $orderDetails = [
            'items' => $items,
            'subtotal' => $order['subtotal'],
            'discount' => $order['discount'],
            'total' => $order['total'],
            'payment_method' => $order['payment_method'],
            'shipping_address' => $order['shipping_address']
        ];
        
        sendOrderConfirmationEmail($order['email'], $order['full_name'], $order['order_number'], $orderDetails);
        
        $conn->commit();
        
        setFlashMessage('success', 'Payment verified! Your order is being synchronized.');
        redirect(SITE_URL . "/order-success.php?id=" . $order['order_number']);
        
    } catch (Exception $e) {
        $conn->rollBack();
        setFlashMessage('danger', 'Synchronization failed: ' . $e->getMessage());
        redirect(SITE_URL . '/checkout.php');
    }
} else {
    setFlashMessage('danger', 'Nexus Signature Mismatch. Payment verification failed.');
    redirect(SITE_URL . '/checkout.php');
}
