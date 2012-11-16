<?php
require_once('includes/content/media/functions.php');
$media_files = DB_PREFIX . 'pico_media_files';
$img_info = $db->assoc('SELECT * FROM `'.$media_files.'` WHERE `instance_id`=? ORDER BY RAND() LIMIT 1', $instance_id);

if (is_array($img_info))
{
	$image_file = get_gallery_image($img_info['file_id']);
	$output = '<img src="'.$body->url($image_file).'" border="0" />';
	
	$url = $img_info['url'];
	
	if (strlen($url) > 0)
	{
		$output = '<a href="'.$url.'">'.$output.'</a>';
	}
	
	echo $output;
}
?>
