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
$options        = gallery_get_base_options($component_id);

$xml         = '';
$categories  = $db->force_multi_assoc('SELECT * FROM `'.$media_categories.'` WHERE `component_id`=? ORDER BY `position` ASC', $component_id);
$cat_counter = 0;

if ( (sizeof($categories) > 0) and (is_array($categories)) )
{
	foreach ($categories as $category)
	{
		$default = ($cat_counter == 0) ? 'default="true"' : ''; $cat_counter++;
		$cat_name = $category['title'];
		$xml .= "\t<category name=\"$cat_name\" $default>\n";
		
		// get all the images
		
		$images = $db->force_multi_assoc('SELECT * FROM `'.$media_files.'` WHERE `instance_id`=? AND `category_id`=? ORDER BY `position` ASC', $instance_id, $category['category_id']);
		if ( (sizeof($images) > 0) and (is_array($images)) )
		{
			foreach ($images as $image)
			{
				$image_file = get_gallery_image($image['file_id']);
				$thumb_file = get_gallery_thumb($image['file_id']);
				
				$image_path  = $body->url($image_file);
				$thumb_path  = $body->url($thumb_file);
				
				$description = (strlen($image['description']) > 0) ? $image['description'] : '';
				$title       = (strlen($image['title']) > 0) ? $image['title'] : '';
				
				$xml .= "\t\t<picture src=\"$image_path\" thumbnail=\"$thumb_path\" short_description=\"$title\">\n";
				
				if (strlen($description) > 0)
				{
					$xml .= "\t\t\t<description>\n\t\t\t\t$description\n\t\t\t</description>\n";
				}
				$xml .= "\t\t</picture>\n";
			}
		}
		
		$xml .= "\t</category>\n";
	}
}
?>
<gallery width="<?=$options['swf_width']?>" height="<?=$options['swf_height']?>" maxWidth="<?=$options['img_width']?>" maxHeight="<?=$options['img_height']?>">
	<config>
		<controlColor>0x<?=$flash_settings['controlColor']?></controlColor>
		<controlFontSize><?=$flash_settings['controlFontSize']?></controlFontSize>
		<controlTextColor>0x<?=$flash_settings['controlTextColor']?></controlTextColor>
		<controlTextColorHover>0x<?=$flash_settings['controlTextColorHover']?></controlTextColorHover>
		<bgColor>0x<?=$flash_settings['bgColor']?></bgColor>
		<borderColor>0x<?=$flash_settings['borderColor']?></borderColor>
		<thumbnailTitleColor>0x<?=$flash_settings['thumbnailTitleColor']?></thumbnailTitleColor>
		<thumbnailFontSize><?=$flash_settings['thumbnailFontSize']?></thumbnailFontSize>
		<descriptionColor>0x<?=$flash_settings['descriptionColor']?></descriptionColor>
		<descriptionBgColor>0x<?=$flash_settings['descriptionBgColor']?></descriptionBgColor>
		<preloaderColor>0x<?=$flash_settings['preloaderColor']?></preloaderColor>
	</config>
<?=$xml?>
</gallery>