DROP DATABASE IF EXISTS `buffet`;
CREATE DATABASE `buffet` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

USE buffet;

DROP TABLE IF EXISTS `users`;
CREATE TABLE `users` (
  `id` int NOT NULL AUTO_INCREMENT,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `is_admin` tinyint(1) DEFAULT '0', -- 檢查是不是管理員 如果是的話轉移到admin.php畫面
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `email` varchar(255) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO users(username,password,is_admin,email) VALUES
(11124235,'Zx0920520',1,'zx0989030601@gmail.com');

DROP TABLE IF EXISTS `unserved_orders`;
CREATE TABLE `unserved_orders` (
  `food_name` varchar(255) NOT NULL,
  `quantity` int NOT NULL,
  `id` int NOT NULL AUTO_INCREMENT,
  `table_number` int(2) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

DROP TABLE IF EXISTS `served_orders`;
CREATE TABLE `served_orders` (
  `food_name` varchar(255) NOT NULL,
  `quantity` int NOT NULL,
  `id` int NOT NULL AUTO_INCREMENT,
  `order_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `table_number` int(2) NOT NULL,
  `is_delivered` BOOLEAN DEFAULT 0, -- 0代表未送達，1代表已送達
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO served_orders (food_name, quantity, table_number) VALUES 
('烤雞腿', 1, 1);



DROP TABLE IF EXISTS `menu`;
CREATE TABLE `menu` (
  `id` int NOT NULL AUTO_INCREMENT,
  `food_name` varchar(255) NOT NULL,
  `price` int NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `menu` (`food_name`, `price`) VALUES
('板腱牛', 0),
('雪花牛', 0),
('肩胛牛', 0),
('牛舌片', 0),
('霜降牛肉', 0),
('梅花豬', 0),
('五花豬', 0),
('黑豬梅花', 0),
('松阪豬', 0),
('培根豬', 0),
('雞腿肉', 0),
('雞胸肉', 0),
('雞翅肉', 0),
('雞皮', 0),
('雞肉丸', 0),
('大白菜', 0),
('青江菜', 0),
('茼蒿', 0),
('菠菜', 0),
('高麗菜', 0),
('金針菇', 0),
('杏鮑菇', 0),
('香菇', 0),
('玉米', 0),
('豆腐', 0),
('油豆腐', 0),
('凍豆腐', 0),
('海帶結', 0),
('白蘿蔔', 0),
('南瓜', 0),
('紅薯', 0),
('山藥', 0),
('蓮藕', 0),
('絲瓜', 0),
('冬瓜', 0);

DROP TABLE IF EXISTS `tables`;
CREATE TABLE tables (
    table_number INT PRIMARY KEY,
    status ENUM('vacant', 'reserved', 'occupied') DEFAULT 'vacant',
    reservation_time DATETIME,
    check_in_time DATETIME,
    diners_count INT DEFAULT 0,
    total_amount DECIMAL(10, 2) DEFAULT 0,
    Last_name varchar(255) DEFAULT '',
    phone_number VARCHAR(15) DEFAULT '0900-000000'
);

INSERT INTO tables (table_number, status, reservation_time, check_in_time)
VALUES (1, 'vacant', NULL, NULL),
       (2, 'vacant',NULL, NULL),
       (3, 'vacant', NULL, NULL),
       (4, 'vacant', NULL, NULL),
       (5, 'vacant', NULL, NULL),
       (6, 'vacant', NULL, NULL),
       (7, 'vacant', NULL, NULL),
       (8, 'vacant', NULL, NULL),
       (9, 'vacant', NULL, NULL),
       (10, 'vacant', NULL, NULL);
