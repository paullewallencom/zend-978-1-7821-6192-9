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
)
