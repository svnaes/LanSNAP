<?php
chdir('../../../../../');
require_once('core.php');
require_once('includes/content/media/functions.php');
$media_files      = DB_PREFIX . 'pico_media_files';
$instance_id      = $_GET['instance_id'];
$component_id     = $_GET['component_id'];

$user_settings  = gallery_get_settings($component_id);
$gallery_config = gallery_get_base_options($component_id);

$images  = $db->force_multi_assoc('SELECT * FROM `'.$media_files.'` WHERE `instance_id`=? ORDER BY `position` ASC', $instance_id);
$xml = '';
if ( (sizeof($images) > 0) and (is_array($images)) )
{
	foreach ($images as $image_info)
	{
		$image_id = $image_info['file_id'];
		
		$desc  = addslashes($image_info['description']);
		$url   = $image_info['url'];
		
		$image = get_gallery_image($image_id);
		$thumb = get_gallery_thumb($image_id);
		
		$image = basename($image);
		$thumb = basename($thumb);
		
		
		$xml .= "\t\t<ImageName link=\"$url\" target=\"_self\" description=\"$description\" thumb=\"$thumb\">$image</ImageName>\n";
	}
}

echo '<?xml version="1.0" encoding="utf-8" standalone="no"?>' . "\n";
?>
<Items>
	<Header>
		<ImagesFolder><?=$body->url('includes/content/media/galleries/pane_viewer/files/')?></ImagesFolder>
		<!-- IF SET TO 0, THERE WILL BE NO AUTOSLIDE -->
		<autoSlide><?=$gallery_config['autoSlide']?></autoSlide>
	</Header> 
	<Images>
<?=$xml?>
	</Images>
</Items>