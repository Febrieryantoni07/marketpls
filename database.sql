-- ===================================================================
-- DATABASE MARKETPLACE RAKET BADMINTON (SMASHZONE)
-- Mendukung PHP Native, MySQLi, XAMPP, dan REST API (Soal 11 & Soal 12)
-- ===================================================================

CREATE DATABASE IF NOT EXISTS `badminton_marketplace` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `badminton_marketplace`;

-- 1. TABEL PENGGUNA (USERS)
CREATE TABLE IF NOT EXISTS `users` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `name` VARCHAR(100) NOT NULL,
    `email` VARCHAR(100) NOT NULL UNIQUE,
    `password` VARCHAR(255) NOT NULL,
    `role` ENUM('seller', 'customer') DEFAULT 'customer',
    `phone` VARCHAR(25) NULL,
    `address` TEXT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 2. TABEL KATEGORI (CATEGORIES)
CREATE TABLE IF NOT EXISTS `categories` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `name` VARCHAR(60) NOT NULL,
    `slug` VARCHAR(60) NOT NULL UNIQUE,
    `description` VARCHAR(255) NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 3. TABEL PRODUK (PRODUCTS)
CREATE TABLE IF NOT EXISTS `products` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `seller_id` INT NOT NULL,
    `category_id` INT NOT NULL,
    `name` VARCHAR(150) NOT NULL,
    `brand` VARCHAR(60) NOT NULL,
    `price` DECIMAL(12, 2) NOT NULL,
    `stock` INT NOT NULL DEFAULT 0,
    `weight_grip` VARCHAR(20) DEFAULT '4U/G5',
    `tension_lbs` VARCHAR(30) DEFAULT '24 - 30 LBS',
    `balance_point` VARCHAR(50) DEFAULT 'Head Heavy',
    `flexibility` VARCHAR(50) DEFAULT 'Medium Stiff',
    `description` TEXT NULL,
    `image` VARCHAR(255) DEFAULT 'default_racket.svg',
    `is_featured` TINYINT(1) DEFAULT 0,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT `fk_products_seller` FOREIGN KEY (`seller_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
    CONSTRAINT `fk_products_category` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 4. TABEL PESANAN (ORDERS)
CREATE TABLE IF NOT EXISTS `orders` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `invoice_code` VARCHAR(50) NOT NULL UNIQUE,
    `customer_name` VARCHAR(100) NOT NULL,
    `customer_email` VARCHAR(100) NOT NULL,
    `customer_phone` VARCHAR(30) NOT NULL,
    `shipping_address` TEXT NOT NULL,
    `courier` VARCHAR(50) NOT NULL DEFAULT 'JNE Regular',
    `payment_method` VARCHAR(50) NOT NULL DEFAULT 'Transfer Bank BCA',
    `total_amount` DECIMAL(12, 2) NOT NULL,
    `status` ENUM('pending', 'paid', 'shipped', 'completed', 'cancelled') DEFAULT 'pending',
    `notes` TEXT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 5. TABEL DETAIL PESANAN (ORDER_ITEMS)
CREATE TABLE IF NOT EXISTS `order_items` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `order_id` INT NOT NULL,
    `product_id` INT NOT NULL,
    `product_name` VARCHAR(150) NOT NULL,
    `price` DECIMAL(12, 2) NOT NULL,
    `quantity` INT NOT NULL DEFAULT 1,
    `subtotal` DECIMAL(12, 2) NOT NULL,
    CONSTRAINT `fk_order_items_order` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE,
    CONSTRAINT `fk_order_items_product` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 6. TABEL ULASAN PRODUK (REVIEWS)
CREATE TABLE IF NOT EXISTS `reviews` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `product_id` INT NOT NULL,
    `user_name` VARCHAR(100) NOT NULL,
    `rating` TINYINT(1) NOT NULL DEFAULT 5,
    `comment` TEXT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT `fk_reviews_product` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ===================================================================
-- DATA AWAL (SEEDER DATA)
-- ===================================================================

-- Seeder Kategori
INSERT INTO `categories` (`id`, `name`, `slug`, `description`) VALUES
(1, 'Power & Smash (Head Heavy)', 'power-smash', 'Raket dengan bobot condong ke kepala untuk menghasilkan smash keras dan bertenaga.'),
(2, 'Speed & Drive (Head Light)', 'speed-drive', 'Raket lincah dengan frame aerodinamis untuk rally cepat dan permainan ganda lincah.'),
(3, 'All-Around & Control (Even Balance)', 'all-around', 'Keseimbangan sempurna antara daya serang dan pertahanan dengan akurasi kontrol tinggi.'),
(4, 'Beginner & All Level', 'beginner-friendly', 'Raket fleksibel yang ramah untuk pemula dan pemain rekreasional dengan sweet spot luas.');

-- Seeder Pengguna (Password hash bcrypt untuk: password123)
INSERT INTO `users` (`id`, `name`, `email`, `password`, `role`, `phone`, `address`) VALUES
(1, 'Official SmashZone Store', 'admin@smashzone.com', '$2y$10$IIjOybmrjIBQZH4QweDfYePzksj1U88zFFbwjGCwkP5xwAXCH3TOm', 'seller', '081234567890', 'Jl. Bulutangkis No. 10, Jakarta Selatan'),
(2, 'Budi Santoso', 'budi@gmail.com', '$2y$10$IIjOybmrjIBQZH4QweDfYePzksj1U88zFFbwjGCwkP5xwAXCH3TOm', 'customer', '089876543210', 'Jl. Merdeka Barat No. 45, Bandung');

-- Seeder Produk Raket Badminton Original
INSERT INTO `products` (`id`, `seller_id`, `category_id`, `name`, `brand`, `price`, `stock`, `weight_grip`, `tension_lbs`, `balance_point`, `flexibility`, `description`, `image`, `is_featured`) VALUES
(1, 1, 1, 'Yonex Astrox 88D Pro (3rd Gen)', 'Yonex', 2850000.00, 15, '4U/G5', '20 - 28 LBS', 'Head Heavy (305mm)', 'Stiff', 'Raket andalan pemain ganda belakang level dunia. Memberikan daya hancur smash maksimal dengan teknologi Rotational Generator System Plus dan 2G-Namd Flex Force.', 'racket_astrox_88d.svg', 1),
(2, 1, 2, 'Yonex Nanoflare 1000Z', 'Yonex', 2950000.00, 12, '4U/G5', '20 - 28 LBS', 'Head Light (290mm)', 'Extra Stiff', 'Pemegang rekor dunia smash tercepat. Menawarkan kecepatan ayunan luar biasa (Sonic Flare System) untuk drive kilat dan intercept di depan net.', 'racket_nanoflare_1000z.svg', 1),
(3, 1, 1, 'Victor Thruster Ryuga II Pro', 'Victor', 2650000.00, 10, '4U/G5', 'Max 31 LBS', 'Head Heavy (308mm)', 'Stiff', 'Senjata andalan Lee Zii Jia. Menggunakan teknologi WES 2.0 dan Pyrofil carbon fiber untuk smash tajam menusuk ke bidang lawan.', 'racket_ryuga_2.svg', 1),
(4, 1, 2, 'Victor Auraspeed 100X Ultra', 'Victor', 2550000.00, 14, '4U/G5', 'Max 29 LBS', 'Even Balance (295mm)', 'Medium Stiff', 'Raket pilihan Mohammad Ahsan & Hendra Setiawan. Manuver lincah, pengembalian bola akurat, dan kontrol rally netting yang sangat halus.', 'racket_auraspeed_100x.svg', 1),
(5, 1, 3, 'Li-Ning Halbertec 8000', 'Li-Ning', 2750000.00, 8, '4U/G5', 'Max 30 LBS', 'Even Balance (298mm)', 'Medium', 'Raket legendaris Yuta Watanabe. Memadukan presisi drop shot dan drive yang sangat stabil dengan frame T1100 carbon composite.', 'racket_halbertec_8000.svg', 1),
(6, 1, 1, 'Li-Ning Aeronaut 9000C', 'Li-Ning', 2450000.00, 9, '3U/G5', 'Max 32 LBS', 'Head Heavy (305mm)', 'Medium', 'Dilengkapi 4 slot ventilasi udara terintegrasi untuk mengurangi hambatan angin saat melakukan full smash bertenaga tinggi.', 'racket_aeronaut_9000c.svg', 0),
(7, 1, 3, 'Mizuno Fortius 11 Power', 'Mizuno', 2400000.00, 11, '4U/G5', 'Max 29 LBS', 'Head Heavy (300mm)', 'Stiff', 'Dibuat di Jepang dengan material Beyond Force System untuk transfer energi yang padat pada setiap pukulan lob dan smash.', 'racket_mizuno_fortius.svg', 0),
(8, 1, 4, 'Felet Woven TJ 1000 Power', 'Felet', 1650000.00, 20, '4U/G5', 'Max 35 LBS', 'Head Heavy (295mm)', 'Medium', 'Raket kuat tahan tarikan ekstrem hingga 35 LBS. Menggunakan bahan Woven carbon fiber premium, sangat awet dan bertenaga.', 'racket_felet_tj1000.svg', 0);

-- Seeder Transaksi Contoh
INSERT INTO `orders` (`id`, `invoice_code`, `customer_name`, `customer_email`, `customer_phone`, `shipping_address`, `courier`, `payment_method`, `total_amount`, `status`, `notes`) VALUES
(1, 'INV-202611-001', 'Budi Santoso', 'budi@gmail.com', '089876543210', 'Jl. Merdeka Barat No. 45, Coblong, Kota Bandung, Jawa Barat 40132', 'JNE Regular', 'Transfer Bank BCA', 2850000.00, 'completed', 'Tolong pasang senar BG66 Ultimax 27 LBS jika bisa');

INSERT INTO `order_items` (`id`, `order_id`, `product_id`, `product_name`, `price`, `quantity`, `subtotal`) VALUES
(1, 1, 1, 'Yonex Astrox 88D Pro (3rd Gen)', 2850000.00, 1, 2850000.00);

-- Seeder Reviews
INSERT INTO `reviews` (`product_id`, `user_name`, `rating`, `comment`) VALUES
(1, 'Taufik H.', 5, 'Smash terasa sangat padat dan bertenaga! Raket 100% original, pengemasan dengan pipa PVC sangat aman.'),
(2, 'Kevin Sanjaya Fan', 5, 'Drive depan net sangat cepat dan tidak ada delay swing sama sekali. Mantap banget buat main ganda!'),
(3, 'Rian Ardianto', 5, 'Daya tahan frame sangat kokoh, tarikan 29 lbs masih sangat stabil.'),
(4, 'Ahsan Lover', 5, 'Kontrol netting sangat presisi dan sweetspot terasa nyaman di tangan.');
