<?php
chdir('../../../../');
require_once('core.php');

//print_r($_POST);

$layout_id = $_POST['layout_id'];
$tables =  $_POST['tables'];

$layout_table = DB_PREFIX . 'lansnap_layouts';
$table_table  = DB_PREFIX . 'lansnap_layout_tables';
$seat_table   = DB_PREFIX . 'lansnap_layout_seats';

$keep_tables = array(); // array of tables to keep
$new_tables = array(); // array of new tables to add 
$post_tables = array();

if ( (sizeof($tables) > 0) and (is_array($tables)) )
{
	foreach ($tables as $counter => $info)
	{
		if (strlen($info['id']) > 0)
		{
			$table_id = substr($info['id'], 6);
			$keep_tables[] = $table_id;
			$post_tables[$table_id] = $info;
		}
		else
		{
			$new_tables[] = $info;
		}
	}
}

// get all tables in this layout

$table_list = $db->force_multi_assoc('SELECT * FROM `'.$table_table.'` WHERE `layout_id`=?', $layout_id);
if (is_array($table_list))
{
	foreach ($table_list as $table)
	{
		if (!in_array($table['table_id'], $keep_tables))
		{
			// delete this table and any seats it contains
			$db->run('DELETE FROM `'.$table_table.'` WHERE `table_id`=?', $table['table_id']);
			$db->run('DELETE FROM `'.$seat_table.'` WHERE `table_id`=?', $table['table_id']);
		}
		else
		{
			$table_id = $table['table_id'];
			$table_info = $post_tables[$table_id];
			//print_r();
			
			$db->run('UPDATE `'.$table_table.'` SET `pos_x`=?, `pos_y`=? WHERE `table_id`=?',
				$table_info['x'], $table_info['y'], $table['table_id']
			);
		}
	}
}

if (sizeof($new_tables) > 0)
{
	foreach ($new_tables as $info)
	{
		switch ($info['type'])
		{
			case '8foot_tall':
				$num_seats = 3;
				break;
			case '8foot_tall_2seats':
				$num_seats = 2;
				break;
			case '6foot_tall':
				$num_seats = 2;
				break;
			case '8foot_wide':
				$num_seats = 3;
				break;
			case '8foot_wide_2seats':
				$num_seats = 2;
				break;
			case '6foot_wide':
				$num_seats = 2;
				break;
		}
		$table_id = $db->insert('INSERT INTO `'.$table_table.'` (`layout_id`, `table_type`, `num_seats`, `pos_x`, `pos_y`) VALUES (?,?,?,?,?)',
			$layout_id, $info['type'], $num_seats, $info['x'], $info['y']
		);
		
		// add some tables
		
		for ($x = 0; $x < $num_seats; $x++)
		{
			$db->run('INSERT INTO `'.$seat_table.'` (`table_id`, `layout_id`, `table_seat_number`) VALUES (?,?,?)',
				$table_id, $layout_id, $x
			);
		}
	}
}

echo "Saved";


?>