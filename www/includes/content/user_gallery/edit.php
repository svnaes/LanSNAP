<?php
// nothing here!

$user_image_table = DB_PREFIX . 'user_images';

$db->run(<<<SQL
CREATE TABLE IF NOT EXISTS `$user_image_table` (
	`image_id` BIGINT(11) AUTO_INCREMENT,
	`filename` TEXT,
	`component_id` BIGINT(11),
	`timestamp` BIGINT(11),
	`ip_address` TEXT,
	`md5` VARCHAR(32),
	PRIMARY KEY(`image_id`)
)
SQL
);



?>