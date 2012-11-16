<?php
$layout_table = DB_PREFIX . 'lansnap_layouts';
$table_table  = DB_PREFIX . 'lansnap_layout_tables';
$seat_table   = DB_PREFIX . 'lansnap_layout_seats';

$db->run(<<<SQL
CREATE TABLE IF NOT EXISTS `$layout_table` (
	`layout_id` BIGINT(11) AUTO_INCREMENT,
	`component_id` BIGINT(11) NOT NULL,
	`name` VARCHAR(50),
	`status` TINYINT(1) NOT NULL DEFAULT 0,
	`active` TINYINT(1) NOT NULL DEFAULT 0,
	`width` INT(5),
	`height` INT(5),
	PRIMARY KEY (`layout_id`) 
)
SQL
);

$db->run(<<<SQL
CREATE TABLE IF NOT EXISTS `$table_table` (
	`table_id` BIGINT(11) AUTO_INCREMENT,
	`layout_id` BIGINT(11) NOT NULL,
	`row` VARCHAR(50),
	`label` VARCHAR(50),
	`table_type` VARCHAR(50),
	`num_seats` INT(3),
	`pos_x` INT(9),
	`pos_y` INT(9),
	PRIMARY KEY (`table_id`)
)
SQL
);

$db->run(<<<SQL
CREATE TABLE IF NOT EXISTS `$seat_table` (
	`seat_id` BIGINT(11) AUTO_INCREMENT,
	`table_id` BIGINT(11) NOT NULL,
	`layout_id` BIGINT(11) NOT NULL,
	`seat_num` VARCHAR(50),
	`user_id` BIGINT(11) NOT NULL DEFAULT 0,
	`table_seat_number` INT(3),
	PRIMARY KEY (`seat_id`)
)
SQL
);
?>