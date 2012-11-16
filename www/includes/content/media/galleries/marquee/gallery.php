<?php

chdir('../../../../../');
require_once('core.php');
require_once('includes/content/media/functions.php');

$media_files      = DB_PREFIX . 'pico_media_files';
$media_categories = DB_PREFIX . 'pico_media_categories';

$instance_id = $_GET['instance_id'];

$images  = $db->force_multi_assoc('SELECT * FROM `'.$media_files.'` WHERE `instance_id`=? ORDER BY `position` ASC', $instance_id);
$img_xml = '';
if ( (sizeof($images) > 0) and (is_array($images)) )
{
	foreach ($images as $image)
	{
		$image_file = get_gallery_image($image['file_id']);
		$image_path  = $body->url($image_file);
		
		$img_xml .= <<<XML
	<IMAGE>
		<url>$image_path</url>
	</IMAGE>

XML;
	}
}

echo <<<XML
<?xml version="1.0" encoding="iso-8859-1"?>
<IMAGES>
$img_xml
</IMAGES>
XML;
?>