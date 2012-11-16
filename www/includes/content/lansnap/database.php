<?php
$settings_table = DB_PREFIX . 'lansnap_settings';

$db->run(<<<SQL
CREATE TABLE IF NOT EXISTS `$settings_table` (
	`setting_name` VARCHAR(50) NOT NULL,
	`setting_value` VARCHAR(255) NOT NULL
)
SQL
);

$ticket_table = DB_PREFIX . 'lansnap_tickets';

$db->run(<<<SQL
CREATE TABLE IF NOT EXISTS `$ticket_table` (
	`ticket_id` BIGINT(11) AUTO_INCREMENT,
	`name` VARCHAR(100),
	`price` FLOAT,
	PRIMARY KEY (`ticket_id`)
)
SQL
);

$user_table = DB_PREFIX . 'lansnap_users';

$db->run(<<<SQL
CREATE TABLE IF NOT EXISTS `$user_table` (
	`user_id` BIGINT(11) AUTO_INCREMENT,
	`username` VARCHAR(100),
	`first_name` VARCHAR(100),
	`last_name` VARCHAR(100),
	`email_address` VARCHAR(255),
	`ip_group` TINYINT(1),
	`ticket_type` BIGINT(11),
	`date_entered` BIGINT(11),
	`date_registered` BIGINT(11),
	`seat_number` VARCHAR(10),
	`password` VARCHAR(32),
	`status` TINYINT(1) NOT NULL DEFAULT 0,
	PRIMARY KEY (`user_id`)
)
SQL
);

$ip_table = DB_PREFIX . 'lansnap_addresses';

$db->run(<<<SQL
CREATE TABLE IF NOT EXISTS `$ip_table` (
	`entry_id` BIGINT(11) AUTO_INCREMENT,
	`user_id` BIGINT(11),
	`mac_address` VARCHAR(12),
	`ip_address` VARCHAR(15),
	`hostname` VARCHAR(50),
	`description` TEXT,
	`status` TINYINT(1) NOT NULL DEFAULT 0,
	`source` TINYINT(1) NOT NULL DEFAULT 0,
	`bytes_in` FLOAT NOT NULL DEFAULT 0,
	`bytes_out` FLOAT NOT NULL DEFAULT 0,
	`packets_in` FLOAT NOT NULL DEFAULT 0,
	`packets_out` FLOAT NOT NULL DEFAULT 0,
	`bound` TINYINT(1) NOT NULL DEFAULT 0,
	PRIMARY KEY (`entry_id`)
)
SQL
);

$domain_table = DB_PREFIX . 'lansnap_domains';

$db->run(<<<SQL
CREATE TABLE IF NOT EXISTS `$domain_table` ( 
  `name` varchar(255) default NULL, 
  `ttl` int(11) default NULL, 
  `rdtype` varchar(255) default NULL, 
  `rdata` varchar(255) default NULL 
)
SQL
);

$traffic_table = DB_PREFIX . 'lansnap_traffic';
$db->run(<<<SQL
CREATE TABLE IF NOT EXISTS `$traffic_table` ( 
	`entry_id` BIGINT(11) AUTO_INCREMENT,
	`ip_address` VARCHAR(15) NOT NULL,
	`timestamp` BIGINT(11),
	`bytes_in` FLOAT NOT NULL DEFAULT 0,
	`bytes_out` FLOAT NOT NULL DEFAULT 0,
	`packets_in` FLOAT NOT NULL DEFAULT 0,
	`packets_out` FLOAT NOT NULL DEFAULT 0,
	PRIMARY KEY (`entry_id`)
)
SQL
);

$survey_questions = DB_PREFIX . 'lansnap_survey';
$db->run(<<<SQL
CREATE TABLE IF NOT EXISTS `$survey_questions` (
	`question_id` BIGINT(11) AUTO_INCREMENT,
	`component_id` BIGINT(11) NOT NULL,
	`question` text,
	`question_type` TINYINT(1) NOT NULL DEFAULT 0,
	`answers` blob,
	PRIMARY KEY (`question_id`)
)
SQL
);

$survey_results = DB_PREFIX . 'lansnap_survey_results';

$db->run(<<<SQL
CREATE TABLE IF NOT EXISTS `$survey_results` (
	`entry_id` BIGINT(11) AUTO_INCREMENT,
	`user_id` BIGINT(11),
	`answers` blob,
	PRIMARY KEY (`entry_id`)
)
SQL
);

?>