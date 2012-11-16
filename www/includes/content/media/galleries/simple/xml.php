<?php
chdir('../../../../../');
require_once('core.php');
require_once('includes/content/media/functions.php');
$media_files      = DB_PREFIX . 'pico_media_files';
$media_categories = DB_PREFIX . 'pico_media_categories';
$instance_id      = $_GET['instance_id'];
$component_id     = $_GET['component_id'];
$category_id      = $_GET['category'];

$flash_settings  = gallery_get_settings($component_id); // loads the settings as well as defaults if needed

$xml = '';
// get all the images

$images  = $db->force_multi_assoc('SELECT * FROM `'.$media_files.'` WHERE `instance_id`=? ORDER BY `position` ASC', $instance_id);
if ( (sizeof($images) > 0) and (is_array($images)) )
{
	foreach ($images as $image)
	{
		$image_file = get_gallery_image($image['file_id']);
		$thumb_file = get_gallery_thumb($image['file_id']);
		
		$image_path  = $body->url($image_file);
		$thumb_path  = $body->url($thumb_file);
		
		$full_link  = 'http://' . $_SERVER['SERVER_NAME'] . $body->url($image_file);
		
		$description = (strlen($image['description']) > 0) ? $image['description'] : '';
		$title       = (strlen($image['title']) > 0) ? $image['title'] : '';
		
		$xml .= "<image title=\"$title\" fullLink=\"$full_link\" full=\"$image_path\" thumb=\"$thumb_path\">\n";
		$xml .= "\t<desc>\n\t\t<![CDATA[$description]]>\n\t</desc>\n";
		$xml .= "</image>\n";
	}
}

echo '<?xml version="1.0" encoding="utf-8"?>' . "\n";
?>
<gallery
	displayThumbs="<?=$flash_settings['displayThumbs']?>"
	thumbsScrollSpeed="<?=$flash_settings['thumbsScrollSpeed']?>"
	delay="<?=$flash_settings['delay']?>"
	linkColor="#<?=$flash_settings['linkColor']?>"
	hoverColor="#<?=$flash_settings['hoverColor']?>"
>
<?=$xml?>
</gallery>