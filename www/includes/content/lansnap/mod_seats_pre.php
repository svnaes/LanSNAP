<?php

if ($_POST['page_action'] == 'change_seat')
{
	$move_id = $_POST['move_seat'];
	$seat_name = $_POST['table_names'][$move_id];

	$new_id = $_POST['seat_number'];
	$new_name = LS_GetReadableSeatNumber($new_id);

	// update the external table
	//
	$host   = LS_GetSetting('externaldb_host');
	$user   = LS_GetSetting('externaldb_user');
	$pass   = LS_GetSetting('externaldb_pass');
	$dbname = LS_GetSetting('externaldb_name');
	$etable = LS_GetSetting('externaldb_table');
	$column = LS_GetSetting('edb_linked_barcode');
	$linked_seat = LS_GetSetting('edb_linked_seat');

	$external_db = new DataBase($host, $user, $pass, $dbname);
	// check to see if that seat is in use
	//
	$seat_info = $external_db->assoc('SELECT * FROM `'.$etable.'` WHERE `'.$linked_seat.'`=?', $new_name);
	if (is_array($seat_info))
	{
		// see if a user is sitting here 
		$check_username = $seat_info[LS_GetSetting('edb_linked_username')];
		if (strlen($check_username) > 0)
		{
			echo 'Seat is in use';
			return;
		}
		else
		{
			//echo '<pre>'.print_r($seat_info, true).'</pre>';
			$external_db->run('DELETE FROM `'.$etable.'` WHERE `'.$linked_seat.'`=?', $new_name);
			//return;
		}
	}
	
	$external_db->run('UPDATE `'.$etable.'` SET `'.$linked_seat.'`=? WHERE `'.$linked_seat.'`=?', $new_name, $seat_name);

	//echo $external_db->query;
	echo '<p>SEAT CHANGED!</p>';
}

$output = LS_GetTableLayout($component_id, '', '<span class="label">TABLE_ROWSEAT_NUM</span><input class="table_checkbox" type="radio" name="move_seat" value="SEAT_ID" />
	<input type="hidden" name="table_names[SEAT_ID]" value="TABLE_ROW-SEAT_NUM" />
	<div class="USER_SEAT_CLASS" title="USER_SEAT"></div>
	', TRUE);

?>
<form method="post" action="<?=$_SERVER['REQUEST_URI']?>">
<input type="hidden" name="page_action" value="change_seat" />
<?=$output?>
<p>Change Seat: <?=LS_GetSeatsDropdown($component_id)?></p>
<input type="submit" value="Continue" />
</form>

