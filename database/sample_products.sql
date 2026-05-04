-- Sample Products for Watch Store
-- Run this AFTER importing the main database.sql

USE watch_store;

-- Insert Sample Products
INSERT INTO products (category_id, name, slug, brand, model_number, description, price, discount_price, stock, movement_type, case_material, strap_material, water_resistance, warranty, features, is_featured, is_active) VALUES
(1, 'Classic Leather Watch', 'classic-leather-watch', 'Timex', 'TX-1001', 'Elegant leather strap watch perfect for formal occasions', 5999.00, 4999.00, 15, 'Quartz', 'Stainless Steel', 'Genuine Leather', '30m', '2 Years', 'Date Display\nScratch Resistant Glass\nWater Resistant', 1, 1),
(1, 'Sport Chronograph', 'sport-chronograph', 'Casio', 'CS-2002', 'Durable sports watch with chronograph functionality', 3499.00, 2999.00, 25, 'Quartz', 'Plastic Resin', 'Rubber', '100m', '1 Year', 'Stopwatch Function\nAlarm\nBacklight', 1, 1),
(2, 'Rose Gold Elegance', 'rose-gold-elegance', 'Fossil', 'FS-3003', 'Beautiful rose gold watch for women', 8999.00, 7499.00, 12, 'Quartz', 'Stainless Steel', 'Metal Bracelet', '50m', '2 Years', 'Rose Gold Plating\nElegant Design\nDate Window', 1, 1),
(3, 'Smart Fitness Tracker', 'smart-fitness-tracker', 'Samsung', 'SM-4004', 'Advanced smartwatch with fitness tracking', 12999.00, 11999.00, 20, 'Smart', 'Aluminum', 'Silicone', '50m', '1 Year', 'Heart Rate Monitor\nGPS Tracking\nNotifications\nFitness Apps', 1, 1),
(3, 'Apple SmartWatch Series', 'apple-smartwatch-series', 'Apple', 'AW-5005', 'Premium smartwatch with advanced features', 35999.00, 32999.00, 8, 'Smart', 'Aluminum', 'Sport Band', '50m', '1 Year', 'ECG App\nBlood Oxygen\nAlways-On Display\nCellular', 1, 1),
(4, 'Luxury Automatic Gold', 'luxury-automatic-gold', 'Rolex', 'RX-6006', 'Premium luxury automatic watch', 299999.00, NULL, 3, 'Automatic', '18K Gold', 'Gold Bracelet', '100m', '5 Years', 'Self-Winding\nSapphire Crystal\nPrecision Movement', 1, 1),
(5, 'Outdoor Adventure Pro', 'outdoor-adventure-pro', 'Garmin', 'GR-7007', 'Rugged sports watch for outdoor activities', 15999.00, 13999.00, 18, 'Quartz', 'Fiber-Reinforced Polymer', 'Silicone', '100m', '2 Years', 'GPS Navigation\nCompass\nBarometer\nShock Resistant', 1, 1),
(1, 'Business Professional', 'business-professional', 'Citizen', 'CT-8008', 'Professional watch for business meetings', 9999.00, 8499.00, 10, 'Automatic', 'Stainless Steel', 'Leather', '50m', '3 Years', 'Sapphire Crystal\nDate Display\nAutomatic Movement', 0, 1);

-- For demonstration: You would normally upload actual images through the admin panel
-- These are just placeholder entries
-- INSERT INTO product_images (product_id, image_path, is_primary, display_order) VALUES
-- (1, 'sample-watch-1.jpg', 1, 0),
-- (2, 'sample-watch-2.jpg', 1, 0);
-- etc.

SELECT 'Sample products inserted successfully!' as Status;
