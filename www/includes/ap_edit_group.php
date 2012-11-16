<?php
chdir('../');
require_once('core.php');
if (USER_ACCESS < 3) { exit(); }

$group_id = $_GET['group_id'];
if (!is_numeric($group_id)) { exit(); }

$groups = DB_PREFIX . 'pico_groups';

if ($_POST['page_action'] == 'update_group')
{
	$new_name   = trim(stripslashes($_POST['group_name']));
	$profile_id = $_POST['profile_id'];
	
	if (is_array($_POST['current_users']))
	{
		$all_users = $_POST['current_users'];
		$all_users = array_unique($all_users);
		$new_users = (sizeof($_POST['current_users']) > 0) ? implode(',', $all_users) : null;
	}
	else
	{
		$new_users = null;
	}
	
	$sql = $db->run('UPDATE `'.$groups.'` SET `name`=?, `users`=?, `profile_id`=? WHERE `group_id`=?', $new_name, $new_users, $profile_id, $group_id);
	return;
}

$group = $db->assoc('SELECT * FROM `'.$groups.'` WHERE `group_id`=?', $group_id);
if (!is_array($group)) { exit(); }

$users = $db->force_multi_assoc('SELECT * FROM `'.DB_USER_TABLE.'` ORDER BY `access` DESC, `username` ASC');
$all_users = '';
if (sizeof($users) > 0)
{
	foreach ($users as $user)
	{
		$all_users .= '<option value="'.$user['id'].'">'.$user['username'].'</option>';
	}
}

$group_users = ($group['users'] != null) ? explode(',', $group['users']) : array();
$current_users = '';

if (sizeof($group_users) > 0)
{
	foreach ($group_users as $user_id)
	{
		$user = $db->assoc('SELECT * FROM `'.DB_USER_TABLE.'` WHERE `id`=?', $user_id);
		if (is_array($user))
		{
			$current_users .= '<option value="'.$user['id'].'">'.$user['username'].'</option>';
		}
	}
}

?>
<form method="post" action="<?=$body->url('includes/ap_edit_group.php?group_id='.$group_id)?>" onsubmit="Pico_SubmitEditGroup(this); return false">
<input type="hidden" name="page_action" value="update_group" />
<h3 style="margin: 10px 0">Edit <?=$group['name']?></h3>
<table border="0" cellpadding="0" cellspacing="0" class="admin_list">
<tr>
	<td>Group Name:</td>
	<td><input type="text" name="group_name" value="<?=$group['name']?>" /></td>
</tr>
<tr>
	<td>Group Profile:</td>
	<td><?=Pico_GroupProfileDropdown('profile_id', $group['profile_id'])?></td>
</tr>
<tr>
	<td>Users</td>
	<td>
		<table border="0" cellpadding="2" cellspacing="1">
		<tr>
			<td>
				Current Users<br />
				<select id="current_users" name="current_users[]" multiple="multiple" style="width: 150px; height: 200px">
					<?=$current_users?>
				</select>
			</td>
			<td style="text-align: center">
				<button onclick="Pico_SelectAdd('all_users', 'current_users'); return false">Add</button><br />
				<button onclick="Pico_SelectDelete('current_users'); return false">Delete</button><br />
				<br />
				<input type="submit" value="Save" />
			</td>
			<td>
				All Users<br />
				<select id="all_users" name="all_users[]" multiple="multiple" style="width: 150px; height: 200px">
					<?=$all_users?>
				</select>
			</td>
		</tr>
		</table>
	</td>
</tr>
</table>
</form>
<br />
<div class="click" onclick="Pico_ManageGroups()">Go Back</div>