<?php

if ($_POST['page_action'] == 'assign_row')
{
	//echo '<pre>'.print_r($_POST, true).'</pre>';
	$layout_info = $db->assoc('SELECT * FROM `'.$layout_table.'` WHERE `component_id`=?', $component_id);
	$layout_id   = $layout_info['layout_id'];
	$row         = trim(stripslashes($_POST['row']));
	
	$selected_tables = array_keys($_POST['tables']);
	$table_list      = $db->force_multi_assoc('SELECT * FROM `'.$table_table.'` WHERE `layout_id`=? ORDER BY `pos_x` ASC, `pos_y` ASC', $layout_id);
	
	$seat_counter = 1;
	
	foreach ($table_list as $table)
	{
		$table_id = $table['table_id'];
		if (in_array($table_id, $selected_tables))
		{
			// give it a "row"
			$db->run('UPDATE `'.$table_table.'` SET `row`=? WHERE `table_id`=?', $row, $table_id);
			
			$seat_list = $db->force_multi_assoc('SELECT * FROM `'.$seat_table.'` WHERE `table_id`=? ORDER BY `table_seat_number` ASC', $table_id);
			//echo $db->query;
			
			// assign row/seat # to seats in this table
			foreach ($seat_list as $seat)
			{
				$db->run('UPDATE `'.$seat_table.'` SET `seat_num`=? WHERE `seat_id`=?', $seat_counter, $seat['seat_id']);
				$seat_counter++;
			}
		}
	}
	
}

$output = LS_GetTableLayout($component_id, '<input class="table_checkbox" type="checkbox" name="tables[TABLE_ID]" value="1" />', '<span class="label">TABLE_ROWSEAT_NUM</span>');

?>
<form method="post" action="<?=$_SERVER['REQUEST_URI']?>">
<input type="hidden" name="page_action" value="assign_row" />
<?=$output?>
<p>Assign Row: <input type="text" name="row" value="" /></p>
<input type="submit" value="Continue" />
</form>

