--
-- User Module Setup
--

CREATE TABLE IF NOT EXISTS `user` (
  `id` INTEGER UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` TEXT NOT NULL,
  `email` VARCHAR(255) NOT NULL,
  `password` TEXT NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE INDEX `idx_email`(`email`)
);

CREATE TABLE IF NOT EXISTS `uploads` (
	`id` INT NOT NULL AUTO_INCREMENT PRIMARY KEY ,
	`filename` VARCHAR( 255 ) NOT NULL ,
	`label` VARCHAR( 255 ) NOT NULL ,
	`user_id` INT NOT NULL,
	UNIQUE KEY (`filename`)
);

CREATE TABLE IF NOT EXISTS `uploads_sharing` (
	`id` INT NOT NULL AUTO_INCREMENT PRIMARY KEY ,
	`upload_id` INT NOT NULL ,
	`user_id` INT NOT NULL,
	UNIQUE KEY (`upload_id`, `user_id`)
);

CREATE TABLE IF NOT EXISTS `chat_messages` (
	`id` INT NOT NULL AUTO_INCREMENT PRIMARY KEY ,
	`user_id` INT NOT NULL,
	`message` VARCHAR( 255 ) NOT NULL ,	
	`stamp` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS `image_uploads` (
	`id` INT NOT NULL AUTO_INCREMENT PRIMARY KEY ,
	`filename` VARCHAR( 255 ) NOT NULL ,
	`thumbnail` VARCHAR( 255 ) NOT NULL ,
	`label` VARCHAR( 255 ) NOT NULL ,
	`user_id` INT NOT NULL,
	UNIQUE KEY (`filename`)
);

-- Store / Products
CREATE TABLE IF NOT EXISTS `store_products` (
	`id` int(11) NOT NULL AUTO_INCREMENT,
	`name` varchar(255) NOT NULL,
	`desc` varchar(255) NOT NULL,
	`cost` float(9,2) NOT NULL,
	PRIMARY KEY (`id`)
);

-- Store / Orders
CREATE TABLE IF NOT EXISTS `store_orders` (
	`id` int(11) NOT NULL AUTO_INCREMENT,
	`store_product_id` int(11) NOT NULL,
	`qty` int(11) NOT NULL,
	`total` float(9,2) NOT NULL,
	`status` enum('new','completed','shipped','cancelled') DEFAULT NULL,
	`stamp` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
	`first_name` varchar(255) DEFAULT NULL,
	`last_name` varchar(255) DEFAULT NULL,
	`email` varchar(255) DEFAULT NULL,
	`ship_to_street` varchar(255) DEFAULT NULL,
	`ship_to_city` varchar(255) DEFAULT NULL,
	`ship_to_state` varchar(2) DEFAULT NULL,
	`ship_to_zip` int(11) DEFAULT NULL,
	PRIMARY KEY (`id`)
);

