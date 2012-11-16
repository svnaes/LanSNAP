<?php
chdir('../../../');
require_once('core.php');

if (USER_ACCESS < 3) { exit(); }
$music_entries = DB_PREFIX . 'music_data';

$action = $_REQUEST['page_action'];
if ($action == 'purge')
{
	$filename = urldecode($_GET['filename']);
	$check = 'includes/content/music/upload/'.$filename;
	
	if (file_exists($check))
	{
		unlink($check);
	}
}
elseif ($action == 'add')
{
	foreach ($_POST as $key=>$val)
	{
		if (is_string($val))
		{
			$$key = stripslashes($val);
		}
	}
	
	$full_music_file = 'includes/content/music/upload/' . $music_file;
	$full_image_file = 'includes/content/music/upload/' . $image_file;
	
	if (!file_exists($full_music_file))
	{
		echo 'File not found: ' . $music_file;
	}
	else
	{
		// add it
		$db->run('UPDATE `'.$music_entries.'` SET `position`=(`position`+1) WHERE `instance_id`=?', $instance_id);
		$entry_id = $db->insert('INSERT INTO `'.$music_entries.'` (`component_id`, `music_file`, `image_file`, `artist`, `song_name`, `instance_id`) VALUES (?,?,?,?,?,?)',
			$component_id, $music_file, $image_file, $artist, $song_name, $instance_id
		);
		
		$new_dir = 'includes/content/music/storage/'.$entry_id;
		mkdir($new_dir, 0777);
		rename($full_music_file, $new_dir . '/' . $music_file);
		if ( (file_exists($full_image_file)) and (is_file($full_image_file)) )
		{
			rename($full_image_file, $new_dir . '/' . $image_file);
		}
	}
}
elseif ($action == 'delete')
{
	$entry_id = $_GET['entry'];
	$component_id = $db->result('SELECT `component_id` FROM `'.$music_entries.'` WHERE `entry_id`=?', $entry_id);
	$instance_id  = $db->result('SELECT `instance_id` FROM `'.$music_entries.'` WHERE `entry_id`=?', $entry_id);
	$position     = $db->result('SELECT `position` FROM `'.$music_entries.'` WHERE `entry_id`=?', $entry_id);
	$db->run('UPDATE `'.$music_entries.'` SET `position`=(`position`-1) WHERE `position` > ? AND `instance_id`=?', $position, $instance_id);
	$db->run('DELETE FROM `'.$music_entries.'` WHERE `entry_id`=? LIMIT 1', $entry_id);
}
elseif ($action == 'move')
{
	$entry_id = $_GET['entry'];
	$direction = $_GET['direction'];
	$instance_id  = $db->result('SELECT `instance_id` FROM `'.$music_entries.'` WHERE `entry_id`=?', $entry_id);
	$component_id = $db->result('SELECT `component_id` FROM `'.$music_entries.'` WHERE `entry_id`=?', $entry_id);
	$position     = $db->result('SELECT `position` FROM `'.$music_entries.'` WHERE `entry_id`=?', $entry_id);
	
	if ($direction == 'up')
	{
		$new_position = $position - 1;
	}
	else
	{
		$new_position = $position + 1;
	}
	
	$move_id = $db->result('SELECT `entry_id` FROM `'.$music_entries.'` WHERE `instance_id`=? AND `position`=?', $instance_id, $new_position);
	if (is_numeric($move_id))
	{
		$db->run('UPDATE `'.$music_entries.'` SET `position`=? WHERE `entry_id`=?', $new_position, $entry_id);
		$db->run('UPDATE `'.$music_entries.'` SET `position`=? WHERE `entry_id`=?', $position, $move_id);
	}
}
elseif ($action == 'update')
{
	$field    = $_GET['field'];
	$text     = urldecode($_GET['text']);
	$entry_id = $_GET['entry'];
	
	$db->run('UPDATE `'.$music_entries.'` SET `'.$field.'`=? WHERE `entry_id`=?', $text, $entry_id);
}
elseif ($action == 'update_options')
{
	$component_id = $_POST['component_id'];
	$new_options  = $_POST['options'];
	
	foreach ($new_options as $key=>$val)
	{
		$new_options[$key] = stripslashes($val);
	}
	
	$db->run('UPDATE `'.DB_COMPONENT_TABLE.'` SET `additional_info`=? WHERE `component_id`=?', serialize($new_options), $component_id);
	exit();
}
?>