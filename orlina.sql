-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Jul 21, 2026 at 04:31 PM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.0.30

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `orlina`
--

-- --------------------------------------------------------

--
-- Table structure for table `blog_categories`
--

CREATE TABLE `blog_categories` (
  `id` int(11) NOT NULL,
  `name` varchar(150) NOT NULL,
  `slug` varchar(150) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `blog_posts`
--

CREATE TABLE `blog_posts` (
  `id` int(11) NOT NULL,
  `category_id` int(11) DEFAULT NULL,
  `title` varchar(255) NOT NULL,
  `slug` varchar(255) NOT NULL,
  `content` longtext NOT NULL,
  `featured_image` varchar(255) DEFAULT NULL,
  `status` enum('draft','published') DEFAULT 'draft',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `categories`
--

CREATE TABLE `categories` (
  `id` int(11) NOT NULL,
  `name` varchar(150) NOT NULL,
  `slug` varchar(150) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `categories`
--

INSERT INTO `categories` (`id`, `name`, `slug`, `created_at`) VALUES
(1, 'Dresses', 'dresses', '2026-07-06 08:07:01'),
(2, 'Sarees', 'sarees', '2026-07-06 08:07:01'),
(3, 'Abayas', 'abayas', '2026-07-06 08:07:01'),
(4, 'Handbags', 'handbags', '2026-07-06 08:07:01'),
(5, 'Jewelry', 'jewelry', '2026-07-06 08:07:01'),
(6, 'Shoes', 'shoes', '2026-07-06 08:07:01'),
(7, 'Skincare', 'skincare', '2026-07-06 08:07:01');

-- --------------------------------------------------------

--
-- Table structure for table `orders`
--

CREATE TABLE `orders` (
  `id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `order_number` varchar(50) NOT NULL,
  `total_amount` decimal(12,2) NOT NULL,
  `status` enum('pending','processing','shipped','delivered','cancelled') DEFAULT 'pending',
  `payment_method` varchar(50) DEFAULT NULL,
  `payment_status` enum('unpaid','paid','refunded') DEFAULT 'unpaid',
  `stripe_session_id` varchar(200) DEFAULT NULL,
  `shipping_address` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `orders`
--

INSERT INTO `orders` (`id`, `user_id`, `order_number`, `total_amount`, `status`, `payment_method`, `payment_status`, `stripe_session_id`, `shipping_address`, `created_at`) VALUES
(1, NULL, 'ORL-FC205900-20260707', 20000.00, 'cancelled', 'cod', 'unpaid', NULL, 'unpaid', '2026-07-07 15:39:18'),
(2, NULL, 'ORL-3B01BC57-20260707', 20000.00, 'pending', 'cod', 'unpaid', NULL, 'unpaid', '2026-07-07 15:40:12'),
(3, NULL, 'ORL-1B169B60-20260708', 30800.00, 'pending', 'cod', 'unpaid', NULL, 'unpaid', '2026-07-08 15:22:30'),
(4, NULL, 'ORL-EEC62E43-20260708', 13200.00, 'pending', 'cod', 'unpaid', NULL, 'unpaid', '2026-07-08 17:17:25'),
(5, NULL, 'ORL-3B339830-20260708', 2750.00, 'pending', 'bkash', 'unpaid', NULL, 'unpaid', '2026-07-08 17:17:39'),
(6, NULL, 'ORL-09A36DE4-20260708', 35550.00, 'pending', 'cod', 'unpaid', NULL, 'unpaid', '2026-07-08 17:29:37'),
(7, NULL, 'ORL-E531C694-20260709', 14850.00, 'pending', 'cod', 'unpaid', NULL, 'unpaid', '2026-07-09 05:57:01'),
(8, 9, 'ORL-1022BB8E-20260709', 3850.00, 'pending', 'nagad', 'unpaid', NULL, 'unpaid', '2026-07-09 06:30:43');

-- --------------------------------------------------------

--
-- Table structure for table `order_items`
--

CREATE TABLE `order_items` (
  `id` int(11) NOT NULL,
  `order_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `quantity` int(11) NOT NULL,
  `price` decimal(12,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `order_items`
--

INSERT INTO `order_items` (`id`, `order_id`, `product_id`, `quantity`, `price`) VALUES
(1, 1, 1, 2, 10000.00),
(2, 2, 1, 2, 10000.00),
(3, 3, 2, 8, 3850.00),
(4, 4, 2, 2, 3850.00),
(5, 4, 3, 2, 2750.00),
(6, 5, 3, 1, 2750.00),
(7, 6, 3, 1, 2750.00),
(8, 6, 10, 2, 3600.00),
(9, 6, 12, 8, 3200.00),
(10, 7, 2, 1, 3850.00),
(11, 7, 3, 2, 2750.00),
(12, 7, 4, 1, 5500.00),
(13, 8, 2, 1, 3850.00);

-- --------------------------------------------------------

--
-- Table structure for table `products`
--

CREATE TABLE `products` (
  `id` int(11) NOT NULL,
  `category_id` int(11) NOT NULL,
  `name` varchar(200) NOT NULL,
  `slug` varchar(200) NOT NULL,
  `description` text DEFAULT NULL,
  `price` decimal(12,2) NOT NULL,
  `old_price` decimal(12,2) DEFAULT NULL,
  `badge` enum('NEW','SALE','TRENDING') DEFAULT NULL,
  `stock` int(11) DEFAULT 0,
  `featured` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `products`
--

INSERT INTO `products` (`id`, `category_id`, `name`, `slug`, `description`, `price`, `old_price`, `badge`, `stock`, `featured`, `created_at`) VALUES
(1, 3, 'Premium Fabrics Icon Premium Fabrics Handmade Icon Handmade Ethical Trading Icon Ethical Trading Fair Wages Icon Fair Wages Junior Girls Premium Duchess Ruffle Open Abaya – Lilac Mist', 'premium-fabrics-icon-premium-fabrics-handmade-icon-handmade-ethical-trading-icon-ethical-trading-fair-wages-icon-fair-wages-junior-girls-premium-duchess-ruffle-open-abaya-lilac-mist', 'HOW TO MEASURE YOUR CHILD\'S ABAYA SIZE\r\n\r\nMeasure your child from the top of your shoulder to the point where you\'d like the abaya to fall, in inches. This is the length she will need. For example, if the measurement is 44\", she will require an abaya with a length of 44\". \r\n\r\nAdditionally, please ensure that your childs chest measurement across the front is 1\" smaller than the corresponding chest measurement for your selected length in the table below. If the chest measurement is larger, the abaya may fit too tightly, and we recommend sizing up for a more comfortable fit.', 10000.00, 150000.00, 'NEW', 6, 1, '2026-07-07 15:37:18'),
(2, 1, 'Digital Print Work Silk Designer Gown In Blue', 'midnight-rose-embroidered-dress', 'Elegant full-length embroidered dress with delicate rose patterns. Perfect for evening events and special occasions. Crafted from premium georgette fabric with a flattering A-line silhouette.', 3850.00, 5200.00, 'TRENDING', 6, 1, '2026-07-07 16:31:10'),
(3, 1, 'Long Sleeves Wedding Dres', 'sunset-chiffon-maxi-dress', 'Flowing chiffon maxi dress in warm sunset tones. Features a halter neckline with a wrap tie waist. Ideal for garden parties and beach weddings.', 2750.00, NULL, 'NEW', 19, 1, '2026-07-07 16:31:10'),
(4, 1, 'Royal Blue Anarkali Gown', 'royal-blue-anarkali-gown', 'Stunning royal blue Anarkali gown with intricate gold zari work. Long sleeves with churidar bottom. A regal choice for festive occasions and family gatherings.', 5500.00, 7000.00, 'SALE', 11, 1, '2026-07-07 16:31:10'),
(5, 1, 'Blush Pink Cocktail Dress', 'blush-pink-cocktail-dress', 'Chic blush pink cocktail dress with subtle shimmer and a flared skirt. Features a sweetheart neckline and back zip closure. A modern classic for any celebration.', 2200.00, NULL, 'TRENDING', 30, 0, '2026-07-07 16:31:10'),
(7, 2, 'Banarasi Georgette Saree - Crimson', 'banarasi-georgette-saree-crimson', 'Lightweight Banarasi georgette saree in deep crimson with delicate floral zari motifs. Easy to drape with a rich pallu. Perfect for festive and formal occasions.', 4200.00, NULL, 'NEW', 15, 1, '2026-07-07 16:31:10'),
(8, 2, 'Chanderi Cotton Saree - Mint Green', 'chanderi-cotton-saree-mint-green', 'Breezy Chanderi cotton saree in refreshing mint green with silver border. Comfortable for all-day wear with a graceful fall. Includes matching unstitched blouse piece.', 2800.00, 3500.00, 'TRENDING', 20, 0, '2026-07-07 16:31:10'),
(9, 3, 'Pearl White Luxury Abaya', 'pearl-white-luxury-abaya', 'Elegant pearl white open abaya with subtle floral lace panels along the sleeves and hemline. Made from premium crepe fabric that drapes beautifully. A modern modest fashion statement.', 4800.00, 6000.00, 'SALE', 14, 1, '2026-07-07 16:31:10'),
(10, 3, 'Ebony Black Embellished Abaya', 'ebony-black-embellished-abaya', 'Classic black abaya with gold embellishment on cuffs and neckline. Flowing silhouette made from high-quality nida fabric. Versatile enough for everyday wear and special events.', 3600.00, NULL, 'NEW', 20, 1, '2026-07-07 16:31:10'),
(11, 3, 'Dusty Rose Kimono Abaya', 'dusty-rose-kimono-abaya', 'Stylish kimono-style abaya in dusty rose with contrast piping details. Open-front design with inner slip dress included. A contemporary take on modest fashion.', 5200.00, NULL, 'TRENDING', 10, 0, '2026-07-07 16:31:10'),
(12, 4, 'Gucci Classic coded', 'quilted-chain-shoulder-bag-champagne', 'Luxurious quilted leather shoulder bag in champagne gold with antique chain strap. Features an interior pocket and magnetic snap closure. Elevate any outfit effortlessly.', 3200.00, 4500.00, 'SALE', 8, 1, '2026-07-07 16:31:10'),
(16, 5, 'Layered Gold Choker Necklace Set', 'layered-gold-choker-necklace-set', 'Trendy layered choker set with three chains of varying lengths in 18K gold plating. Includes lobster clasp with 5cm extender. A versatile everyday statement piece.', 1250.00, NULL, 'NEW', 28, 1, '2026-07-07 16:31:10'),
(19, 6, 'TOWED22 Flats Shoes Women', 'embroidered-mojari-flats-ivory', 'Traditional mojari flats in ivory with colorful thread embroidery and gold sequin work. Cushioned insole for comfort. Handcrafted heritage footwear for festive occasions.', 2000.00, NULL, 'NEW', 25, 0, '2026-07-07 16:31:10'),
(20, 7, 'Rose Gold Glow Serum', 'rose-gold-glow-serum', 'Luxurious brightening serum infused with 24K gold particles, rosehip oil, and vitamin C. Reduces dark spots and gives a radiant glow in just 2 weeks. Suitable for all skin types.', 1800.00, 2400.00, 'SALE', 40, 1, '2026-07-07 16:31:10'),
(21, 7, 'Hydra-Veil Moisturizing Cream', 'hydra-veil-moisturizing-cream', 'Rich yet lightweight moisturizing cream with hyaluronic acid and shea butter. Provides 72-hour hydration and visibly plumps fine lines. Perfect for dry and combination skin.', 1350.00, NULL, 'NEW', 50, 0, '2026-07-07 16:31:10'),
(22, 7, 'The Ordinary', 'the-ordinary', 'The Ordinary AHA 30% + BHA 2% Peeling Solution is a powerful exfoliating treatment that is designed to improve the appearance of skin texture and tone. The formula contains a combination of alpha hydroxy acids (AHAs) and beta hydroxy acids (BHAs) that work together to remove dead skin cells, unclog pores, and smooth the skin\'s surface.The AHAs in the formula include glycolic acid and lactic acid, which work to exfoliate the skin and improve skin texture. The BHA in the formula is salicylic acid, which is known for its ability to penetrate deep into pores and help to unclog them.In addition to the AHAs and BHAs, the formula also contains Tasmanian pepperberry, which helps to reduce inflammation and soothe the skin. The formula is also free from alcohol, oil, and silicone.The Ordinary AHA 30% + BHA 2% Peeling Solution is recommended for those with dull, uneven, or congested skin. It can be used up to twice a week as a part of a skincare routine to help improve the overall appearance and texture of the skin. It is important to follow the directions carefully and to patch test the product before using it on the face.', 700.00, NULL, NULL, 10, 0, '2026-07-09 14:15:21'),
(23, 4, 'Crossbody Bags', 'crossbody-bags', 'Crossbody Bags for Women Leather Cross Body Purses Cute Color-Block Designer Handbags Shoulder Bag Medium Size Green', 3000.00, NULL, NULL, 10, 0, '2026-07-11 14:19:19'),
(24, 6, 'Valentino High Heel', 'valentino-high-heel', 'Upper Material: Silk\r\nHandmade: Yes\r\nInsole Material: Bonded Leather\r\nLining Material: PU\r\nToe Shape: Pointed Toe\r\nOutsole Material: Rubber\r\nHeel Height: 10cm', 5000.00, NULL, NULL, 10, 0, '2026-07-11 14:25:10'),
(25, 7, 'Facial Beauty Creams', 'facial-beauty-creams', 'Facial beauty creams are topical skincare products that are formulated to improve the appearance and health of the skin. These creams are usually made with a combination of active ingredients, such as vitamins, antioxidants, peptides, and hyaluronic acid, among others. Facial beauty creams come in different types, including moisturizers, serums, masks, and night creams, among others.', 1500.00, NULL, 'NEW', 10, 0, '2026-07-12 05:38:04');

-- --------------------------------------------------------

--
-- Table structure for table `product_images`
--

CREATE TABLE `product_images` (
  `id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `image_url` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `product_images`
--

INSERT INTO `product_images` (`id`, `product_id`, `image_url`) VALUES
(1, 1, 'https://abayabuth.com/cdn/shop/files/AB_24_113.jpg?v=1769445339'),
(38, 20, 'https://images.unsplash.com/photo-1556228578-8c89e6adf883?w=600&h=800&fit=crop'),
(39, 20, 'https://images.unsplash.com/photo-1598440947619-2c35fc9aa908?w=600&h=800&fit=crop'),
(40, 21, 'https://images.unsplash.com/photo-1598440947619-2c35fc9aa908?w=600&h=800&fit=crop'),
(41, 21, 'https://images.unsplash.com/photo-1556228578-8c89e6adf883?w=600&h=800&fit=crop'),
(42, 7, 'https://tse1.explicit.bing.net/th/id/OIP.R6Vbxi647zwI98wga9my4wHaKf?r=0&w=1200&h=1700&rs=1&pid=ImgDetMain&o=7&rm=3'),
(43, 10, 'https://www.hayahalmuslimah.com/cdn/shop/files/Black-Crystal-Embellished-Open-Abaya-7_1800x1800.jpg?v=1738758947'),
(45, 22, 'https://www.byrdie.com/thmb/41CxHcu3tM287mdR1q_LumZ2Q2k=/1000x1000/filters:no_upscale():max_bytes(150000):strip_icc()/The-Ordinary-Vitamin-C-Suspension-2-7cbafd950ed14d91a39c6278f9a84364.jpg'),
(46, 8, 'https://tse1.mm.bing.net/th/id/OIP.g4Qxtp3z8Isu30lxo-GpfgHaLH?r=0&rs=1&pid=ImgDetMain&o=7&rm=3'),
(47, 9, 'https://i.pinimg.com/736x/b4/65/a1/b465a11dde1df706111798a84933df1a.jpg'),
(48, 11, 'https://tse1.explicit.bing.net/th/id/OIP.z250KewWw8SbjiO2vXCJ4QHaJ4?r=0&rs=1&pid=ImgDetMain&o=7&rm=3'),
(49, 23, 'https://img.everymarket.com/6f9i7auh7r077d2gn17951b9u3kr?width=800&height=800&format=jpg'),
(50, 4, 'https://th.bing.com/th/id/R.eb6457b770a177e0a6884b97ce8c14bf?rik=x9%2bJ9x04oWdyVA&riu=http%3a%2f%2fwww.zop.in%2fcdn%2fshop%2ffiles%2fKB241_BLUE_fceb1715-fa35-4d17-bb47-6ac4feeef2a2.jpg%3fv%3d1735206788&ehk=sUjNHT1OSJxLMF1ygU05DXxbcfz4B1TIhChEcuWVvqo%3d&ri'),
(51, 5, 'https://hips.hearstapps.com/hmg-prod/images/102879656-1530028302.jpg?crop=1xw:0.9776207302709069xh;center,top'),
(52, 24, 'https://cicciousa.com/cdn/shop/products/product-image-1926820771_1024x1024@2x.jpg?v=1644933539'),
(53, 2, 'https://cdn.sareeka.com/image/data2023/digital-print-work-silk-designer-gown-in-blue-for-ceremonial-274238.jpg'),
(54, 3, 'https://i.etsystatic.com/27357728/r/il/603684/3226929116/il_fullxfull.3226929116_8e96.jpg'),
(55, 12, 'https://media.karousell.com/media/photos/products/2023/2/22/gucci_classic_coded_1677040570_fa2cc66c.jpg'),
(56, 16, 'https://img.ltwebstatic.com/images3_pi/2021/10/29/16354934510b883f9938f2f833271b56b7ac3a4b78_thumbnail_600x.jpg'),
(57, 19, 'https://i5.walmartimages.com/asr/69a1bcc4-45fc-40e6-b77a-4bbed9a7dc63.747a3580095ee87583690849bbca4f8b.jpeg'),
(58, 25, 'https://www.topbest10reviews.com/wp-content/uploads/2017/12/Face-Creams.jpg');

-- --------------------------------------------------------

--
-- Table structure for table `reviews`
--

CREATE TABLE `reviews` (
  `id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `rating` int(11) NOT NULL CHECK (`rating` between 1 and 5),
  `review_text` text DEFAULT NULL,
  `status` enum('pending','approved','rejected') DEFAULT 'pending',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `settings`
--

CREATE TABLE `settings` (
  `id` int(11) NOT NULL,
  `setting_key` varchar(100) NOT NULL,
  `setting_value` text NOT NULL,
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `settings`
--

INSERT INTO `settings` (`id`, `setting_key`, `setting_value`, `updated_at`) VALUES
(1, 'active_currency', 'USD', '2026-07-09 06:33:00'),
(2, 'stripe_publishable_key', 'pk_test_REPLACE', '2026-07-06 08:07:01'),
(3, 'stripe_secret_key', 'sk_test_REPLACE', '2026-07-06 08:07:01'),
(4, 'paypal_client_id', '', '2026-07-06 08:07:01'),
(5, 'sslcommerz_store_id', '', '2026-07-06 08:07:01'),
(6, 'sslcommerz_store_password', '', '2026-07-06 08:07:01');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `first_name` varchar(100) NOT NULL,
  `last_name` varchar(100) NOT NULL,
  `email` varchar(150) NOT NULL,
  `password` varchar(255) NOT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `address` text DEFAULT NULL,
  `city` varchar(100) DEFAULT NULL,
  `avatar` varchar(255) DEFAULT NULL,
  `role` enum('member','admin','super_admin') DEFAULT 'member',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `first_name`, `last_name`, `email`, `password`, `phone`, `address`, `city`, `avatar`, `role`, `created_at`) VALUES
(1, 'Admin', 'Orlina', 'admin@orlina.com', '$2y$10$wHqv7WlXqY6Q2nYfZz1E5uV6mC0kzRZzGZqV6sE8rW8YwJXyZ7e2O', NULL, NULL, NULL, NULL, 'super_admin', '2026-07-06 08:07:01'),
(2, 'Test', 'User', 'testuser@example.com', '$2y$10$Bl6fD7lkIGnZ45wMMeeY6.xbvExI1Eff3TMpDJarHroMP.zBadYzC', '01700000000', NULL, NULL, NULL, 'member', '2026-07-07 05:02:30'),
(7, 'Test', 'User', 'random12345@example.com', '$2y$10$XGWvQG6x7M0GJ8wnG51aP.J6YBI6K2z5Xk8KoYSAkP27qavmSXccK', '01712345678', NULL, NULL, NULL, 'member', '2026-07-09 05:41:14'),
(8, 'Test', 'Duplicate', 'duplicate_diff_pass@example.com', '$2y$10$cLPlwdAlY/T/LjrDTCFTnOzB5EqFMqcAU4C6KFSXHe4IID7oi4mB6', '01700000001', NULL, NULL, NULL, 'member', '2026-07-09 05:47:14'),
(9, 'Fahmida', 'Islam', 'fahmida.anima@gmail.com', '$2y$10$pXRYzvZSxaX6x5s1GWWCEerBkm34ftsUZ5bIYD.tEM9/jwQcphgY.', '+8801896130212', NULL, NULL, NULL, 'super_admin', '2026-07-09 06:12:44');

-- --------------------------------------------------------

--
-- Table structure for table `wishlists`
--

CREATE TABLE `wishlists` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `wishlists`
--

INSERT INTO `wishlists` (`id`, `user_id`, `product_id`, `created_at`) VALUES
(11, 9, 10, '2026-07-09 06:30:01');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `blog_categories`
--
ALTER TABLE `blog_categories`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `slug` (`slug`);

--
-- Indexes for table `blog_posts`
--
ALTER TABLE `blog_posts`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `slug` (`slug`),
  ADD KEY `category_id` (`category_id`);

--
-- Indexes for table `categories`
--
ALTER TABLE `categories`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `slug` (`slug`);

--
-- Indexes for table `orders`
--
ALTER TABLE `orders`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `order_number` (`order_number`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `order_items`
--
ALTER TABLE `order_items`
  ADD PRIMARY KEY (`id`),
  ADD KEY `order_id` (`order_id`),
  ADD KEY `product_id` (`product_id`);

--
-- Indexes for table `products`
--
ALTER TABLE `products`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `slug` (`slug`),
  ADD KEY `category_id` (`category_id`);

--
-- Indexes for table `product_images`
--
ALTER TABLE `product_images`
  ADD PRIMARY KEY (`id`),
  ADD KEY `product_id` (`product_id`);

--
-- Indexes for table `reviews`
--
ALTER TABLE `reviews`
  ADD PRIMARY KEY (`id`),
  ADD KEY `product_id` (`product_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `settings`
--
ALTER TABLE `settings`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `setting_key` (`setting_key`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indexes for table `wishlists`
--
ALTER TABLE `wishlists`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_wishlist` (`user_id`,`product_id`),
  ADD KEY `product_id` (`product_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `blog_categories`
--
ALTER TABLE `blog_categories`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `blog_posts`
--
ALTER TABLE `blog_posts`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `categories`
--
ALTER TABLE `categories`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `orders`
--
ALTER TABLE `orders`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `order_items`
--
ALTER TABLE `order_items`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT for table `products`
--
ALTER TABLE `products`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=26;

--
-- AUTO_INCREMENT for table `product_images`
--
ALTER TABLE `product_images`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=59;

--
-- AUTO_INCREMENT for table `reviews`
--
ALTER TABLE `reviews`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `settings`
--
ALTER TABLE `settings`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `wishlists`
--
ALTER TABLE `wishlists`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `blog_posts`
--
ALTER TABLE `blog_posts`
  ADD CONSTRAINT `blog_posts_ibfk_1` FOREIGN KEY (`category_id`) REFERENCES `blog_categories` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `orders`
--
ALTER TABLE `orders`
  ADD CONSTRAINT `orders_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `order_items`
--
ALTER TABLE `order_items`
  ADD CONSTRAINT `order_items_ibfk_1` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `order_items_ibfk_2` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `products`
--
ALTER TABLE `products`
  ADD CONSTRAINT `products_ibfk_1` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `product_images`
--
ALTER TABLE `product_images`
  ADD CONSTRAINT `product_images_ibfk_1` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `reviews`
--
ALTER TABLE `reviews`
  ADD CONSTRAINT `reviews_ibfk_1` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `reviews_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `wishlists`
--
ALTER TABLE `wishlists`
  ADD CONSTRAINT `wishlists_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `wishlists_ibfk_2` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
