<?php
chdir('../');
require_once('core.php');
if (USER_ACCESS < 3) { exit(); }

$users = $db->force_multi_assoc('SELECT * FROM `'.DB_USER_TABLE.'` ORDER BY `access` DESC, `username` ASC');
$table = '';
if (sizeof($users) > 0)
{
	foreach ($users as $user)
	{
		$delete = '';
		if (($user['access'] <= USER_ACCESS) and (USER_ID != $user['id']))
		{
			$delete = '<img src="'.$body->url('includes/icons/delete.png').'" class="click icon" onclick="Pico_DeleteUser('.$user['id'].')" />';
		}
		
		$edit = '';
		if ($user['access'] <= USER_ACCESS)
		{
			$edit = '<img src="'.$body->url('includes/icons/edit.png').'" class="click icon" onclick="Pico_AddUser('.$user['id'].')" />';
		}
		
		$table .= '
<tr>
	<td>'.$user['username'].'</td>
	<td>'.AccessLevel($user['access']).'</td>
	<td>'.Stamp($user['last_login']).'</td>
	<td>'.$user['last_ip'].'</td>
	<td>'.$user['email_address'].'</td>
	<td>'.$user['first_name'].'</td>
	<td>'.$user['last_name'].'</td>
	<td>'.$delete.$edit.'</td>
</tr>';
	}
}
?>
<table id="manage_users" cellpadding="2" cellspacing="1">
<tr>
	<th>Username</th>
	<th>Access</th>
	<th>Last Login</th>
	<th>IP Address</th>
	<th>E-mail Address</th>
	<th>First Name</th>
	<th>Last Name</th>
	<th>Actions</th>
</tr>
<?=$table?>
</table>