<?php
require_once 'includes/config.php';

echo "<h1>🎫 Coupon Seeder</h1>";
echo "<div style='font-family: Arial; line-height: 1.6;'>";

$coupons = [
    [
        'code' => 'WELCOME10',
        'description' => '10% discount for new users',
        'discount_type' => 'percentage',
        'discount_value' => 10,
        'min_order_amount' => 1000,
        'max_discount' => 500,
        'usage_limit' => 100,
        'is_active' => 1,
        'expiry_date' => date('Y-12-31')
    ],
    [
        'code' => 'WATCH500',
        'description' => 'Flat ₹500 off on orders above ₹5000',
        'discount_type' => 'fixed',
        'discount_value' => 500,
        'min_order_amount' => 5000,
        'max_discount' => null,
        'usage_limit' => 50,
        'is_active' => 1,
        'expiry_date' => date('Y-12-31')
    ]
];

foreach ($coupons as $coupon) {
    try {
        $stmt = $conn->prepare("SELECT COUNT(*) FROM coupons WHERE code = ?");
        $stmt->execute([$coupon['code']]);
        if ($stmt->fetchColumn() == 0) {
            $sql = "INSERT INTO coupons (code, description, discount_type, discount_value, min_order_amount, max_discount, usage_limit, is_active, expiry_date) 
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
            $stmt = $conn->prepare($sql);
            $stmt->execute([
                $coupon['code'], $coupon['description'], $coupon['discount_type'], 
                $coupon['discount_value'], $coupon['min_order_amount'], 
                $coupon['max_discount'], $coupon['usage_limit'], $coupon['is_active'],
                $coupon['expiry_date']
            ]);
            echo "✅ Created coupon: <b>{$coupon['code']}</b><br>";
        } else {
            echo "ℹ️ Coupon <b>{$coupon['code']}</b> already exists.<br>";
        }
    } catch (PDOException $e) {
        echo "❌ Error adding {$coupon['code']}: " . $e->getMessage() . "<br>";
    }
}

echo "<hr>🎉 Seeding completed!";
echo "<br><a href='admin/coupons.php' style='display:inline-block; margin-top:10px; padding:8px 15px; background:#3498db; color:white; text-decoration:none; border-radius:4px;'>Go to Coupon Management</a>";
echo "</div>";
?>
