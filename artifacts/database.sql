CREATE DATABASE `zicht` /*!40100 COLLATE 'utf8_general_ci' */;

CREATE USER 'zicht'@'localhost' IDENTIFIED BY 'df5cce6f05e4b021a0ed2572d173229e';
GRANT SELECT, INSERT, DELETE, UPDATE  ON `zicht`.* TO 'zicht'@'localhost';

CREATE TABLE `zicht`.`email_subscriptions` (
	`id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
	`email` VARCHAR(255) NOT NULL,
	`name` VARCHAR(255) NULL,
	PRIMARY KEY (`id`)
) COLLATE='utf8_general_ci';