<?php
chdir('../');
require_once('core.php');
if (USER_ACCESS < 3) { exit(); }
$profile_list = DB_PREFIX . 'user_profile_list';
$profile_fields = DB_PREFIX . 'user_profile_fields';

$db->run(<<<SQL
CREATE TABLE IF NOT EXISTS `$profile_list` (
	`profile_id` BIGINT(11) AUTO_INCREMENT,
	`name` VARCHAR(255) NOT NULL,
	PRIMARY KEY(`profile_id`)
)
SQL
);

$db->run(<<<SQL
CREATE TABLE IF NOT EXISTS `$profile_fields` (
	`field_id` BIGINT(11) AUTO_INCREMENT,
	`profile_id` BIGINT(11) NOT NULL,
	`name` VARCHAR(255) NOT NULL,
	`type` VARCHAR(10) NOT NULL,
	`pattern` VARCHAR(10) NOT NULL,
	`billing` VARCHAR(20),
	`required` TINYINT(1) NOT NULL DEFAULT 0,
	`options` TEXT,
	`caption` TEXT,
	`position` BIGINT(11) NOT NULL,
	`display` TINYINT(1) NOT NULL DEFAULT 1,
	PRIMARY KEY(`field_id`)
)
SQL
);

if ($_GET['edit'] != 0)
{
	$action = 'edit_user_profile';
	$text1  = 'Edit Profile';
	$text2  = 'Edit';
	$extra  = '<input type="hidden" name="edit_id" value="'.$_GET['edit'].'" />';
	$value  = $db->result('SELECT `name` FROM `'.$profile_list.'` WHERE `profile_id`=?', $_GET['edit']);
}
else
{
	$action = 'add_user_profile';
	$text1  = 'Add a profile';
	$text2  = 'Add';
	$extra  = '';
	$value  = '';
}

$profiles = $db->force_multi_assoc('SELECT * FROM `'.$profile_list.'` ORDER BY `name` ASC');
$output = '';
if (is_array($profiles))
{
	$output .= '<table border="0" cellpadding="0" cellspacing="1" class="admin_list">';
	$output .= '<tr><th>Profile</th><th>Actions</th></tr>';
	$counter = 0;
	foreach ($profiles as $profile)
	{
		$edit    = '<img hspace="1" src="'.$body->url('includes/icons/edit.png').'" title="Edit" class="icon click" onclick="Pico_UserProfiles('.$profile['profile_id'].')"/>';
		$delete  = '<img hspace="1" src="'.$body->url('includes/icons/delete.png').'" title="Delete" class="icon click" onclick="Pico_DeleteUserProfile('.$profile['profile_id'].')"/>';
		$fields  = '<img hspace="1" src="'.$body->url('includes/icons/content.png').'" title="Fields" class="icon click" onclick="Pico_UserProfileFields('.$profile['profile_id'].')"/>';
		$export  = '<a href="'.$body->url('includes/ap_actions.php?ap_action=export_profile_users&profile_id='.$profile['profile_id']).'"><img hspace="1" src="'.$body->url('includes/icons/logout.png').'" title="Export" class="icon" /></a>';
		
		$class   = ($counter % 2 == 0) ? 'a' : 'b'; $counter++;
		$output .= '<tr class="'.$class.'"><td>'.$profile['name'].'</td><td>'.$edit.$fields.$delete.$export.'</td></tr>';
	}
	$output .= '</table>';
}

?>
<div class="ap_overflow">
	<?=$output?>
	<form method="post" action="<?=$body->url('includes/ap_actions.php')?>" onsubmit="Pico_AddUserProfile(this); return false">
	<?=$extra?>
	<input type="hidden" name="ap_action" value="<?=$action?>" />
	<p><?=$text1?>: <input type="text" name="profile_name" value="<?=$value?>" /> <input type="submit" value="<?=$text2?>" /></p>
	</form>
</div>