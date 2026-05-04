-- MariaDB dump 10.19  Distrib 10.4.28-MariaDB, for osx10.10 (x86_64)
--
-- Host: localhost    Database: watch_store
-- ------------------------------------------------------
-- Server version	10.4.28-MariaDB

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Table structure for table `admins`
--

DROP TABLE IF EXISTS `admins`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `admins` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `username` varchar(50) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `full_name` varchar(100) NOT NULL,
  `profile_image` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `username` (`username`),
  UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `admins`
--

LOCK TABLES `admins` WRITE;
/*!40000 ALTER TABLE `admins` DISABLE KEYS */;
INSERT INTO `admins` VALUES (6,'admin','admin@watchstore.com','$2y$10$qr.Res1ncLEIYjaU4dtiWO3fOwXJkeUdfLxOFQQnr3di3gc4KZMim','System Administrator','admin_6_1775079577.jpeg','2026-02-15 12:53:40','2026-04-01 21:39:37');
/*!40000 ALTER TABLE `admins` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `cart`
--

DROP TABLE IF EXISTS `cart`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `cart` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `quantity` int(11) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_user_product` (`user_id`,`product_id`),
  KEY `product_id` (`product_id`),
  KEY `idx_user` (`user_id`),
  CONSTRAINT `cart_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `cart_ibfk_2` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=30 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `cart`
--

LOCK TABLES `cart` WRITE;
/*!40000 ALTER TABLE `cart` DISABLE KEYS */;
INSERT INTO `cart` VALUES (6,2,1,1,'2026-03-01 17:09:57','2026-03-01 17:09:57');
/*!40000 ALTER TABLE `cart` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `categories`
--

DROP TABLE IF EXISTS `categories`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `categories` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(50) NOT NULL,
  `slug` varchar(50) NOT NULL,
  `description` text DEFAULT NULL,
  `image` varchar(255) DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `name` (`name`),
  UNIQUE KEY `slug` (`slug`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `categories`
--

LOCK TABLES `categories` WRITE;
/*!40000 ALTER TABLE `categories` DISABLE KEYS */;
INSERT INTO `categories` VALUES (1,'Men\'s Watches','mens-watches','Stylish and sophisticated watches for men',NULL,1,'2026-02-15 09:43:56','2026-02-15 09:43:56'),(2,'Women\'s Watches','womens-watches','Elegant and trendy watches for women',NULL,1,'2026-02-15 09:43:56','2026-02-15 09:43:56'),(3,'Smart Watches','smart-watches','Advanced smartwatches with modern features',NULL,1,'2026-02-15 09:43:56','2026-02-15 09:43:56'),(4,'Luxury Watches','luxury-watches','',NULL,1,'2026-02-15 09:43:56','2026-03-02 13:27:46'),(5,'Sports Watches','sports-watches','Durable watches for sports and outdoor activities',NULL,1,'2026-02-15 09:43:56','2026-02-15 09:43:56');
/*!40000 ALTER TABLE `categories` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `contact_messages`
--

DROP TABLE IF EXISTS `contact_messages`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `contact_messages` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `subject` varchar(200) NOT NULL,
  `message` text NOT NULL,
  `is_read` tinyint(1) DEFAULT 0,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `contact_messages`
--

LOCK TABLES `contact_messages` WRITE;
/*!40000 ALTER TABLE `contact_messages` DISABLE KEYS */;
INSERT INTO `contact_messages` VALUES (1,'jeet','pjeet2176@gmail.com','thg','t ghter ter hrt',0,'2026-02-15 16:22:43'),(2,'jeets','admin@watchstore.com','ggg','refgerf f',1,'2026-02-17 17:27:50');
/*!40000 ALTER TABLE `contact_messages` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `coupons`
--

DROP TABLE IF EXISTS `coupons`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `coupons` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `code` varchar(50) NOT NULL,
  `description` varchar(255) DEFAULT NULL,
  `discount_type` enum('percentage','fixed') NOT NULL,
  `discount_value` decimal(10,2) NOT NULL,
  `min_order_amount` decimal(10,2) DEFAULT 0.00,
  `max_discount` decimal(10,2) DEFAULT NULL,
  `usage_limit` int(11) DEFAULT NULL,
  `used_count` int(11) DEFAULT 0,
  `start_date` date DEFAULT NULL,
  `expiry_date` date DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `code` (`code`),
  KEY `idx_code` (`code`),
  KEY `idx_active` (`is_active`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `coupons`
--

LOCK TABLES `coupons` WRITE;
/*!40000 ALTER TABLE `coupons` DISABLE KEYS */;
INSERT INTO `coupons` VALUES (1,'WATCH10','','percentage',10.00,0.00,NULL,NULL,0,NULL,NULL,1,'2026-02-15 15:42:13','2026-02-15 15:42:13'),(2,'WATCH50','','percentage',500.00,0.00,NULL,NULL,0,NULL,NULL,1,'2026-02-17 17:15:57','2026-02-17 17:15:57');
/*!40000 ALTER TABLE `coupons` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `order_items`
--

DROP TABLE IF EXISTS `order_items`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `order_items` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `order_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `product_name` varchar(200) NOT NULL,
  `quantity` int(11) NOT NULL,
  `price` decimal(10,2) NOT NULL,
  `total` decimal(10,2) NOT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `product_id` (`product_id`),
  KEY `idx_order` (`order_id`),
  CONSTRAINT `order_items_ibfk_1` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE,
  CONSTRAINT `order_items_ibfk_2` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=28 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `order_items`
--

LOCK TABLES `order_items` WRITE;
/*!40000 ALTER TABLE `order_items` DISABLE KEYS */;
INSERT INTO `order_items` VALUES (1,1,2,'Sport Chronograph',2,2999.00,5998.00,'2026-02-15 15:43:05'),(2,1,5,'Apple SmartWatch Series',1,32999.00,32999.00,'2026-02-15 15:43:05'),(3,2,1,'Classic Leather Watch',1,4999.00,4999.00,'2026-02-17 13:38:35'),(4,3,1,'Classic Leather Watch',2,4999.00,9998.00,'2026-02-17 13:47:43'),(5,4,1,'Classic Leather Watch',1,4999.00,4999.00,'2026-02-17 17:13:51'),(6,5,2,'Sport Chronograph',1,2999.00,2999.00,'2026-03-02 13:36:10'),(7,6,1,'Classic Leather Watch',1,4999.00,4999.00,'2026-03-02 13:48:05'),(8,7,2,'Sport Chronograph',1,2999.00,2999.00,'2026-03-02 15:38:05'),(9,8,8,'Business Professional',1,8499.00,8499.00,'2026-03-02 15:41:59'),(10,9,2,'Sport Chronograph',3,2999.00,8997.00,'2026-03-25 03:40:44'),(11,9,3,'Rose Gold Elegance',1,7499.00,7499.00,'2026-03-25 03:40:44'),(12,10,1,'Classic Leather Watch',1,4999.00,4999.00,'2026-04-01 20:29:41'),(13,11,1,'Classic Leather Watch',1,4999.00,4999.00,'2026-04-01 21:28:11'),(14,12,2,'Sport Chronograph',1,2999.00,2999.00,'2026-04-01 21:30:32'),(15,13,3,'Rose Gold Elegance',1,7499.00,7499.00,'2026-04-01 21:54:51'),(16,14,5,'Apple SmartWatch Series',1,32999.00,32999.00,'2026-04-01 22:02:33'),(17,15,2,'Sport Chronograph',1,2999.00,2999.00,'2026-04-01 22:10:53'),(18,16,1,'Classic Leather Watch',1,4999.00,4999.00,'2026-04-01 22:15:42'),(19,17,1,'Classic Leather Watch',1,4999.00,4999.00,'2026-04-01 22:16:19'),(20,18,1,'Classic Leather Watch',1,4999.00,4999.00,'2026-04-14 04:35:29'),(21,19,5,'Apple SmartWatch Series',1,32999.00,32999.00,'2026-04-14 04:46:25'),(22,20,1,'Classic Leather Watch',1,4999.00,4999.00,'2026-04-14 04:54:40'),(23,21,2,'Sport Chronograph',1,2999.00,2999.00,'2026-04-14 04:56:49'),(24,22,2,'Sport Chronograph',1,2999.00,2999.00,'2026-04-14 04:57:54'),(25,23,1,'Classic Leather Watch',1,4999.00,4999.00,'2026-04-14 04:58:56'),(26,24,1,'Classic Leather Watch',1,4999.00,4999.00,'2026-04-14 05:01:07'),(27,25,3,'Rose Gold Elegance',1,7499.00,7499.00,'2026-04-14 05:02:40');
/*!40000 ALTER TABLE `order_items` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `orders`
--

DROP TABLE IF EXISTS `orders`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `orders` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `order_number` varchar(50) NOT NULL,
  `full_name` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `phone` varchar(20) NOT NULL,
  `shipping_address` text NOT NULL,
  `billing_address` text DEFAULT NULL,
  `city` varchar(100) NOT NULL,
  `state` varchar(100) NOT NULL,
  `zip_code` varchar(20) NOT NULL,
  `country` varchar(100) NOT NULL DEFAULT 'India',
  `subtotal` decimal(10,2) NOT NULL,
  `discount` decimal(10,2) DEFAULT 0.00,
  `tax` decimal(10,2) DEFAULT 0.00,
  `total` decimal(10,2) NOT NULL,
  `payment_method` enum('COD','Online') NOT NULL,
  `payment_status` enum('Pending','Completed','Failed') DEFAULT 'Pending',
  `order_status` enum('Pending','Processing','Shipped','Delivered','Cancelled') DEFAULT 'Pending',
  `coupon_code` varchar(50) DEFAULT NULL,
  `razorpay_order_id` varchar(255) DEFAULT NULL,
  `razorpay_payment_id` varchar(255) DEFAULT NULL,
  `razorpay_signature` varchar(255) DEFAULT NULL,
  `order_notes` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `order_number` (`order_number`),
  KEY `idx_user` (`user_id`),
  KEY `idx_order_number` (`order_number`),
  KEY `idx_status` (`order_status`),
  CONSTRAINT `orders_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=26 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `orders`
--

LOCK TABLES `orders` WRITE;
/*!40000 ALTER TABLE `orders` DISABLE KEYS */;
INSERT INTO `orders` VALUES (1,2,'ORD-6991E989213D0','tushar','tushar09250@gmail.com','1234333333','edwsfg efg ewrf werfg, fgewr, efwfwef - 323245','edwsfg efg ewrf werfg, fgewr, efwfwef - 323245','fgewr','efwfwef','323245','India',38997.00,0.00,7019.46,46016.46,'COD','Pending','Pending',NULL,NULL,NULL,NULL,NULL,'2026-02-15 15:43:05','2026-02-15 15:43:05'),(2,2,'ORD-69946F5BEE7FE','tushar','tushar09250@gmail.com','4554454545','fhdrhd, hrdfd, fdhdfh - 323245','fhdrhd, hrdfd, fdhdfh - 323245','hrdfd','fdhdfh','323245','India',4999.00,0.00,899.82,5898.82,'COD','Completed','Delivered',NULL,NULL,NULL,NULL,NULL,'2026-02-17 13:38:35','2026-02-17 13:42:00'),(3,2,'ORD-6994717F4E6DF','tushar','tushar09250@gmail.com','4565645655','rfdhyh yhryh, 565464, 546546 - 323245','rfdhyh yhryh, 565464, 546546 - 323245','565464','546546','323245','India',9998.00,0.00,1799.64,11797.64,'COD','Completed','Delivered',NULL,NULL,NULL,NULL,NULL,'2026-02-17 13:47:43','2026-02-17 13:56:24'),(4,2,'ORD-6994A1CF8F3C5','tushar','tushar09250@gmail.com','4565645655','dfgdg gr retgh, er gerg er, g erg eg - 323245','dfgdg gr retgh, er gerg er, g erg eg - 323245','er gerg er','g erg eg','323245','India',4999.00,0.00,899.82,5898.82,'COD','Completed','Delivered',NULL,NULL,NULL,NULL,NULL,'2026-02-17 17:13:51','2026-02-17 17:16:26'),(5,5,'ORD-69A5924A36933','jeet','pjeet2176@gmail.com','2555555822','asdsfsdfsdsdvd, fg, dfdsd - 362625','asdsfsdfsdsdvd, fg, dfdsd - 362625','fg','dfdsd','362625','India',2999.00,0.00,539.82,3538.82,'COD','Pending','Pending',NULL,NULL,NULL,NULL,NULL,'2026-03-02 13:36:10','2026-03-02 13:36:10'),(6,5,'ORD-69A5951580077','jeet','pjeet2176@gmail.com','2555555822','fdssd s, fg, dfdsd - 362625','fdssd s, fg, dfdsd - 362625','fg','dfdsd','362625','India',4999.00,0.00,899.82,5898.82,'COD','Pending','Pending',NULL,NULL,NULL,NULL,NULL,'2026-03-02 13:48:05','2026-03-02 13:48:05'),(7,5,'ORD-69A5AEDD9CFAC','jeet','pjeet2176@gmail.com','2555555822','sa  a fefa f, fg, dfdsd - 362625','sa  a fefa f, fg, dfdsd - 362625','fg','dfdsd','362625','India',2999.00,0.00,539.82,3538.82,'COD','Pending','Pending',NULL,NULL,NULL,NULL,NULL,'2026-03-02 15:38:05','2026-03-02 15:38:05'),(8,5,'ORD-69A5AFC78E866','jeet','pjeet2176@gmail.com','2555555822','qqqqqqqqqqqqqqqqqqqqqqqqqqqq, qqqqqqqqqqqqq, qqqqqqqqqqqq - 362625','qqqqqqqqqqqqqqqqqqqqqqqqqqqq, qqqqqqqqqqqqq, qqqqqqqqqqqq - 362625','qqqqqqqqqqqqq','qqqqqqqqqqqq','362625','India',8499.00,0.00,1529.82,10028.82,'COD','Pending','Pending',NULL,NULL,NULL,NULL,NULL,'2026-03-02 15:41:59','2026-03-02 15:41:59'),(9,5,'ORD-69C3593C0047A','jeet','pjeet2176@gmail.com','7096868452','rku, rajkot, gujrat - 362625','rku, rajkot, gujrat - 362625','rajkot','gujrat','362625','India',16496.00,0.00,2969.28,19465.28,'Online','Completed','Pending',NULL,NULL,NULL,NULL,NULL,'2026-03-25 03:40:44','2026-03-25 03:40:44'),(10,24,'ORD-69CD8035349EB','Tushar','tushar633712@gmail.com','1234567811','drttt yyyf. f fttfy, fadf, fewfwe - 466777','drttt yyyf. f fttfy, fadf, fewfwe - 466777','fadf','fewfwe','466777','India',4999.00,0.00,899.82,5898.82,'Online','Completed','Pending',NULL,NULL,NULL,NULL,NULL,'2026-04-01 20:29:41','2026-04-01 20:29:41'),(11,24,'ORD-69CD8DEBDC664','Tushar','tushar633712@gmail.com','1234567811','eff efef ef, fewqfefw, fewfwe - 466777','eff efef ef, fewqfefw, fewfwe - 466777','fewqfefw','fewfwe','466777','India',4999.00,0.00,899.82,5898.82,'COD','Pending','Pending',NULL,NULL,NULL,NULL,NULL,'2026-04-01 21:28:11','2026-04-01 21:28:11'),(12,24,'ORD-69CD8E7892E02','Tushar','tushar633712@gmail.com','1234567811','vghghvgvg, fadf, fewfwe - 466777','vghghvgvg, fadf, fewfwe - 466777','fadf','fewfwe','466777','India',2999.00,0.00,539.82,3538.82,'COD','Completed','Delivered',NULL,NULL,NULL,NULL,NULL,'2026-04-01 21:30:32','2026-04-01 21:31:10'),(13,24,'ORD-69CD942B5CE5F','Tushar','tushar633712@gmail.com','1234567811','efwfwefwef, fewqfefw, fewfwe - 466777','efwfwefwef, fewqfefw, fewfwe - 466777','fewqfefw','fewfwe','466777','India',7499.00,0.00,1349.82,8848.82,'Online','Completed','Pending',NULL,NULL,NULL,NULL,NULL,'2026-04-01 21:54:51','2026-04-01 21:54:51'),(14,24,'ORD-69CD95F9116F7','Tushar','tushar633712@gmail.com','1234567811','f y hgjh, fewqfefw, fewfwe - 466777','f y hgjh, fewqfefw, fewfwe - 466777','fewqfefw','fewfwe','466777','India',32999.00,0.00,5939.82,38938.82,'Online','Pending','Pending',NULL,NULL,NULL,NULL,NULL,'2026-04-01 22:02:33','2026-04-01 22:02:33'),(15,24,'ORD-69CD97ED5AF2B','Tushar Solanki','tushar633712@gmail.com','1234567811','fenkwjsd fwsjkv, fadf, fewfwe - 466777','fenkwjsd fwsjkv, fadf, fewfwe - 466777','fadf','fewfwe','466777','India',2999.00,0.00,539.82,3538.82,'Online','Pending','Pending',NULL,NULL,NULL,NULL,NULL,'2026-04-01 22:10:53','2026-04-01 22:10:53'),(16,24,'ORD-69CD990E66C7F','Tushar Solanki','tushar633712@gmail.com','1234567811','wqdd qdqwdfqw, fewqfefw, fewfwe - 466777','wqdd qdqwdfqw, fewqfefw, fewfwe - 466777','fewqfefw','fewfwe','466777','India',4999.00,0.00,899.82,5898.82,'Online','Pending','Pending',NULL,NULL,NULL,NULL,NULL,'2026-04-01 22:15:42','2026-04-01 22:15:42'),(17,24,'ORD-69CD9933C9569','Tushar Solanki','tushar633712@gmail.com','1234567811','dshvn sdk jvsdjkn, wecw, csdcsdc - 466777','dshvn sdk jvsdjkn, wecw, csdcsdc - 466777','wecw','csdcsdc','466777','India',4999.00,0.00,899.82,5898.82,'COD','Pending','Pending',NULL,NULL,NULL,NULL,NULL,'2026-04-01 22:16:19','2026-04-01 22:16:19'),(18,7,'ORD-69DDC411EE762','Dhyey','ddhaduk480@rku.ac.in','1234567811','ejkf jkweb hfweh, fadf, fewfwe - 466777','ejkf jkweb hfweh, fadf, fewfwe - 466777','fadf','fewfwe','466777','India',4999.00,0.00,899.82,5898.82,'Online','Pending','Pending',NULL,NULL,NULL,NULL,NULL,'2026-04-14 04:35:29','2026-04-14 04:35:29'),(19,7,'ORD-69DDC6A1DE838','Dhyey','ddhaduk480@rku.ac.in','1234567811','ehf hekwjfbewfbjwf, dcwdfc, wefdwewef - 466777','ehf hekwjfbewfbjwf, dcwdfc, wefdwewef - 466777','dcwdfc','wefdwewef','466777','India',32999.00,0.00,5939.82,38938.82,'Online','Pending','Pending',NULL,NULL,NULL,NULL,NULL,'2026-04-14 04:46:25','2026-04-14 04:46:25'),(20,24,'ORD-69DDC8906456A','Test User','tushar633712@gmail.com','9999999999','123 Test Street, Mumbai, Maharashtra - 400001','123 Test Street, Mumbai, Maharashtra - 400001','Mumbai','Maharashtra','400001','India',4999.00,0.00,899.82,5898.82,'Online','Pending','Pending',NULL,'order_SdFRdEFbN5rPo3',NULL,NULL,NULL,'2026-04-14 04:54:40','2026-04-14 04:54:41'),(21,7,'ORD-69DDC911A0894','Dhyey','ddhaduk480@rku.ac.in','9974748389','ewqdfwe few f ew, rajkot, dfdf - 466777','ewqdfwe few f ew, rajkot, dfdf - 466777','rajkot','dfdf','466777','India',2999.00,0.00,539.82,3538.82,'Online','Pending','Pending',NULL,NULL,NULL,NULL,NULL,'2026-04-14 04:56:49','2026-04-14 04:56:49'),(22,7,'ORD-69DDC952C4A98','Dhyey','ddhaduk480@rku.ac.in','1234567811','wdwqdj qwihdq, fadf, fewfwe - 466777','wdwqdj qwihdq, fadf, fewfwe - 466777','fadf','fewfwe','466777','India',2999.00,0.00,539.82,3538.82,'Online','Pending','Pending',NULL,NULL,NULL,NULL,NULL,'2026-04-14 04:57:54','2026-04-14 04:57:54'),(23,24,'ORD-69DDC9909DD5B','Tushar Solanki','tushar633712@gmail.com','1234567811','dqwdq ewd we f, fadf, fewfwe - 466777','dqwdq ewd we f, fadf, fewfwe - 466777','fadf','fewfwe','466777','India',4999.00,0.00,899.82,5898.82,'Online','Pending','Pending',NULL,NULL,NULL,NULL,NULL,'2026-04-14 04:58:56','2026-04-14 04:58:56'),(24,7,'ORD-69DDCA1348B2F','Dhyey','ddhaduk480@rku.ac.in','9974748389','ewfwe fwe few, fadf, dfdf - 466777','ewfwe fwe few, fadf, dfdf - 466777','fadf','dfdf','466777','India',4999.00,0.00,899.82,5898.82,'Online','Completed','Delivered',NULL,'order_SdFYRBffBMGlwo','pay_SdFYYwo8DVu2Ov','122c174660a44767d18f58ac10e1b74c218c48f9bfd0feefe136ff4bca761f97',NULL,'2026-04-14 05:01:07','2026-04-14 05:02:10'),(25,24,'ORD-69DDCA7036465','Tushar Solanki','tushar633712@gmail.com','1234567811','ewfnwef nwnjef, fewqfefw, dfdf - 466777','ewfnwef nwnjef, fewqfefw, dfdf - 466777','fewqfefw','dfdf','466777','India',7499.00,0.00,1349.82,8848.82,'Online','Completed','Pending',NULL,'order_SdFa4XeMLLcqw2','pay_SdFa96hbJbRnoU','382872937e121302b4f277a67b78875718906196e7a5277c7dfb7b1c5fe54eda',NULL,'2026-04-14 05:02:40','2026-04-14 05:03:01');
/*!40000 ALTER TABLE `orders` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `password_resets`
--

DROP TABLE IF EXISTS `password_resets`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `password_resets` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `email` varchar(100) NOT NULL,
  `otp` varchar(6) NOT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `expires_at` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `is_used` tinyint(1) DEFAULT 0,
  PRIMARY KEY (`id`),
  KEY `idx_email` (`email`),
  KEY `idx_otp` (`otp`)
) ENGINE=InnoDB AUTO_INCREMENT=14 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `password_resets`
--

LOCK TABLES `password_resets` WRITE;
/*!40000 ALTER TABLE `password_resets` DISABLE KEYS */;
INSERT INTO `password_resets` VALUES (1,'tushar09250@gmail.com','492145','2026-02-15 14:44:35','2026-02-15 09:44:35',0),(2,'tushar09250@gmail.com','145612','2026-02-15 14:45:33','2026-02-15 09:45:33',0),(3,'tushar09250@gmail.com','126791','2026-02-15 15:18:10','2026-02-15 10:18:10',0),(4,'pjeet2176@gmail.com','644431','2026-03-25 03:29:19','2026-03-24 22:29:19',0),(5,'pjeet2176@gmail.com','427256','2026-03-25 03:32:31','2026-03-24 22:32:31',0),(6,'pjeet2176@gmail.com','524267','2026-03-25 03:35:04','2026-03-24 22:35:04',0),(7,'pjeet2176@gmail.com','940031','2026-03-25 03:35:50','2026-03-24 22:35:50',0),(8,'pjeet2176@gmail.com','750160','2026-03-25 04:11:19','2026-03-24 23:11:19',0),(9,'pjeet2176@gmail.com','542934','2026-04-01 16:03:30','2026-04-01 11:03:30',0),(10,'pjeet2176@gmail.com','135609','2026-04-01 19:37:24','2026-04-01 14:37:24',0),(11,'pjeet2176@gmail.com','739355','2026-04-01 19:41:04','2026-04-01 14:41:04',0),(12,'pjeet2176@gmail.com','781081','2026-04-01 19:48:37','2026-04-01 14:48:37',0),(13,'tushar633712@gmail.com','151204','2026-04-01 20:26:56','2026-04-01 17:26:56',0);
/*!40000 ALTER TABLE `password_resets` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `product_images`
--

DROP TABLE IF EXISTS `product_images`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `product_images` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `product_id` int(11) NOT NULL,
  `image_path` varchar(255) NOT NULL,
  `is_primary` tinyint(1) DEFAULT 0,
  `display_order` int(11) DEFAULT 0,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_product` (`product_id`),
  CONSTRAINT `product_images_ibfk_1` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=31 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `product_images`
--

LOCK TABLES `product_images` WRITE;
/*!40000 ALTER TABLE `product_images` DISABLE KEYS */;
INSERT INTO `product_images` VALUES (4,2,'watch_2_0_6991d9e6a7058.jpg',1,0,'2026-02-15 14:36:25'),(5,2,'watch_2_1_6991d9e941cdf.jpg',0,1,'2026-02-15 14:36:27'),(6,2,'watch_2_2_6991d9eb935eb.jpg',0,2,'2026-02-15 14:36:29'),(7,3,'watch_3_0_6991d9ed890b2.jpg',1,0,'2026-02-15 14:36:31'),(9,3,'watch_3_2_6991d9f128e48.jpg',0,2,'2026-02-15 14:36:35'),(10,4,'watch_4_0_6991d9f3733cd.jpg',1,0,'2026-02-15 14:36:39'),(11,4,'watch_4_1_6991d9f745154.jpg',0,1,'2026-02-15 14:36:42'),(12,4,'watch_4_2_6991d9fa61b1b.jpg',0,2,'2026-02-15 14:36:45'),(13,5,'watch_5_0_6991d9fd3f014.jpg',1,0,'2026-02-15 14:36:47'),(14,5,'watch_5_1_6991d9ff642b5.jpg',0,1,'2026-02-15 14:36:49'),(15,5,'watch_5_2_6991da019f241.jpg',0,2,'2026-02-15 14:36:51'),(20,8,'watch_8_0_6991da0c2b001.jpg',1,0,'2026-02-15 14:37:01'),(21,8,'watch_8_1_6991da0d7bda6.jpg',0,1,'2026-02-15 14:37:03'),(22,8,'watch_8_2_6991da0f7663e.jpg',0,2,'2026-02-15 14:37:04'),(27,1,'6994a4992bd3a_1771349145.jpg',0,0,'2026-02-17 17:25:45'),(28,1,'6994a4992c415_1771349145.jpg',0,1,'2026-02-17 17:25:45'),(29,1,'6994a4992ca0e_1771349145.jpg',1,2,'2026-02-17 17:25:45');
/*!40000 ALTER TABLE `product_images` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `products`
--

DROP TABLE IF EXISTS `products`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `products` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `category_id` int(11) NOT NULL,
  `name` varchar(200) NOT NULL,
  `slug` varchar(200) NOT NULL,
  `brand` varchar(100) NOT NULL,
  `model_number` varchar(100) DEFAULT NULL,
  `description` text NOT NULL,
  `price` decimal(10,2) NOT NULL,
  `discount_price` decimal(10,2) DEFAULT NULL,
  `stock` int(11) NOT NULL DEFAULT 0,
  `movement_type` enum('Quartz','Automatic','Mechanical','Smart') NOT NULL,
  `case_material` varchar(100) DEFAULT NULL,
  `strap_material` varchar(100) DEFAULT NULL,
  `water_resistance` varchar(50) DEFAULT NULL,
  `warranty` varchar(100) DEFAULT NULL,
  `features` text DEFAULT NULL,
  `is_featured` tinyint(1) DEFAULT 0,
  `is_active` tinyint(1) DEFAULT 1,
  `views` int(11) DEFAULT 0,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `slug` (`slug`),
  KEY `idx_category` (`category_id`),
  KEY `idx_featured` (`is_featured`),
  KEY `idx_active` (`is_active`),
  CONSTRAINT `products_ibfk_1` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=10 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `products`
--

LOCK TABLES `products` WRITE;
/*!40000 ALTER TABLE `products` DISABLE KEYS */;
INSERT INTO `products` VALUES (1,1,'Classic Leather Watch','classic-leather-watch','Timex','TX-1001','Elegant leather strap watch perfect for formal occasions',5999.00,4999.00,2,'Quartz','Stainless Steel','Genuine Leather','30m','2 Years','Date Display\r\nScratch Resistant Glass\r\nWater Resistant',1,1,135,'2026-02-15 09:54:25','2026-04-14 05:01:07'),(2,1,'Sport Chronograph','sport-chronograph','Casio','CS-2002','Durable sports watch with chronograph functionality',3499.00,2999.00,14,'Quartz','Plastic Resin','Rubber','100m','1 Year','Stopwatch Function\nAlarm\nBacklight',1,1,15,'2026-02-15 09:54:25','2026-04-14 04:57:54'),(3,2,'Rose Gold Elegance','rose-gold-elegance','Fossil','FS-3003','Beautiful rose gold watch for women',8999.00,7499.00,9,'Quartz','Stainless Steel','Metal Bracelet','50m','2 Years','Rose Gold Plating\r\nElegant Design\r\nDate Window',1,1,9,'2026-02-15 09:54:25','2026-04-14 05:02:40'),(4,3,'Smart Fitness Tracker','smart-fitness-tracker','Samsung','SM-4004','Advanced smartwatch with fitness tracking',12999.00,11999.00,20,'Smart','Aluminum','Silicone','50m','1 Year','Heart Rate Monitor\nGPS Tracking\nNotifications\nFitness Apps',1,1,9,'2026-02-15 09:54:25','2026-04-01 15:52:52'),(5,3,'Apple SmartWatch Series','apple-smartwatch-series','Apple','AW-5005','Premium smartwatch with advanced features',35999.00,32999.00,5,'Smart','Aluminum','Sport Band','50m','1 Year','ECG App\nBlood Oxygen\nAlways-On Display\nCellular',1,1,19,'2026-02-15 09:54:25','2026-04-14 04:46:25'),(8,1,'Business Professional','business-professional','Citizen','CT-8008','Professional watch for business meetings',9999.00,8499.00,9,'Automatic','Stainless Steel','Leather','50m','3 Years','Sapphire Crystal\nDate Display\nAutomatic Movement',0,1,5,'2026-02-15 09:54:25','2026-04-01 22:20:12');
/*!40000 ALTER TABLE `products` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `reviews`
--

DROP TABLE IF EXISTS `reviews`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `reviews` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `product_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `rating` int(11) NOT NULL,
  `title` varchar(200) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `comment` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `is_approved` tinyint(1) DEFAULT 0,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_user_product_review` (`user_id`,`product_id`),
  KEY `idx_product` (`product_id`),
  KEY `idx_approved` (`is_approved`),
  CONSTRAINT `reviews_ibfk_1` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE,
  CONSTRAINT `reviews_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `reviews`
--

LOCK TABLES `reviews` WRITE;
/*!40000 ALTER TABLE `reviews` DISABLE KEYS */;
INSERT INTO `reviews` VALUES (2,1,2,4,'fhjnfgh','fghfghfg',1,'2026-02-17 14:38:13','2026-03-02 13:29:47'),(3,1,24,3,'good','wqdwef',0,'2026-04-01 21:45:58','2026-04-01 21:45:58'),(4,3,24,4,'dsvsdv','sdcsdvsd',0,'2026-04-01 22:16:48','2026-04-01 22:16:48'),(5,5,24,3,'scfsd','dsfsdf',0,'2026-04-01 22:18:05','2026-04-01 22:18:05'),(6,8,24,4,'wqdwqd','wdefescs',0,'2026-04-01 22:20:06','2026-04-01 22:20:06');
/*!40000 ALTER TABLE `reviews` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `site_settings`
--

DROP TABLE IF EXISTS `site_settings`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `site_settings` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `setting_key` varchar(100) NOT NULL,
  `setting_value` text DEFAULT NULL,
  `setting_type` enum('text','textarea','image','number') DEFAULT 'text',
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `setting_key` (`setting_key`)
) ENGINE=InnoDB AUTO_INCREMENT=10 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `site_settings`
--

LOCK TABLES `site_settings` WRITE;
/*!40000 ALTER TABLE `site_settings` DISABLE KEYS */;
INSERT INTO `site_settings` VALUES (1,'site_name','Watch Store','text','2026-02-15 09:43:56'),(2,'site_email','info@watchstore.com','text','2026-02-15 09:43:56'),(3,'site_phone','+91 1234567890','text','2026-02-15 09:43:56'),(4,'site_address','123 Watch Street, Mumbai, India','textarea','2026-02-15 09:43:56'),(5,'facebook_url','#','text','2026-02-15 09:43:56'),(6,'twitter_url','#','text','2026-02-15 09:43:56'),(7,'instagram_url','#','text','2026-02-15 09:43:56'),(8,'free_shipping_min','2000','number','2026-02-15 09:43:56'),(9,'tax_percentage','0','number','2026-02-15 09:43:56');
/*!40000 ALTER TABLE `site_settings` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `users`
--

DROP TABLE IF EXISTS `users`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `full_name` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `address` text DEFAULT NULL,
  `profile_image` varchar(255) DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT 0,
  `activation_token` varchar(100) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB AUTO_INCREMENT=26 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `users`
--

LOCK TABLES `users` WRITE;
/*!40000 ALTER TABLE `users` DISABLE KEYS */;
INSERT INTO `users` VALUES (2,'tushar','tushar09250@gmail.com','$2y$10$iK64zHVx1EItOKoftFDbBehL/ERb0rgcWxj2VMjGo3f3fttKFVe0W','',NULL,'user_2_1771163718.png',1,NULL,'2026-02-15 13:49:57','2026-02-15 13:55:18'),(5,'Dhyey','pjeet2176@gmail.com','$2y$10$LU3vUhIG3U8M1C7rx4WJweBVYCcR2EyUWaCsr.U2ZiSnWEhmQsHEO','6351137065',NULL,'user_5_1774414183.png',1,'63338706e730f61c55846b5d4366cf07e0c11421a03574bba4b6ff9a4edf8807','2026-03-01 17:25:04','2026-03-25 04:49:43'),(6,'divy','mevadadivyesh15@gmail.com','$2y$10$g/twBO7I8RVQr7TiJN4c5uqLeMz8h91CWz/hq.CpMOxyYwe0D7eBq',NULL,NULL,NULL,1,'32c99ca7df56010c495198a2c0caf7d64d82d2b70e9944f7df43af57981e36f4','2026-03-23 15:23:15','2026-03-25 04:55:54'),(7,'Dhyey','ddhaduk480@rku.ac.in','$2y$10$3P74MYkriEqhAZioQ/Ew9eY2BO9N8CLe3BhBtcr89pBgk2fR2TS32',NULL,NULL,NULL,1,'45ab2e98264b27419b08adc07d0125a018439c121495916cd6659143407852ff','2026-03-25 04:41:49','2026-03-25 04:55:50'),(24,'Tushar Solanki','tushar633712@gmail.com','$2y$10$fWTF3nZPTbSU2wG1CNAiSeQWIwEJ8lwD.5RCp4U.F6t43lNO5uLSG','1234567811','rajkot','user_24_1775075315.jpeg',1,NULL,'2026-04-01 20:25:56','2026-04-01 22:07:54');
/*!40000 ALTER TABLE `users` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2026-04-14 10:39:30
