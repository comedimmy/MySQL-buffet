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

INSERT INTO unserved_orders (food_name, quantity, table_number) VALUES 
('烤雞腿', 1, 1);



DROP TABLE IF EXISTS `menu`;
CREATE TABLE `menu` (
  `id` int NOT NULL AUTO_INCREMENT,
  `food_name` varchar(255) NOT NULL,
  `price` int NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO menu(food_name, price) VALUES
('烤雞腿', 0),
('炒牛肉片', 0),
('鹽烤鮭魚', 0),
('煎豬排', 0),
('香煎鱸魚', 0),
('鹽水雞胸肉', 0),
('燒烤羊排', 0),
('義大利香草雞肉串', 0),
('黑椒牛肉炒蘑菇', 0),
('酥炸雞翅', 0),
('義式香烤豬肉', 0),
('泰式檸檬草雞肉', 0),
('燻烤鴨胸肉', 0),
('蜂蜜芥末雞胸肉', 0),
('炸蝦', 0),
('芝士焗花椰菜', 0),
('糖醋排骨', 0),
('香煎牛排', 0),
('中式紅燒牛肉', 0),
('叉燒豬肉', 0),
('酥炸豆腐', 0),
('香菇炒時蔬', 0),
('蒜香小玉米', 0),
('炒時蔬（蘆筍、紅椒、玉米等)', 0),
('香烤番茄', 0),
('日式醬燒茄子', 0),
('綠豆芽炒菜心', 0),
('韓式泡菜炒飯', 0),
('炒時蔬米粉', 0),
('泰式生菜包', 0);


CREATE TABLE tables (
    table_number INT PRIMARY KEY,
    status ENUM('vacant', 'reserved', 'occupied') DEFAULT 'vacant',
    reservation_time DATETIME,
    check_in_time DATETIME,
    diners_count INT DEFAULT 0,
    total_amount DECIMAL(10, 2) DEFAULT 0
);

INSERT INTO tables (table_number, status, reservation_time, check_in_time)
VALUES (1, 'occupied', NULL, '2024-12-15 19:00:00'),
       (2, 'reserved', '2024-12-15 18:00:00', NULL),
       (3, 'vacant', NULL, NULL),
       (4, 'vacant', NULL, NULL),
       (5, 'vacant', NULL, NULL),
       (6, 'vacant', NULL, NULL),
       (7, 'vacant', NULL, NULL),
       (8, 'vacant', NULL, NULL),
       (9, 'vacant', NULL, NULL),
       (10, 'vacant', NULL, NULL),
