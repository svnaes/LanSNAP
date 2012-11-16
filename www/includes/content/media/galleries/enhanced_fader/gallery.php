<?php
chdir('../../../../../');
require_once('core.php');
require_once('includes/content/media/functions.php');
$media_files      = DB_PREFIX . 'pico_media_files';
$media_categories = DB_PREFIX . 'pico_media_categories';
$instance_id      = $_GET['instance_id'];
$component_id     = $_GET['component_id'];

$gallery_options = gallery_get_base_options($component_id);
$flash_settings  = gallery_get_settings($component_id); // loads the settings as well as defaults if needed

$images  = $db->force_multi_assoc('SELECT * FROM `'.$media_files.'` WHERE `instance_id`=? ORDER BY `position` ASC', $instance_id);
$img_xml = '';
if ( (sizeof($images) > 0) and (is_array($images)) )
{
	foreach ($images as $image)
	{
		$image_file = get_gallery_image($image['file_id']);
		$thumb_file = get_gallery_thumb($image['file_id']);
		
		if ($image_file != false)
		{
			$image_path  = $body->url($image_file);
			$thumb_path  = $body->url($thumb_file);
			$url         = $image['url'];
			$description = (strlen($image['description']) > 0) ? $image['description'] : '';
			$title       = (strlen($image['title']) > 0) ? '<![CDATA[' . $image['title'] . ']]>' : '';
			
			$img_xml .= <<<XML
	<slide path="$image_path" thumbPath="$thumb_path" link="$url" target="_blank">
		<caption>$title</caption>
		<description>
			<![CDATA[
				$description
			]]>
		</description>
	</slide>

XML;
		}
	}
}

echo '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
?>
<slides>
<?=$img_xml?>
</slides>