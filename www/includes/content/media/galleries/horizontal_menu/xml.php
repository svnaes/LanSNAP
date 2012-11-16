<?php
chdir('../../../../../');
require_once('core.php');
require_once('includes/content/media/functions.php');
$media_files      = DB_PREFIX . 'pico_media_files';
$media_categories = DB_PREFIX . 'pico_media_categories';
$instance_id      = $_GET['instance_id'];
$component_id     = $_GET['component_id'];
$category_id      = $_GET['category'];

$xml         = '';
$categories  = $db->force_multi_assoc('SELECT * FROM `'.$media_categories.'` WHERE `component_id`=? ORDER BY `position` ASC', $component_id);
$cat_counter = 0;
if ( (sizeof($categories) > 0) and (is_array($categories)) )
{
	foreach ($categories as $category)
	{
		$cat_name = $category['title'];
		$xml .= "\t<button title=\"$cat_name\">\n";
		
		// get all the images
		
		$images  = $db->force_multi_assoc('SELECT * FROM `'.$media_files.'` WHERE `instance_id`=? AND `category_id`=? ORDER BY `position` ASC', $instance_id, $category['category_id']);
		if ( (sizeof($images) > 0) and (is_array($images)) )
		{
			foreach ($images as $image)
			{
				$image_file = get_gallery_image($image['file_id']);
				$thumb_file = get_gallery_thumb($image['file_id']);
				
				$image_path  = $body->url($image_file);
				$thumb_path  = $body->url($thumb_file);
				
				$description = (strlen($image['description']) > 0) ? $image['description'] : ' ';
				$title       = (strlen($image['title']) > 0) ? $image['title'] : ' ';
				
				$xml .= "\t\t<picture thumb=\"$thumb_path\" image=\"$image_path\" name=\"$title\" caption=\"$description\" />\n";
				
			}
		}
		
		$xml .= "\t</button>\n";
	}
}



echo '<?xml version="1.0" encoding="utf-8"?>' . "\n";
?>
<gallery>
<?=$xml?>
</gallery>
