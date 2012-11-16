<?php

// for binding/unbinding ===========================
global $params;
if ($params[2] == 'bind')
{
	$entry_id = $params[3];
	$ip_info = $db->assoc('SELECT * FROM `'.$ip_table.'` WHERE `entry_id`=? LIMIT 1',
		$entry_id
	);
	
	$db->run('UPDATE `'.$ip_table.'` SET `bound`=? WHERE `entry_id`=?', 1, $entry_id);
	LS_BindAddress($ip_info['ip_address']);
	
	header('Location: '.$body->url(CURRENT_ALIAS.'/provision'));
	exit();
}
elseif ($params[2] == 'unbind')
{
	$entry_id = $params[3];
	$ip_info = $db->assoc('SELECT * FROM `'.$ip_table.'` WHERE `entry_id`=? LIMIT 1',
		$entry_id
	);
	
	$db->run('UPDATE `'.$ip_table.'` SET `bound`=? WHERE `entry_id`=?', 0, $entry_id);
	LS_UnbindAddress($ip_info['ip_address']);
	
	header('Location: '.$body->url(CURRENT_ALIAS.'/provision'));
	exit();
}
elseif ($params[2] == 'delete')
{
	$entry_id = $params[3];
	$ip_info = $db->assoc('SELECT * FROM `'.$ip_table.'` WHERE `entry_id`=? LIMIT 1',
		$entry_id
	);
	
	// delete from IP table and DNS table
	$db->run('DELETE FROM `'.$ip_table.'` WHERE `entry_id`=?', $entry_id);
	$db->run('UPDATE `'.$ip_table.'` SET `status`=?', 1); // so dhcp rewrites
	
	if (strlen($ip_info['hostname']) > 0)
	{
		// delete from DNS table
		// TODO: config this
		$dns_db = new DataBase('localhost', 'root', 'jl@0p13!', 'pdns');
		$dns_db->run('DELETE FROM `records` WHERE `content`=?', $ip_info['ip_address']);
	}
	header('Location: '.$body->url(CURRENT_ALIAS . '/provision'));
	exit();
}

// page actions =====================================

if ($_POST['page_action'] == 'provision')
{
	foreach ($_POST as $key=>$val)
	{
		$$key = trim(stripslashes($val));
	}
	
	unset($error); // in case
	
	if (strlen($mac_address) < 12)
	{
		$error = 'Invalid Mac Address';
	}
	elseif (!is_numeric($ip_group))
	{
		$error = 'Please select an IP group';
	}
	else
	{
		// get address
		$ip_address = LS_GetIPAddress($ip_group);
		if (is_bool($ip_address))
		{
			$error = 'Unable to fetch IP';
		}
	}

	// check for dns hostname
	$check = $db->result('SELECT count(1) FROM `'.$ip_table.'` WHERE `hostname`=?', $dns_hostname);
	if ($check != 0)
	{
		$error = 'DUPLICATE HOSTNAME';
	}

	
	if (isset($error))
	{
		echo '<p class="error">'.$error.'</p>';
	}
	else
	{
		// add the user
		$entry_id = $db->insert('INSERT INTO `'.$ip_table.'` (`user_id`, `mac_address`, `ip_address`, `hostname`, `description`, `source`, `status`) VALUES (?,?,?,?,?,?,?)',
			$user, $mac_address, $ip_address, $dns_hostname, $description, 1, 1
		);
		
		if (!is_numeric($entry_id))
		{
			echo '<p class="error">Unable to add: ' . $db->error .'</p>';
		}
		else
		{
			if (strlen($dns_hostname) > 0)
			{
				// add to table
				$domain = $dns_hostname . '.' . LS_GetSetting('domain_tld') . '.lan';
				
				/*
				$db->run('INSERT INTO `'.$domain_table.'` (`name`, `ttl`, `rdtype`, `rdata`) VALUES (?,?,?,?)',
					$domain, '259200', 'A', $ip_address
				);*/
				
				// TODO: config this
				$dns_db = new DataBase('localhost', 'root', 'jl@0p13!', 'pdns');
				$dns_db->run('INSERT INTO `records` (`domain_id`, `name`, `content`, `type`, `ttl` , `prio` , `auth`) VALUES (?,?,?,?,?,?,?)',
					1, $domain, $ip_address, 'A', 120, 'NULL', 1
				);
				
				//echo $db->query . '<br />' . $db->error;
			}
			
			header('Location: '.$_SERVER['REQUEST_URI']);
			exit();
		}
	}
}

$users  = $db->force_multi_assoc('SELECT * FROM `'.$user_table.'` ORDER BY `last_name` ASC, `first_name` ASC');
$output = '';

$ticket_counts = array();
$user_list = '<select name="user"><option value="0">None</option>';

if (is_array($users))
{
	foreach ($users as $u)
	{
		$user_list .= '<option value="'.$u['user_id'].'">'.$u['last_name'].', '.$u['first_name'].'</option>';
	}
}

$user_list .= '<select>';

if (!isset($_POST['page_action']))
{
	// show list
	$provisioned_users = $db->force_multi_assoc('SELECT * FROM `'.$ip_table.'` WHERE `source`=? ORDER BY `hostname` ASC', 1);
	$output = '';
	if (is_array($provisioned_users))
	{
		$output .= '
		<table class="mod_userlist" cellpadding="0" cellspacing="0" border="0">
		<tr>
			<th>Hostname</th>
			<th>Mac Address</th>
			<th>IP Address</th>
			<th>User</th>
			<th>Description</th>
			<th>Network</th>
			<th>Delete</th>
		</tr>';
		
		$counter = 0;
		
		foreach ($provisioned_users as $user)
		{
			if ($user['user_id'] == 0)
			{
				$name = 'None';
			}
			else
			{
				$user_info = $db->assoc('SELECT * FROM `'.$user_table.'` WHERE `user_id`=?', $user['user_id']);
				$name = $user_info['last_name'] . ', ' . $user_info['first_name'];
			}
			
			$class = ($counter % 2 == 0) ? 'a' : 'b';
			$network = ($user['bound'] == 0) ? '<a href="'.$body->url(CURRENT_ALIAS.'/provision/bind/'.$user['entry_id']).'">On</a>' : '<a href="'.$body->url(CURRENT_ALIAS.'/provision/unbind/'.$user['entry_id']).'">Off</a>';
			
			$delete = '<a href="'.$body->url(CURRENT_ALIAS  . '/provision/delete/'.$user['entry_id']).'" onclick="return confirm(\'Are you sure you want to remove this entry?\')"><img src="'.$body->url('includes/icons/delete.png').'" /></a>';
			
			$output .= '
			<tr class="'.$class.'">
				<td>'.$user['hostname'].'</td>
				<td>'.$user['mac_address'].'</td>
				<td>'.$user['ip_address'].'</td>
				<td>'.$name.'</td>
				<td>'.$user['description'].'</td>
				<td>'.$network.'</td>
				<td>'.$delete.'</td>
			</tr>';
		}
		
		$output .= '</table>';
		echo $output;
	}
}

?>


<form method="post" action="<?=$_SERVER['REQUEST_URI']?>">
<input type="hidden" name="page_action" value="provision" />
<table class="mod_register_user" cellpadding="0" cellspacing="0" border="0">
<tr>
	<td class="left">Mac Address</td>
	<td class="right"><input type="text" name="mac_address" value="<?=$mac_address?>" autocomplete="off" /></td>
</tr>
<tr>
	<td class="left">IP Group</td>
	<td class="right">
		<select name="ip_group">
			<option value="">Select...</option>
			<option value="1" <?=($ip_group == 1) ? 'selected="selected"' : ''?>>Staff</option>
			<option value="2" <?=($ip_group == 2) ? 'selected="selected"' : ''?>>Other</option>
		</select>
	</td>
</tr>
<tr>
	<td class="left">DNS Hostname</td>
	<td class="right"><input type="text" name="dns_hostname" value="<?=$dns_hostname?>" autocomplete="off" /></td>
</tr>
<tr>
	<td class="left">Description</td>
	<td class="right"><input type="text" name="description" autocomplete="off" value="<?=$description?>" /></td>
</tr>
<tr>
	<td class="left">User</td>
	<td class="right"><?=$user_list?></td>
</tr>
</table>

<input type="submit" value="Add" />
</form>
