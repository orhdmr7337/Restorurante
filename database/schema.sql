-- =====================================================
-- RESTAURANT ERP SYSTEM - FULL DATABASE SCHEMA
-- Version: 2.0.0
-- Date: 2025-10-25
-- =====================================================

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";
SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

-- =====================================================
-- 1. STOK YÖNETİMİ TABLOLARI
-- =====================================================

-- Malzemeler
CREATE TABLE IF NOT EXISTS `materials` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `unit` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'kg, lt, adet, paket',
  `current_stock` decimal(10,2) DEFAULT 0.00,
  `min_stock` decimal(10,2) DEFAULT 0.00,
  `cost_price` decimal(10,2) DEFAULT 0.00,
  `status` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_name` (`name`),
  KEY `idx_status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Stok Hareketleri
CREATE TABLE IF NOT EXISTS `stock_movements` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `material_id` int(11) unsigned NOT NULL,
  `type` enum('in','out') COLLATE utf8mb4_unicode_ci NOT NULL,
  `quantity` decimal(10,2) NOT NULL,
  `reference_type` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'purchase, order, adjustment',
  `reference_id` int(11) DEFAULT NULL,
  `user_id` int(11) unsigned DEFAULT NULL,
  `notes` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `fk_stock_material` (`material_id`),
  KEY `idx_type` (`type`),
  KEY `idx_reference` (`reference_type`,`reference_id`),
  CONSTRAINT `fk_stock_material` FOREIGN KEY (`material_id`) REFERENCES `materials` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Reçeteler (Ürün-Malzeme İlişkisi)
CREATE TABLE IF NOT EXISTS `recipes` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `product_id` int(11) NOT NULL,
  `material_id` int(11) unsigned NOT NULL,
  `quantity` decimal(10,2) NOT NULL COMMENT 'Kullanılan miktar',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `fk_recipe_product` (`product_id`),
  KEY `fk_recipe_material` (`material_id`),
  CONSTRAINT `fk_recipe_material` FOREIGN KEY (`material_id`) REFERENCES `materials` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_recipe_product` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- 2. TEDARİKÇİ YÖNETİMİ TABLOLARI
-- =====================================================

-- Tedarikçiler
CREATE TABLE IF NOT EXISTS `suppliers` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `contact_person` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `phone` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `email` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `address` text COLLATE utf8mb4_unicode_ci,
  `tax_number` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `iban` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `balance` decimal(10,2) DEFAULT 0.00 COMMENT 'Borç bakiyesi',
  `status` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_name` (`name`),
  KEY `idx_status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Satın Alma İşlemleri (Alış Faturaları)
CREATE TABLE IF NOT EXISTS `purchases` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `supplier_id` int(11) unsigned NOT NULL,
  `invoice_number` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `total_amount` decimal(10,2) DEFAULT 0.00,
  `tax_amount` decimal(10,2) DEFAULT 0.00,
  `payment_status` enum('unpaid','partial','paid') COLLATE utf8mb4_unicode_ci DEFAULT 'unpaid',
  `purchase_date` date NOT NULL,
  `due_date` date DEFAULT NULL,
  `user_id` int(11) unsigned DEFAULT NULL,
  `notes` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `fk_purchase_supplier` (`supplier_id`),
  KEY `idx_payment_status` (`payment_status`),
  KEY `idx_purchase_date` (`purchase_date`),
  CONSTRAINT `fk_purchase_supplier` FOREIGN KEY (`supplier_id`) REFERENCES `suppliers` (`id`) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Satın Alma Detayları
CREATE TABLE IF NOT EXISTS `purchase_items` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `purchase_id` int(11) unsigned NOT NULL,
  `material_id` int(11) unsigned NOT NULL,
  `quantity` decimal(10,2) NOT NULL,
  `unit_price` decimal(10,2) NOT NULL,
  `total_price` decimal(10,2) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `fk_purchase_item_purchase` (`purchase_id`),
  KEY `fk_purchase_item_material` (`material_id`),
  CONSTRAINT `fk_purchase_item_material` FOREIGN KEY (`material_id`) REFERENCES `materials` (`id`) ON DELETE RESTRICT,
  CONSTRAINT `fk_purchase_item_purchase` FOREIGN KEY (`purchase_id`) REFERENCES `purchases` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- 3. CARİ HESAP & BORÇ TAKİBİ TABLOLARI
-- =====================================================

-- Cari Hesaplar
CREATE TABLE IF NOT EXISTS `accounts` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `type` enum('customer','supplier') COLLATE utf8mb4_unicode_ci NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `phone` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `email` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `address` text COLLATE utf8mb4_unicode_ci,
  `tax_number` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `balance` decimal(10,2) DEFAULT 0.00 COMMENT 'Pozitif: Alacak, Negatif: Borç',
  `credit_limit` decimal(10,2) DEFAULT 0.00,
  `status` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_type` (`type`),
  KEY `idx_name` (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Cari Hareketler
CREATE TABLE IF NOT EXISTS `account_transactions` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `account_id` int(11) unsigned NOT NULL,
  `type` enum('debit','credit') COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'debit: borç, credit: alacak',
  `amount` decimal(10,2) NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `reference_type` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `reference_id` int(11) DEFAULT NULL,
  `transaction_date` date NOT NULL,
  `user_id` int(11) unsigned DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `fk_transaction_account` (`account_id`),
  KEY `idx_transaction_date` (`transaction_date`),
  CONSTRAINT `fk_transaction_account` FOREIGN KEY (`account_id`) REFERENCES `accounts` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Borç Ödemeleri
CREATE TABLE IF NOT EXISTS `debt_payments` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `account_id` int(11) unsigned NOT NULL,
  `amount` decimal(10,2) NOT NULL,
  `payment_method` enum('cash','card','bank','other') COLLATE utf8mb4_unicode_ci DEFAULT 'cash',
  `payment_date` date NOT NULL,
  `notes` text COLLATE utf8mb4_unicode_ci,
  `user_id` int(11) unsigned DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `fk_payment_account` (`account_id`),
  KEY `idx_payment_date` (`payment_date`),
  CONSTRAINT `fk_payment_account` FOREIGN KEY (`account_id`) REFERENCES `accounts` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- 4. MUHASEBE TABLOLARI
-- =====================================================

-- Gelirler
CREATE TABLE IF NOT EXISTS `incomes` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `category` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Satış, Diğer',
  `amount` decimal(10,2) NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `income_date` date NOT NULL,
  `payment_method` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `reference_type` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `reference_id` int(11) DEFAULT NULL,
  `user_id` int(11) unsigned DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_income_date` (`income_date`),
  KEY `idx_category` (`category`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Giderler
CREATE TABLE IF NOT EXISTS `expenses` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `category` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Kira, Maaş, Fatura, Malzeme',
  `amount` decimal(10,2) NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `expense_date` date NOT NULL,
  `payment_method` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `user_id` int(11) unsigned DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_expense_date` (`expense_date`),
  KEY `idx_category` (`category`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Kasa Hareketleri
CREATE TABLE IF NOT EXISTS `cash_transactions` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `type` enum('in','out') COLLATE utf8mb4_unicode_ci NOT NULL,
  `amount` decimal(10,2) NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `reference_type` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `reference_id` int(11) DEFAULT NULL,
  `transaction_date` date NOT NULL,
  `user_id` int(11) unsigned DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_transaction_date` (`transaction_date`),
  KEY `idx_type` (`type`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Banka Hareketleri
CREATE TABLE IF NOT EXISTS `bank_transactions` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `account_name` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `type` enum('in','out') COLLATE utf8mb4_unicode_ci NOT NULL,
  `amount` decimal(10,2) NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `reference_number` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `transaction_date` date NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_transaction_date` (`transaction_date`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- 5. PERSONEL & MAAŞ TABLOLARI
-- =====================================================

-- Roller
CREATE TABLE IF NOT EXISTS `roles` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `permissions` text COLLATE utf8mb4_unicode_ci COMMENT 'JSON format',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_role_name` (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Varsayılan Rolleri Ekle (eğer yoksa)
INSERT IGNORE INTO `roles` (`name`, `permissions`) VALUES
('Admin', '{"all": true}'),
('Garson', '{"orders": true, "tables": true}'),
('Kasiyer', '{"orders": true, "payments": true, "reports": true}'),
('Şef', '{"orders": true, "kitchen": true}'),
('Muhasebe', '{"finance": true, "reports": true, "suppliers": true, "stock": true}');

-- Users tablosuna yeni kolonlar ekle
ALTER TABLE `users` 
  ADD COLUMN `role_id` int(11) unsigned DEFAULT NULL AFTER `user_position`,
  ADD COLUMN `salary` decimal(10,2) DEFAULT NULL AFTER `role_id`,
  ADD COLUMN `hire_date` date DEFAULT NULL AFTER `salary`,
  ADD COLUMN `status` tinyint(1) DEFAULT 1 AFTER `hire_date`,
  ADD COLUMN `updated_at` timestamp NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP AFTER `status`;

-- Maaş Ödemeleri
CREATE TABLE IF NOT EXISTS `salary_payments` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` int(10) unsigned NOT NULL,
  `amount` decimal(10,2) NOT NULL,
  `payment_date` date NOT NULL,
  `period_month` int(2) NOT NULL,
  `period_year` int(4) NOT NULL,
  `notes` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `fk_salary_user` (`user_id`),
  KEY `idx_period` (`period_year`,`period_month`),
  CONSTRAINT `fk_salary_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Vardiyalar
CREATE TABLE IF NOT EXISTS `shifts` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` int(10) unsigned NOT NULL,
  `shift_date` date NOT NULL,
  `start_time` time NOT NULL,
  `end_time` time DEFAULT NULL,
  `notes` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `fk_shift_user` (`user_id`),
  KEY `idx_shift_date` (`shift_date`),
  CONSTRAINT `fk_shift_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- 6. BİLDİRİM & LOG TABLOLARI
-- =====================================================

-- Bildirimler
CREATE TABLE IF NOT EXISTS `notifications` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `type` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'order, stock, debt, report',
  `title` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `message` text COLLATE utf8mb4_unicode_ci,
  `user_id` int(10) unsigned DEFAULT NULL,
  `is_read` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_user_id` (`user_id`),
  KEY `idx_is_read` (`is_read`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Sistem Logları
CREATE TABLE IF NOT EXISTS `system_logs` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` int(10) unsigned DEFAULT NULL,
  `action` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `table_name` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `record_id` int(11) DEFAULT NULL,
  `old_value` text COLLATE utf8mb4_unicode_ci,
  `new_value` text COLLATE utf8mb4_unicode_ci,
  `ip_address` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_user_id` (`user_id`),
  KEY `idx_action` (`action`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- 7. API ENTEGRASYON TABLOLARI
-- =====================================================

-- API Ayarları
CREATE TABLE IF NOT EXISTS `api_settings` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `provider` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'getir, trendyol, qr_order',
  `api_key` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `api_secret` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT 0,
  `settings` text COLLATE utf8mb4_unicode_ci COMMENT 'JSON format',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_provider` (`provider`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dış Siparişler
CREATE TABLE IF NOT EXISTS `external_orders` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `provider` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `external_id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `order_data` text COLLATE utf8mb4_unicode_ci COMMENT 'JSON format',
  `status` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `total_amount` decimal(10,2) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_provider` (`provider`),
  KEY `idx_external_id` (`external_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- 8. LİSANS & AYARLAR TABLOLARI
-- =====================================================

-- Lisans
CREATE TABLE IF NOT EXISTS `license` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `license_key` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `company_name` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `activation_date` date DEFAULT NULL,
  `expiry_date` date DEFAULT NULL,
  `status` enum('active','expired','suspended') COLLATE utf8mb4_unicode_ci DEFAULT 'active',
  `max_users` int(11) DEFAULT 10,
  `features` text COLLATE utf8mb4_unicode_ci COMMENT 'JSON format',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_license_key` (`license_key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Sistem Ayarları
CREATE TABLE IF NOT EXISTS `settings` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `setting_key` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `setting_value` text COLLATE utf8mb4_unicode_ci,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_setting_key` (`setting_key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Varsayılan Ayarlar (eğer yoksa)
INSERT IGNORE INTO `settings` (`setting_key`, `setting_value`) VALUES
('app_name', 'Restaurant ERP'),
('currency', 'TRY'),
('tax_rate', '18'),
('qr_order_enabled', '0'),
('low_stock_alert', '1'),
('debt_alert_days', '7'),
('email_notifications', '1');

-- =====================================================
-- 9. MEVCUT TABLOLARI GÜNCELLE
-- =====================================================

-- Orders tablosuna yeni kolonlar
ALTER TABLE `orders` 
  ADD COLUMN `order_type` enum('dine_in','takeaway','delivery','external') DEFAULT 'dine_in' AFTER `status`,
  ADD COLUMN `external_provider` varchar(50) DEFAULT NULL AFTER `order_type`,
  ADD COLUMN `updated_at` timestamp NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP AFTER `created_at`;

-- Products tablosuna yeni kolonlar
ALTER TABLE `products` 
  ADD COLUMN `image` varchar(255) DEFAULT NULL AFTER `price`,
  ADD COLUMN `description` text DEFAULT NULL AFTER `image`,
  ADD COLUMN `is_active` tinyint(1) DEFAULT 1 AFTER `description`,
  ADD COLUMN `created_at` timestamp DEFAULT CURRENT_TIMESTAMP AFTER `is_active`,
  ADD COLUMN `updated_at` timestamp NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP AFTER `created_at`;

-- Product Categories tablosuna yeni kolonlar
ALTER TABLE `product_categories` 
  ADD COLUMN `image` varchar(255) DEFAULT NULL AFTER `name`,
  ADD COLUMN `sort_order` int(11) DEFAULT 0 AFTER `image`,
  ADD COLUMN `is_active` tinyint(1) DEFAULT 1 AFTER `sort_order`,
  ADD COLUMN `created_at` timestamp DEFAULT CURRENT_TIMESTAMP AFTER `is_active`,
  ADD COLUMN `updated_at` timestamp NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP AFTER `created_at`;

SET FOREIGN_KEY_CHECKS = 1;

-- =====================================================
-- SCHEMA COMPLETED
-- =====================================================
