-- SQL for creating payments table in WordPress
-- Run this SQL in your database to store payment records
-- Note: Replace 'wp_' with your actual WordPress table prefix if different

CREATE TABLE IF NOT EXISTS `wp_payments` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `added_date` datetime DEFAULT NULL,
  `ip_address` varchar(50) DEFAULT NULL,
  `name` varchar(255) DEFAULT NULL,
  `email` varchar(255) NOT NULL,
  `phone` varchar(50) DEFAULT NULL,
  `amount` decimal(10,2) NOT NULL,
  `currency` varchar(10) NOT NULL DEFAULT 'AED',
  `message` text DEFAULT NULL,
  `payment_id` varchar(255) DEFAULT NULL,
  `session_id` varchar(255) DEFAULT NULL,
  `payment_method` varchar(50) DEFAULT 'stripe',
  `payment_status` enum('pending','completed','failed','refunded') NOT NULL DEFAULT 'pending',
  `webhook_received` tinyint(1) DEFAULT '0',
  `created_at` datetime DEFAULT NULL,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `payment_id` (`payment_id`),
  KEY `email` (`email`),
  KEY `payment_status` (`payment_status`),
  KEY `added_date` (`added_date`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
