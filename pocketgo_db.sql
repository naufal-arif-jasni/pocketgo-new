-- PocketGo Database Schema SQL Script
-- Import this into phpMyAdmin (pocketgo_db)

CREATE DATABASE IF NOT EXISTS `pocketgo_db` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `pocketgo_db`;

-- 1. Create Users Table
CREATE TABLE IF NOT EXISTS `users` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `name` VARCHAR(100) NOT NULL,
  `ic` VARCHAR(50) DEFAULT '',
  `email` VARCHAR(100) NOT NULL UNIQUE,
  `phone` VARCHAR(50) DEFAULT '',
  `child` VARCHAR(100) DEFAULT '',
  `childClass` VARCHAR(50) DEFAULT '',
  `studentId` VARCHAR(50) DEFAULT '',
  `card_serial` VARCHAR(50) DEFAULT '',
  `cards_json` TEXT DEFAULT NULL,
  `visa_card_json` TEXT DEFAULT NULL,
  `balance` DECIMAL(10,2) DEFAULT 0.00,
  `daily_limit` DECIMAL(10,2) DEFAULT 50.00,
  `status` VARCHAR(20) DEFAULT 'active',
  `password` VARCHAR(255) NOT NULL,
  `role` VARCHAR(20) DEFAULT 'parent',
  `topupTotal` DECIMAL(10,2) DEFAULT 0.00,
  `topupCount` INT DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 2. Create Students Table
CREATE TABLE IF NOT EXISTS `students` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `userId` INT NOT NULL,
  `student_id` VARCHAR(50) NOT NULL UNIQUE,
  `name` VARCHAR(100) NOT NULL,
  `class` VARCHAR(50) NOT NULL,
  FOREIGN KEY (`userId`) REFERENCES `users`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 3. Create Student Cards Table
CREATE TABLE IF NOT EXISTS `student_cards` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `student_id` VARCHAR(50) NOT NULL,
  `card_serial` VARCHAR(50) NOT NULL UNIQUE,
  `balance` DECIMAL(10,2) DEFAULT 0.00,
  `daily_limit` DECIMAL(10,2) DEFAULT 50.00,
  `status` VARCHAR(20) DEFAULT 'active',
  FOREIGN KEY (`student_id`) REFERENCES `students`(`student_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 4. Create Visa Cards Table
CREATE TABLE IF NOT EXISTS `visa_cards` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `userId` INT NOT NULL,
  `cardholder_name` VARCHAR(100) NOT NULL,
  `card_number` VARCHAR(50) NOT NULL,
  `expiry_date` VARCHAR(10) NOT NULL,
  FOREIGN KEY (`userId`) REFERENCES `users`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 5. Create Payments Table (D2: Payment File Data Store)
CREATE TABLE IF NOT EXISTS `payments` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `userId` INT NOT NULL,
  `card_serial` VARCHAR(50) NOT NULL,
  `amount` DECIMAL(10,2) NOT NULL,
  `method` VARCHAR(50) NOT NULL,
  `date` VARCHAR(50) NOT NULL,
  `status` VARCHAR(20) DEFAULT 'Completed',
  FOREIGN KEY (`userId`) REFERENCES `users`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 6. Create Transactions Table (D1: Transaction File Data Store)
CREATE TABLE IF NOT EXISTS `transactions` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `userId` INT NOT NULL,
  `description` VARCHAR(255) NOT NULL,
  `amount` DECIMAL(10,2) NOT NULL,
  `date` VARCHAR(50) NOT NULL,
  `type` VARCHAR(20) NOT NULL, -- 'topup' or 'spend'
  `icon` VARCHAR(10) NOT NULL,
  `cat` VARCHAR(50) NOT NULL,
  `title` VARCHAR(100) NOT NULL,
  `sub` VARCHAR(100) NOT NULL,
  FOREIGN KEY (`userId`) REFERENCES `users`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 7. Create Reports Table
CREATE TABLE IF NOT EXISTS `reports` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `reporterName` VARCHAR(100) NOT NULL,
  `child` VARCHAR(100) NOT NULL,
  `type` VARCHAR(50) NOT NULL,
  `subject` VARCHAR(100) NOT NULL,
  `description` TEXT NOT NULL,
  `status` VARCHAR(20) DEFAULT 'Open',
  `createdAt` VARCHAR(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 8. Seed Data
-- Insert Users
INSERT INTO `users` (`id`, `name`, `ic`, `email`, `phone`, `child`, `childClass`, `studentId`, `card_serial`, `cards_json`, `visa_card_json`, `balance`, `daily_limit`, `status`, `password`, `role`, `topupTotal`, `topupCount`) VALUES
(1, 'Ahmad Bin Abdullah', '850101-10-5432', 'ahmad@email.com', '012-3456789', 'Muhammad Faris', '4 Amanah', 'PG-40124', '1000000001', '[{"card_serial":"1000000001","student_name":"Muhammad Faris","student_id":"PG-40124","class":"4 Amanah","balance":120.50,"daily_limit":50.00,"status":"active"}]', '{"cardholder_name":"Ahmad Bin Abdullah","card_number":"•••• 4321","expiry_date":"12/28"}', 120.50, 50.00, 'active', 'password123', 'parent', 100.00, 2),
(2, 'Siti Binti Hassan', '870312-08-6112', 'siti@email.com', '013-9876543', 'Nur Aisyah', '3 Bestari', 'PG-30125', '1000000002', '[{"card_serial":"1000000002","student_name":"Nur Aisyah","student_id":"PG-30125","class":"3 Bestari","balance":85.50,"daily_limit":30.00,"status":"active"}]', NULL, 85.50, 30.00, 'active', 'password123', 'parent', 50.00, 1),
(3, 'Roslan Bin Bakar', '820421-14-5331', 'roslan@email.com', '019-4567890', 'Ahmad Daniel', '5 Cemerlang', 'PG-50126', '1000000003', '[{"card_serial":"1000000003","student_name":"Ahmad Daniel","student_id":"PG-50126","class":"5 Cemerlang","balance":200.00,"daily_limit":50.00,"status":"active"}]', NULL, 200.00, 50.00, 'active', 'password123', 'parent', 150.00, 3),
(4, 'Zainab Binti Mohd', '880614-10-5882', 'zainab@email.com', '017-2345678', 'Umar Hakim', '2 Dedikasi', 'PG-20127', '1000000004', '[{"card_serial":"1000000004","student_name":"Umar Hakim","student_id":"PG-20127","class":"2 Dedikasi","balance":45.00,"daily_limit":20.00,"status":"active"}]', NULL, 45.00, 20.00, 'inactive', 'password123', 'parent', 0.00, 0);

-- Insert Admin Account
INSERT INTO `users` (`id`, `name`, `ic`, `email`, `phone`, `child`, `childClass`, `studentId`, `balance`, `daily_limit`, `status`, `password`, `role`, `topupTotal`, `topupCount`) VALUES
(5, 'Administrator', '', 'Admin1', '', '', '', '', 0.00, 0.00, 'active', '12345', 'admin', 0.00, 0);

-- Seed Students
INSERT INTO `students` (`id`, `userId`, `student_id`, `name`, `class`) VALUES
(1, 1, 'PG-40124', 'Muhammad Faris', '4 Amanah'),
(2, 2, 'PG-30125', 'Nur Aisyah', '3 Bestari'),
(3, 3, 'PG-50126', 'Ahmad Daniel', '5 Cemerlang'),
(4, 4, 'PG-20127', 'Umar Hakim', '2 Dedikasi');

-- Seed Student Cards
INSERT INTO `student_cards` (`id`, `student_id`, `card_serial`, `balance`, `daily_limit`, `status`) VALUES
(1, 'PG-40124', '1000000001', 120.50, 50.00, 'active'),
(2, 'PG-30125', '1000000002', 85.50, 30.00, 'active'),
(3, 'PG-50126', '1000000003', 200.00, 50.00, 'active'),
(4, 'PG-20127', '1000000004', 45.00, 20.00, 'active');

-- Seed Visa Cards
INSERT INTO `visa_cards` (`id`, `userId`, `cardholder_name`, `card_number`, `expiry_date`) VALUES
(1, 1, 'Ahmad Bin Abdullah', '•••• 4321', '12/28');

-- Seed Payments (D2: Payment File)
INSERT INTO `payments` (`id`, `userId`, `card_serial`, `amount`, `method`, `date`, `status`) VALUES
(1, 1, '1000000001', 50.00, 'FPX', '2026-06-22 09:14', 'Completed'),
(2, 2, '1000000002', 50.00, 'DuitNow QR', '2026-06-21 08:00', 'Completed'),
(3, 1, '1000000001', 50.00, 'Visa Card', '2026-06-18 10:30', 'Completed');

-- Seed Transactions (D1: Transaction File)
INSERT INTO `transactions` (`id`, `userId`, `description`, `amount`, `date`, `type`, `icon`, `cat`, `title`, `sub`) VALUES
(1, 1, 'Top Up via FPX', 50.00, '2026-06-22 09:14', 'topup', '⬆️', 'topup', 'Top Up via FPX', 'Maybank · 9:14 AM'),
(2, 1, 'Canteen - Nasi Lemak', -3.50, '2026-06-22 07:45', 'spend', '🍱', 'canteen', 'Canteen – Nasi Lemak', '7:45 AM'),
(3, 2, 'Top Up via DuitNow QR', 50.00, '2026-06-21 08:00', 'topup', '⬆️', 'topup', 'Top Up via DuitNow QR', '8:00 AM'),
(4, 2, 'School Bookshop', -12.00, '2026-06-21 14:10', 'spend', '📚', 'shop', 'School Bookshop', '2:10 PM'),
(5, 3, 'Canteen - Mee Goreng', -3.00, '2026-06-19 07:42', 'spend', '🍱', 'canteen', 'Canteen – Mee Goreng', '7:42 AM'),
(6, 1, 'Top Up via Credit Card', 50.00, '2026-06-18 10:30', 'topup', '⬆️', 'topup', 'Top Up via Credit Card', '10:30 AM');

-- Seed Reports
INSERT INTO `reports` (`id`, `reporterName`, `child`, `type`, `subject`, `description`, `status`, `createdAt`) VALUES
(1, 'Ahmad Bin Abdullah', 'Muhammad Faris', 'damaged', 'Card not scanning at canteen', 'The card stopped working at the canteen reader this morning, edges look cracked.', 'Open', '2026-06-20 09:12');
