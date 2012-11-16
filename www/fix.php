<?php
require_once('core.php');
require_once('includes/content/lansnap/functions.php');

$user_table = 'lansnap_users';

$users = $db->force_multi_assoc('SELECT * FROM `'.$user_table.'`');
foreach ($users as $user)
{
	//echo $user['user_id'] . "\n";
	$seat_number = $user['seat_number'];
	$db->run('UPDATE `lansnap_layout_seats` SET `user_id`=? WHERE `seat_id`=?', $user['user_id'], $seat_number);
}

