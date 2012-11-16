<?php
chdir('../../../../');
require_once('core.php');

$component_id = $_GET['component_id'];

$layout_table = DB_PREFIX . 'lansnap_layouts';
$table_table  = DB_PREFIX . 'lansnap_layout_tables';
$seat_table   = DB_PREFIX . 'lansnap_layout_seats';

require_once('includes/content/lansnap/layout/database.php');
require_once('includes/content/lansnap/functions.php');

//$layout_id = 1; // to be replaced when needed
$layout_info = $db->assoc('SELECT * FROM `'.$layout_table.'` WHERE `component_id`=?', $component_id);

if (!is_array($layout_info))
{
	$layout_width  = LS_GetSetting('layout_width');
	$layout_height = LS_GetSetting('layout_height');
	if (!is_numeric($layout_width))  { $layout_width = 100; }
	if (!is_numeric($layout_height)) { $layout_height = 100; }
	
	$db->run('INSERT INTO `'.$layout_table.'` (`component_id`, `name`, `width`, `height`) VALUES (?,?,?,?)',
		$component_id, 'LANsnap Layout', $layout_width, $layout_height
	);
	
	$layout_info = $db->assoc('SELECT * FROM `'.$layout_table.'` WHERE `component_id`=?', $component_id);
}
$layout_width = 57;
$layout_info['width'] = 57;
$layout_id = $layout_info['layout_id'];

// get all tables (and seats)

$table_output = '';

$table_list = $db->force_multi_assoc('SELECT * FROM `'.$table_table.'` WHERE `layout_id`=?', $layout_id);
if (is_array($table_list))
{
	foreach ($table_list as $table)
	{
		$className = (strstr($table['table_type'], 'tall')) ? 'banquet_tall' : 'banquet_wide';
		$left      = $table['pos_x'];
		$top       = $table['pos_y'];
		
		$table_output .= '<div class="placeable_container '.$className.'" style="left: '.$left.'px; top: '.$top.'px" id="table_'.$table['table_id'].'">';
		$table_output .= $table['table_type'];
		$table_output .= '</div>';
	}
}


?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
<head>
	<title>Layout Maker</title>
	<link href="style.css" type="text/css" rel="stylesheet" />
	<script type="text/javascript" src="functions.js"></script>
	<script type="text/javascript">
	var func = function() {
		ResizeCanvas(<?=$layout_info['width']?>, <?=$layout_info['height']?>);
		LoadTables();
	}
	
	add_load_event(func);
	</script>
	<script type="text/javascript" src="<?=$body->url('site/javascript.php')?>"></script>
</head>
<body onmousemove="save_position(event)">

	<div class="centered">
		<div id="canvas"><?=$table_output?></div>
		<div id="controls">
			Insert Table: <br />
			<button onclick="AddTable('8foot_tall')">8 Foot - Tall</button><br />
			<button onclick="AddTable('8foot_tall_2seats')">8 Foot - Tall 2 Seats</button><br />
			<button onclick="AddTable('6foot_tall')">6 Foot - Tall</button><br />
			<button onclick="AddTable('8foot_wide')">8 Foot - Wide</button><br />
			<button onclick="AddTable('8foot_wide_2seats')">8 Foot - Wide 2 Seats</button><br />
			<button onclick="AddTable('6foot_wide')">6 Foot - Wide</button><br />
			
			<p>
			X: <span id="xpos"></span><br />
			Y: <span id="ypos"></span>
			</p>
			<p>Fine Tune: <input id="finetune" style="width: 30px" value="1" onkeyup="VerifyFineTune()"  /></p>
			<p>Snap to grid: <input id="snaptogrid" type="checkbox" value="1" checked="checked" /></p>
		
			<button onclick="SaveLayout(<?=$layout_id?>)">Save Layout</button>
			<div id="form_box"></div>
		</div>
		<div class="clear"></div>
	</div>

</body>
</html>
