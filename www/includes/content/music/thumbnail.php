<?php
chdir('../../../');
require_once('core.php');

$music_entries = DB_PREFIX . 'music_data';
$entry_id   = $_GET['id'];
$entry_info = $db->assoc('SELECT * FROM `'.$music_entries.'` WHERE `entry_id`=?', $entry_id);

if (!is_array($entry_info)) { exit(); }

$source_image = 'includes/content/music/storage/'.$entry_id.'/'.$entry_info['image_file'];

$component_id = $entry_info['component_id'];

$data   = $db->result('SELECT `additional_info` FROM `'.DB_COMPONENT_TABLE.'` WHERE `component_id`=?', $component_id);
$options = unserialize($data);
if (!is_array($options)) { $options = array(); }

if ( (isset($options['player'])) and (strlen($options['player']) > 0) and (file_exists($source_image)) and (is_file($source_image)) )
{
	include('includes/content/music/players/'.$options['player'].'/config.php');
	$t_width  = $config['thumbnail_width'];
	$t_height = $config['thumbnail_height'];
	
	require_once('includes/content/media/functions.php');
	header('Content-type: image/jpeg');
	make_new_image($source_image, null, $t_width, $t_height);
}
else
{
	echo 'bleh';
}

?>