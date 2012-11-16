<?php
chdir('../../../../../');
require_once('core.php');
require_once('includes/content/media/functions.php');
$media_files      = DB_PREFIX . 'pico_media_files';
$media_categories = DB_PREFIX . 'pico_media_categories';
$instance_id      = $_GET['instance_id'];
$component_id     = $_GET['component_id'];
$category_id      = $_GET['category'];

$flash_settings = gallery_get_settings($component_id); // loads the settings as well as defaults if needed

//echo '<pre>'.print_r($flash_settings, TRUE).'</pre>';

if ($category_id == 0)
{
	$images  = $db->force_multi_assoc('SELECT * FROM `'.$media_files.'` WHERE `instance_id`=? ORDER BY `position` ASC', $instance_id);
}
else
{
	$images  = $db->force_multi_assoc('SELECT * FROM `'.$media_files.'` WHERE `instance_id`=? AND `category_id`=? ORDER BY `position` ASC', $instance_id, $category_id);
}


$img_xml = '';
$counter = 1;
if ( (sizeof($images) > 0) and (is_array($images)) )
{
	foreach ($images as $image)
	{
		$image_file = get_gallery_image($image['file_id']);
		$thumb_file = get_gallery_thumb($image['file_id']);
		
		$image_path  = $body->url($image_file);
		$thumb_path  = $body->url($thumb_file);
	
		$description = (strlen($image['description']) > 0) ? $image['description'] : '';
		$title       = (strlen($image['title']) > 0) ? $image['title'] : 'Image ' . $counter;
		
		$img_xml .= <<<XML
	<pic>
        <image>$image_path</image>
		<thumb>$thumb_path</thumb>
		<title>$title</title>
        <description><![CDATA[$description]]></description>
    </pic>

XML;
		$counter++;
	}
}

echo '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
?>
<images delay="<?=$flash_settings['delay']?>" slideshow="<?=$flash_settings['slideshow']?>">
<?=$img_xml?>
</images>