<?php
chdir('../../../');
require_once('core.php');
require_once('includes/content/video/functions.php');
if (USER_ACCESS < 3) { exit(); }

$action = $_REQUEST['page_action'];

if ($action == 'upload')
{
	$filename     = urldecode($_GET['filename']);
	$component_id = urldecode($_GET['component_id']);
	$instance_id  = urldecode($_GET['instance_id']);
	$extension    = strtolower(array_pop(explode('.', $filename)));
	$upload_file  = 'includes/content/video/upload/' . $filename;
	
	$storage_folder = 'includes/content/video/storage/'.$instance_id;
	if (!file_exists($storage_folder))
	{
		mkdir($storage_folder, 0777);
	}
	
	
	
	$videos = array('mov', 'flv');
	if (file_exists($upload_file))
	{
		if (in_array($extension, $videos))
		{
			$target_file = 'includes/content/video/storage/'.$instance_id.'/'.$component_id.'.'.$extension;
			rename($upload_file, $target_file);
		}
		else
		{
			$target_file = 'includes/content/video/storage/'.$instance_id.'/'.$component_id.'.jpg';
			$data = $db->result('SELECT `additional_info` FROM `'.DB_COMPONENT_TABLE.'` WHERE `component_id`=?', $component_id);
			$player_options = unserialize($data);
			if (!is_array($player_options)) { $player_options = array(); }
			// get width and height
			$width  = (is_numeric($player_options['newWidth']))  ? $player_options['newWidth'] : 400;
			$height = (is_numeric($player_options['newHeight'])) ? $player_options['newHeight'] : 300;
			make_new_image($upload_file, $target_file, $width, $height);
		}
	}
	exit();
}

if ($action == 'update_options')
{
	$settings = array();
	foreach($_POST as $key=>$val)
	{
		if (substr($key, 0, 7) == 'option_')
		{
			$_key = substr($key, 7);
			$settings[$_key] = $val; 
		}
	}
	$component_id = $_POST['component_id'];
	$db->run('UPDATE `'.DB_COMPONENT_TABLE.'` SET `additional_info`=? WHERE `component_id`=?', serialize($settings), $component_id);
	exit();
}