-- Create product categories table
CREATE TABLE IF NOT EXISTS `product_categories_tb` (
  `category_id` int(11) NOT NULL AUTO_INCREMENT,
  `category_name` varchar(100) NOT NULL,
  `category_description` text,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`category_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Insert some default categories
INSERT INTO `product_categories_tb` (`category_name`, `category_description`) VALUES
('Home Repairs', 'Products for home repair and maintenance'),
('Construction', 'Construction materials and tools'),
('Cleaning', 'Cleaning supplies and equipment'),
('Painting', 'Painting supplies and tools'),
('Handyman', 'General handyman tools and supplies'),
('Moving', 'Moving and packing supplies');

-- Enhance assets_tb table to include more product details
ALTER TABLE `assets_tb` 
ADD COLUMN `category_id` int(11) DEFAULT NULL AFTER `pname`,
ADD COLUMN `description` text AFTER `psellingcost`,
ADD COLUMN `image_url` varchar(255) DEFAULT NULL AFTER `description`,
ADD COLUMN `featured` tinyint(1) NOT NULL DEFAULT '0' AFTER `image_url`,
ADD COLUMN `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP AFTER `featured`,
ADD FOREIGN KEY (`category_id`) REFERENCES `product_categories_tb`(`category_id`) ON DELETE SET NULL;

-- Create shopping cart table
CREATE TABLE IF NOT EXISTS `shopping_cart_tb` (
  `cart_id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `quantity` int(11) NOT NULL DEFAULT '1',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`cart_id`),
  KEY `user_id` (`user_id`),
  KEY `product_id` (`product_id`),
  CONSTRAINT `shopping_cart_tb_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `requesterlogin_tb` (`r_login_id`) ON DELETE CASCADE,
  CONSTRAINT `shopping_cart_tb_ibfk_2` FOREIGN KEY (`product_id`) REFERENCES `assets_tb` (`pid`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Create orders table
CREATE TABLE IF NOT EXISTS `orders_tb` (
  `order_id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `total_amount` decimal(10,2) NOT NULL,
  `shipping_address` text NOT NULL,
  `shipping_city` varchar(100) NOT NULL,
  `shipping_state` varchar(100) NOT NULL,
  `shipping_zip` varchar(20) NOT NULL,
  `shipping_phone` varchar(20) NOT NULL,
  `payment_method` varchar(50) NOT NULL,
  `payment_status` varchar(50) NOT NULL DEFAULT 'pending',
  `order_status` varchar(50) NOT NULL DEFAULT 'pending',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`order_id`),
  KEY `user_id` (`user_id`),
  CONSTRAINT `orders_tb_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `requesterlogin_tb` (`r_login_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Create order items table
CREATE TABLE IF NOT EXISTS `order_items_tb` (
  `item_id` int(11) NOT NULL AUTO_INCREMENT,
  `order_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `quantity` int(11) NOT NULL,
  `price` decimal(10,2) NOT NULL,
  PRIMARY KEY (`item_id`),
  KEY `order_id` (`order_id`),
  KEY `product_id` (`product_id`),
  CONSTRAINT `order_items_tb_ibfk_1` FOREIGN KEY (`order_id`) REFERENCES `orders_tb` (`order_id`) ON DELETE CASCADE,
  CONSTRAINT `order_items_tb_ibfk_2` FOREIGN KEY (`product_id`) REFERENCES `assets_tb` (`pid`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4; 