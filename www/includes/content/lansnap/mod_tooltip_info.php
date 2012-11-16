<?php
chdir('../../../');
require_once('core.php');

if (USER_ACCESS < 2) { exit(); }
require_once('includes/content/lansnap/functions.php');

$layout_table = DB_PREFIX . 'lansnap_layouts';
$table_table  = DB_PREFIX . 'lansnap_layout_tables';
$seat_table   = DB_PREFIX . 'lansnap_layout_seats';

$user_table   = DB_PREFIX . 'lansnap_users';
$ip_table     = DB_PREFIX . 'lansnap_addresses';

if ($_GET['page_action'] == 'change_seat')
{
	$user_id  = $_GET['user_id'];
	$new_seat = $_GET['new_seat'];
	
	// see if seat is available
	$check = $db->result('SELECT `user_id` FROM `'.$seat_table.'` WHERE `seat_id`=?', $new_seat);
	if ($check == 0)
	{
		$db->run('UPDATE `'.$seat_table.'` SET `user_id`=? WHERE `user_id`=?', 0, $user_id); // get rid of current assignment
		$db->run('UPDATE `'.$seat_table.'` SET `user_id`=? WHERE `seat_id`=?', $user_id, $new_seat); // update seat table
		$db->run('UPDATE `'.$user_table.'` SET `seat_number`=? WHERE `user_id`=?', $new_seat, $user_id); // update user table
	}
	else
	{
		echo "That seat is taken.";
	}
	
	exit();
}

$seat_id      = $_GET['seat_id'];
$component_id = $_GET['component_id'];

$seat_info = $db->assoc('SELECT * FROM `'.$seat_table.'` WHERE `seat_id`=?', $seat_id);
$user_id   = $seat_info['user_id'];

$user_info = $db->assoc('SELECT * FROM `'.$user_table.'` WHERE `user_id`=?', $user_id);
$ip_info   = $db->assoc('SELECT * FROM `'.$ip_table.'` WHERE `user_id`=?', $user_id);
/*
// get some bandwidth info!

$mb_down = round(($ip_info['bytes_in'] / 1024),2);
$mb_up   = round(($ip_info['bytes_out'] / 1024),2);

$total = '<td>'.$mb_down.' MB</td><td>'.$mb_up.' MB</td>';

$lastmin_entries = LS_GetUserBWByMinutes($ip_info['ip_address'], 1);
$last_min = '<td>0</td><td>0</td>';

$kb_down = 0;
$kb_up = 0;

if (sizeof($lastmin_entries) > 0)
{
	foreach ($lastmin_entries as $timestamp => $info)
	{
		$kb_down += $info['bytes_in'];
		$kb_up += $info['bytes_out'];
	}
	
	$mb_down = round($kb_down / 1024, 2);
	$mb_up   = round($kb_up / 1024, 2);
	$last_min = '<td>'.$mb_down.' MB</td><td>'.$mb_up.' MB</td>';
}

$last5_entries = LS_GetUserBWByMinutes($ip_info['ip_address'], 5);
$last_5 = '<td>0</td><td>0</td>';

$kb_down = 0;
$kb_up = 0;

if (sizeof($last5_entries) > 0)
{
	//echo sizeof($last5_entries) . '<br />';
	foreach ($last5_entries as $timestamp => $info)
	{
		
		$kb_down += $info['bytes_in'];
		$kb_up += $info['bytes_out'];
	}
	
	$mb_down = round($kb_down / 1024, 2);
	$mb_up   = round($kb_up / 1024, 2);
	$last_5 = '<td>'.$mb_down.' MB</td><td>'.$mb_up.' MB</td>';
}

$last30_entries = LS_GetUserBWByMinutes($ip_info['ip_address'], 30);
$last_30 = '<td>0</td><td>0</td>';

$kb_down = 0;
$kb_up = 0;

if (sizeof($last30_entries) > 0)
{
	//echo sizeof($last30_entries) . '<br />';
	foreach ($last30_entries as $timestamp => $info)
	{
		$kb_down += $info['bytes_in'];
		$kb_up += $info['bytes_out'];
	}
	
	$mb_down = round($kb_down / 1024, 2);
	$mb_up   = round($kb_up / 1024, 2);
	$last_30 = '<td>'.$mb_down.' MB</td><td>'.$mb_up.' MB</td>';
}

$bw_info = '<h3>Bandwidth History</h3>';
$bw_info .= '<table class="bw_history" cellpadding="0" cellspacing="0">
<tr>
	<th>Time</th>
	<th>Download</th>
	<th>Upload</th>
</tr>
<tr>
	<td>Last minute</td>
	'.$last_min.'
</tr>
<tr>
	<td>Last 5</td>
	'.$last_5.'
</tr>
<tr>
	<td>Last 30</td>
	'.$last_30.'
</tr>
<tr>
	<td>Total</td>
	'.$total.'
</tr>
</table>';
 */
?>
<div class="bold"><?=$user_info['username']?> - <?=$user_info['first_name']?> <?=$user_info['last_name']?></div>
<hr />
Entered: <?=date('h:ia m/d', $user_info['date_entered'])?><br />
Registered: <?=($user_info['date_registered'] != 0) ? date('h:ia m/d', $user_info['date_registered']) : 'N/A' ?><br />
IP Address: <?=$ip_info['ip_address']?><br />
MAC Address: <?=$ip_info['mac_address']?><br />
Switch Seats: <?=LS_GetSeatsDropdown($component_id)?> <button class="mini" onclick="LS_UpdateSeat(<?=$user_id?>)">Update</button><br />
<div class="close" onmouseup="LS_CloseTooltip(<?=$component_id?>, <?=$seat_id?>)"></div>
<?=$bw_info?>
