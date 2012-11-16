<?php
$users  = $db->force_multi_assoc('SELECT * FROM `'.$user_table.'` ORDER BY `last_name` ASC, `first_name` ASC');
$output = '';

$ticket_counts = array();

global $params;
if ($params[2] == 'bind')
{
	$user_id = $params[3];
	$ip_info = $db->assoc('SELECT * FROM `'.$ip_table.'` WHERE `user_id`=? AND `source`=? LIMIT 1',
		$user_id, 0
	);
	
	$db->run('UPDATE `'.$ip_table.'` SET `bound`=? WHERE `entry_id`=?', 1, $ip_info['entry_id']);
	LS_BindAddress($ip_info['ip_address']);
	
	header('Location: '.$body->url(CURRENT_ALIAS.'/users'));
	exit();
}
elseif ($params[2] == 'unbind')
{
	$user_id = $params[3];
	$ip_info = $db->assoc('SELECT * FROM `'.$ip_table.'` WHERE `user_id`=? AND `source`=? LIMIT 1',
		$user_id, 0
	);
	
	$db->run('UPDATE `'.$ip_table.'` SET `bound`=? WHERE `entry_id`=?', 0, $ip_info['entry_id']);
	LS_UnbindAddress($ip_info['ip_address']);
	
	header('Location: '.$body->url(CURRENT_ALIAS.'/users'));
	exit();
}
elseif ($params[2] == 'reset-pwd')
{
	$user_id = $params[3];
	$db->run('UPDATE `'.$user_table.'` SET `password`=? WHERE `user_id`=?', md5('password'), $user_id);
}

if (is_array($users))
{
	$counter = 0;
	foreach ($users as $u)
	{
		$entered = date('g:ia m/d', $u['date_entered']);
		$registered = ($u['date_registered'] != 0) ? date('g:ia m/d', $u['date_registered']) : 'n/a';
		
		$ip_group = ($u['ip_group'] == 1) ? 'Staff' : 'Other';
		$ticket_id   = $u['ticket_type'];
		$ticket_info = $db->assoc('SELECT * FROM `'.$ticket_table.'` WHERE `ticket_id`=?', $ticket_id);
		$ticket_type = $ticket_info['name'];
		
		if (!isset($ticket_counts[$ticket_id]))
		{
			$ticket_counts[$ticket_id] = 0;
		}
		$ticket_counts[$ticket_id]++;
		
		$ip_info = $db->assoc('SELECT * FROM `'.$ip_table.'` WHERE `user_id`=? AND `source`=? LIMIT 1',
			$u['user_id'], 0
		);
		
		$ip_address  = $ip_info['ip_address'];
		$mac_address = $ip_info['mac_address'];
		
		$class = ($counter % 2 == 0) ? 'a': 'b';
		$counter++;
		
		$kb_in  = 0;
		$kb_out = 0;
		
		$last5_entries = LS_GetUserBWByMinutes($ip_address, 5);
		if (sizeof($last5_entries) > 0)
		{
			foreach ($last5_entries as $e)
			{
				$kb_in += $e['bytes_in'];
				$kb_out += $e['bytes_out'];
			}
		}
		
		$mb_in = round($kb_in/1024, 2);
		$mb_out = round($kb_out/1024, 2);

		$reset = '<a href="'.$body->url(CURRENT_ALIAS . '/users/reset-pwd/'.$u['user_id']).'"><img src="'.$body->url('includes/icons/info.png').'" title="Reset Password" class="icon" border="0" /></a>';

		$delete = (USER_ACCESS == 5) ? '<a href="'.$body->url(CURRENT_ALIAS . '/delete-user/'.$u['user_id']).'" onclick="return confirm(\'Are you sure you want to remove this user from the system?\')">
		<img src="'.$body->url('includes/icons/delete.png').'" class="icon" border="0" /></a>' : '';
		
		$network = ($ip_info['bound'] == 0) ? '<a href="'.$body->url(CURRENT_ALIAS.'/users/bind/'.$u['user_id']).'">On</a>' : '<a href="'.$body->url(CURRENT_ALIAS.'/users/unbind/'.$u['user_id']).'">Off</a>';
		
		$seat_num = LS_GetReadableSeatNumber($u['seat_number']);
		
		$output .= <<<HTML
<tr class="$class">
	<td>$delete</td><td>$u[last_name]</td>
	<td>$u[first_name]</td>
	<td>$u[username]</td>
	<td>$u[email_address]</td>
	<td>$ip_group</td>
	<td>$ticket_type</td>
	<td>$entered</td>
	<td>$registered</td>
	<td>$seat_num</td>
	<td>$ip_address</td>
	<td>$mac_address</td>
	<td>$mb_in/$mb_out</td>
	<td>$network $reset</td>
</tr>
HTML;
		
	}
}


?>
<table border="0" cellpadding="0" cellspacing="0" class="mod_userlist">
<tr>
	<th></th>
	<th>Last Name</th>
	<th>First Name</th>
	<th>Username</th>
	<th>Email Address</th>
	<th>IP Group</th>
	<th>Ticket Type</th>
	<th>Entered</th>
	<th>Registered</th>
	<th>Seat Number</th>
	<th>IP Address</th>
	<th>Mac Address</th>
	<th>BW (5 min) down/up</th>
	<th>Network</th>
</tr>
<?=$output?>
</table>

<?php
// show the counts

if (sizeof($ticket_counts) > 0)
{
	echo '<h2>Accounting</h2>';
	echo '<table border="0" cellpadding="0" cellspacing="0" class="mod_userlist">';
	
	$counter = 0;
	$total   = 0;
	$total_tickets = 0;
	foreach ($ticket_counts as $ticket_id => $num_tickets)
	{
		$ticket_info = $db->assoc('SELECT * FROM `'.$ticket_table.'` WHERE `ticket_id`=?', $ticket_id);
		
		$class = ($counter % 2 == 0) ? 'a': 'b';
		$counter++;
		
		$price = '$' . number_format($ticket_info['price'], 2);
		$subtotal = $ticket_info['price'] * $num_tickets;
		$total += $subtotal;
		
		$total_tickets += $num_tickets;
		
		echo '<tr class="'.$class.'">
		<td>'.$ticket_info['name'].' - '.$price.'</td>
		<td>'.$num_tickets.'</td>
		<td>$'.number_format($subtotal, 2).'</td>
		</tr>';
		
	}
	
	echo '<tr><th>Total</th><th>'.$total_tickets.'</th><th>$'.number_format($total, 2).'</th></table>';
}


?>
