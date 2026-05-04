<?php
require_once 'includes/config.php';

// Increase timeout for image downloads
set_time_limit(300);

echo "<h1>⌚ Watch Store Image Seeder (Watch-Only)</h1>";
echo "<div style='font-family: Arial; line-height: 1.6;'>";

// Ensure directory exists
if (!file_exists(PRODUCT_IMAGE_PATH)) {
    mkdir(PRODUCT_IMAGE_PATH, 0777, true);
    echo "✅ Created directory: " . PRODUCT_IMAGE_PATH . "<br>";
}

// Clear existing images to ensure "watch only" policy
$conn->exec("TRUNCATE TABLE product_images");
echo "🧹 Cleared existing product images table<br>";

// Curated list of verified watch-only Unsplash IDs
$images = [
    // Men's Watches (Category 1)
    1 => [
        'https://images.unsplash.com/photo-1524592094714-0f0654e20314?w=800&q=80',
        'https://images.unsplash.com/photo-1522312346375-d1a52e2b99b3?w=800&q=80',
        'https://images.unsplash.com/photo-1523170335258-f5ed11844a49?w=800&q=80',
        'https://images.unsplash.com/photo-1614164185128-e4ec99c436d7?w=800&q=80'
    ],
    // Women's Watches (Category 2)
    2 => [
        'https://images.unsplash.com/photo-1542496658-e33a6d0d50f6?w=800&q=80',
        'https://images.unsplash.com/photo-1595950653106-6c9ebd614d3a?w=800&q=80',
        'https://images.unsplash.com/photo-1508685096489-7aacd43bd3b1?w=800&q=80',
        'https://images.unsplash.com/photo-1616348436168-de43ad0db179?w=800&q=80'
    ],
    // Smart Watches (Category 3)
    3 => [
        'https://images.unsplash.com/photo-1579586337278-3befd40fd17a?w=800&q=80',
        'https://images.unsplash.com/photo-1517502884422-41eaead166d4?w=800&q=80',
        'https://images.unsplash.com/photo-1510017803434-a899398421b3?w=800&q=80'
    ],
    // Luxury Watches (Category 4)
    4 => [
        'https://images.unsplash.com/photo-1547996160-81dfa63595aa?w=800&q=80',
        'https://images.unsplash.com/photo-1585123334904-845d60e97b29?w=800&q=80',
        'https://images.unsplash.com/photo-1548171915-e7c5d3badff7?w=800&q=80'
    ],
    // Sports Watches (Category 5) 
    5 => [
        'https://images.unsplash.com/photo-1523275335684-37898b6baf30?w=800&q=80',
        'https://images.unsplash.com/photo-1539185441755-769473a23570?w=800&q=80',
        'https://images.unsplash.com/photo-1461896704690-464a71dd3ad9?w=800&q=80'
    ]
];

// Get all products
$stmt = $conn->query("SELECT id, name, category_id FROM products");
$products = $stmt->fetchAll();

echo "<h3>📸 Downloading and Assigning Watch-Only Images...</h3>";

$downloadCount = 0;

foreach ($products as $product) {
    $categoryId = $product['category_id'];
    $categoryImages = $images[$categoryId] ?? $images[1];
    
    // Shuffle to get different images for the gallery
    shuffle($categoryImages);
    
    // Take up to 3 images per product for the gallery
    $toAssign = array_slice($categoryImages, 0, 3);
    
    foreach ($toAssign as $i => $imageUrl) {
        // Add random query param to ensure distinct filenames/caching avoidance
        $urlWithParam = $imageUrl . '&sig=' . uniqid();
        
        // Generate unique filename
        $filename = 'watch_' . $product['id'] . '_' . $i . '_' . uniqid() . '.jpg';
        $filepath = PRODUCT_IMAGE_PATH . '/' . $filename;
        
        // Download image
        try {
            $imageData = file_get_contents($urlWithParam);
            if ($imageData !== false) {
                file_put_contents($filepath, $imageData);
                
                // First image is primary
                $isPrimary = ($i == 0) ? 1 : 0;
                
                // Insert into database
                $stmt = $conn->prepare("INSERT INTO product_images (product_id, image_path, is_primary, display_order) VALUES (?, ?, ?, ?)");
                $stmt->execute([$product['id'], $filename, $isPrimary, $i]);
                
                if ($i == 0) echo "⌚ Assigned primary watch photo for: <b>" . $product['name'] . "</b><br>";
                $downloadCount++;
            }
        } catch (Exception $e) {
            echo "⚠️ Error downloading for " . $product['name'] . ": " . $e->getMessage() . "<br>";
        }
        
        // Small pause
        usleep(50000);
    }
}

echo "<hr>";
echo "<h3>🎉 Completed!</h3>";
echo "<p>Successfully assigned $downloadCount watch-specific photos to your catalog.</p>";
echo "<a href='products.php' style='background: #3498db; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px; font-weight: bold;'>View Store</a>";
echo "</div>";
?>
