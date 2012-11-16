<?php

// the purpose of this file is to ensure that the database tables for the blog are set up properly

if ( (!defined('USER_ACCESS')) or (USER_ACCESS < 3) )
{
	exit();
}

$blog_options    = DB_PREFIX . 'pico_blog_options';
$blog_entries    = DB_PREFIX . 'pico_blog_entries';
$blog_categories = DB_PREFIX . 'pico_blog_categories';
$blog_comments   = DB_PREFIX . 'pico_blog_comments';

$sql = $db->run(<<<SQL
CREATE TABLE IF NOT EXISTS `$blog_options` (
	`component_id` BIGINT(11) NOT NULL,
	`allow_comments` TINYINT(1) NOT NULL DEFAULT 0,
	`show_rss` TINYINT(1) NOT NULL DEFAULT 0,
	`moderator_address` VARCHAR(255) NOT NULL DEFAULT '',
	`layout` TEXT,
	`full_layout` TEXT,
	`comment_layout` TEXT,
	`hide_expired` TINYINT(1) NOT NULL DEFAULT 0,
	`num_entries` INT(5) NOT NULL DEFAULT 3,
	`show_short_layout` BLOB,
	`show_bottom_nav` TINYINT(1) NOT NULL DEFAULT 0,
	`image_settings` BLOB,
	PRIMARY KEY(`component_id`));
SQL
);

$sql = $db->run(<<<SQL
CREATE TABLE IF NOT EXISTS `$blog_entries` (
	`post_id` BIGINT(11) auto_increment,
	`component_id` BIGINT(11) NOT NULL,
	`date` BIGINT(11) NOT NULL,
	`saved_date` BIGINT(11) NOT NULL,
	`title` VARCHAR(255) NOT NULL,
	`tags` blob,
	`post` longtext,
	`category` BIGINT(11),
	`alias` VARCHAR(255) NOT NULL,
	`published` TINYINT(1) NOT NULL DEFAULT 1,
	`allow_comments` TINYINT(1) NOT NULL DEFAULT 1,
	`auto_saved_date` BIGINT(11),
	`auto_saved_post` longtext,
	`last_saved_date` BIGINT(11),
	`last_saved_post` longtext,
	`story_image` VARCHAR(255),
	PRIMARY KEY(`post_id`));
SQL
);

$sql = $db->run(<<<SQL
CREATE TABLE IF NOT EXISTS `$blog_categories` (
	`category_id` BIGINT(11) auto_increment,
	`title` VARCHAR(255) NOT NULL,
	`component_id` VARCHAR(11) NOT NULL,
	`alias` VARCHAR(255) NOT NULL,
	PRIMARY KEY(`category_id`));
SQL
);

$sql = $db->run(<<<SQL
CREATE TABLE IF NOT EXISTS `$blog_comments` (
	`comment_id` BIGINT(11) auto_increment,
	`post_id` BIGINT(11) NOT NULL,
	`name` VARCHAR(32) NOT NULL,
	`active` TINYINT(1) NOT NULL DEFAULT 0,
	`date` BIGINT(11) NOT NULL,
	`ip_address` VARCHAR(50) NOT NULL,
	`message` text,
	`url` VARCHAR(255),
	`email` VARCHAR(255),
	`parent` BIGINT(11) NOT NULL DEFAULT 0,
	PRIMARY KEY(`comment_id`));
SQL
);

// there have been updates to the blog from when those initial tables were created.
// below are a bunch of checks to make sure the blog is to its latest database version

$fields = $db->assoc('SHOW COLUMNS FROM `'.$blog_options.'`');
$all_fields = array();
foreach ($fields as $f)
{
	$all_fields[] = $f['Field'];
}

if (!in_array('full_layout', $all_fields))
{
	$db->run('ALTER TABLE `'.$blog_options.'` ADD COLUMN `full_layout` TEXT');
}

if (!in_array('show_bottom_nav', $all_fields))
{
	$db->run('ALTER TABLE `'.$blog_options.'` ADD COLUMN `show_bottom_nav` TINYINT(1) NOT NULL DEFAULT 0');
}

if (!in_array('num_entries', $all_fields))
{
	$db->run('ALTER TABLE `'.$blog_options.'` ADD COLUMN `num_entries` INT(5) NOT NULL DEFAULT 3');
}

if (!in_array('show_short_layout', $all_fields))
{
	$db->run('ALTER TABLE `'.$blog_options.'` ADD COLUMN `show_short_layout` BLOB');
}

if (!in_array('image_settings', $all_fields))
{
	$db->run('ALTER TABLE `'.$blog_options.'` ADD COLUMN `image_settings` BLOB');
}

// check blog comments table

$fields = $db->assoc('SHOW COLUMNS FROM `'.$blog_comments.'`');
$all_fields = array();
foreach ($fields as $f)
{
	$all_fields[] = $f['Field'];
}

if (!in_array('parent', $all_fields))
{
	$db->run('ALTER TABLE `'.$blog_comments.'` ADD COLUMN `parent` BIGINT(11) NOT NULL DEFAULT 0');
}

// check blog entries table

$fields = $db->assoc('SHOW COLUMNS FROM `'.$blog_entries.'`');
$all_fields = array();
foreach ($fields as $f)
{
	$all_fields[] = $f['Field'];
}

if (!in_array('allow_comments', $all_fields))
{
	$db->run('ALTER TABLE `'.$blog_entries.'` ADD COLUMN `allow_comments` TINYINT(1) NOT NULL DEFAULT 1');
}

if (!in_array('auto_saved_date', $all_fields))
{
	$db->run('ALTER TABLE `'.$blog_entries.'` ADD COLUMN `auto_saved_date` BIGINT(11)');
}

if (!in_array('auto_saved_post', $all_fields))
{
	$db->run('ALTER TABLE `'.$blog_entries.'` ADD COLUMN `auto_saved_post` longtext');
}

if (!in_array('last_saved_date', $all_fields))
{
	$db->run('ALTER TABLE `'.$blog_entries.'` ADD COLUMN `last_saved_date` BIGINT(11)');
}

if (!in_array('last_saved_post', $all_fields))
{
	$db->run('ALTER TABLE `'.$blog_entries.'` ADD COLUMN `last_saved_post` longtext');
}

if (!in_array('saved_date', $all_fields))
{
	$db->run('ALTER TABLE `'.$blog_entries.'` ADD COLUMN `saved_date` BIGINT(11) NOT NULL DEFAULT 0');
}

if (!in_array('story_image', $all_fields))
{
	$db->run('ALTER TABLE `'.$blog_entries.'` ADD COLUMN `story_image` VARCHAR(255)');
}

if (!in_array('published', $all_fields))
{
	$db->run('ALTER TABLE `'.$blog_entries.'` ADD COLUMN `published` TINYINT(1) NOT NULL DEFAULT 1');
}

// check blog categories table

$fields = $db->assoc('SHOW COLUMNS FROM `'.$blog_categories.'`');
$all_fields = array();
foreach ($fields as $f)
{
	$all_fields[] = $f['Field'];
}

if (in_array('position', $all_fields))
{
	$db->run('ALTER TABLE `'.$blog_categories.'` DROP `position`');
}
?>