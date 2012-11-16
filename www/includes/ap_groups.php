<?php
chdir('../');
require_once('core.php');
if (USER_ACCESS < 3) { exit(); }

$group_table  = DB_PREFIX . 'pico_groups';
$profile_list = DB_PREFIX . 'user_profile_list';

$sql = $db->run(<<<SQL
CREATE TABLE IF NOT EXISTS `$group_table` (
	`group_id` BIGINT(11) NOT NULL auto_increment,
	`name` VARCHAR(32) NOT NULL,
	`users` blob,
	`profile_id` BIGINT(11) NOT NULL DEFAULT 0,
	PRIMARY KEY(`group_id`));
SQL
);

if (isset($_POST['new_group']))
{
	$new_group  = trim(stripslashes($_POST['new_group']));
	$profile_id = $_POST['profile_id'];
	
	if (strlen($new_group) > 0)
	{
		$check = $db->result('SELECT count(1) FROM `'.$group_table.'` WHERE `name` LIKE ?', $new_group);
		if ($check == 0)
		{
			$db->insert('INSERT INTO `'.$group_table.'` (`name`, `profile_id`) VALUES (?,?)', $new_group, $profile_id);
		}
		else
		{
			echo 'That group name already exists';
		}
	}
	return;
}

$all_groups = $db->force_multi_assoc('SELECT * FROM `'.$group_table.'` ORDER BY `name` ASC');
$g_table = '';
if ( (is_array($all_groups)) and (sizeof($all_groups) > 0) )
{
	foreach ($all_groups as $group)
	{
		$name   = $group['name'];
		$num    = ($group['users'] != null) ? sizeof(explode(',', $group['users'])) : 0;
		$edit   = '<img src="'.$body->url('includes/icons/users.png').'" class="icon click" title="Users" onclick="Pico_EditGroup('.$group['group_id'].')" />';
		$delete = ($num > 0) ? '' : '<img src="'.$body->url('includes/icons/delete.png').'" class="icon click" title="Delete" onclick="Pico_DeleteGroup('.$group['group_id'].')" />';
		if ($group['profile_id'] != 0)
		{
			$profile_name = $db->result('SELECT `name` FROM `'.$profile_list.'` WHERE `profile_id`=?', $group['profile_id']);
		}
		else
		{
			$profile_name = 'None';
		}
		
		$g_table .= <<<HTML
<tr>
	<td>$name</td>
	<td>$num</td>
	<td>$profile_name</td>
	<td>$edit $delete</td>
</tr>
HTML;
	}
}

// make sure we have new profile id field

$fields = $db->assoc('SHOW COLUMNS FROM `'.$group_table.'`');
$all_fields = array();
foreach ($fields as $f)
{
	$all_fields[] = $f['Field'];
}

if (!in_array('profile_id', $all_fields))
{
	$db->run('ALTER TABLE `'.$group_table.'` ADD COLUMN `profile_id` BIGINT(11) NOT NULL DEFAULT 0');
}

// get user profiles



?>
<div class="ap_overflow" style="width: 430px; height: 360px">
	<h3 style="margin: 10px 0">Current Groups</h3>
	<table border="0" cellpadding="2" cellspacing="1">
	<tr>
		<th>Group Name</th>
		<th># Users</th>
		<th>Profile</th>
		<th>Actions</th>
	</tr>
	<?=$g_table?>
	</table>
	
	<hr />
	<h3 style="margin: 10px 0">Add New Group</h3>

	<form method="post" action="<?=$body->url('includes/ap_groups.php')?>" onsubmit="Pico_AddGroup(this); return false">
	<table border="0" cellpadding="0" cellspacing="0" class="admin_list">
		<tr><td>Group Name</td><td><input type="text" name="new_group" /></td></tr>
		<tr><td>Group Profile</td><td><?=Pico_GroupProfileDropdown('profile_id')?></td></tr>
	</table>
	<input type="submit" value="Add" />
	</form>
</div>