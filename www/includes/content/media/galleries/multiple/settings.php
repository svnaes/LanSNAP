<?php
chdir('../../../../../');
require_once('core.php');
require_once('includes/content/media/functions.php');
$component_id = $_GET['component_id'];
$instance_id  = $_GET['instance_id'];

$flash_settings = gallery_get_settings($component_id); // loads the settings as well as defaults if needed
echo '<?xml version="1.0" encoding="utf-8" standalone="yes"?>';

$media_categories = DB_PREFIX . 'pico_media_categories';

$categories = $db->force_multi_assoc('SELECT * FROM `'.$media_categories.'` WHERE `component_id`=? ORDER BY `position` ASC', $component_id);

$xml = '';

if ( (is_array($categories)) and (sizeof($categories) > 0) )
{
	foreach ($categories as $category)
	{
		$name = $category['title'];
		$url  = $body->url('includes/content/media/galleries/multiple/gallery.php?component_id=' . $component_id . '&instance_id='.$instance_id . '&category=' . $category['category_id']);
		$xml .= <<<XML
	<gallery name="$name" url="$url"/>

XML;
	}
}
else
{
	$url  = $body->url('includes/content/media/galleries/multiple/gallery.php?component_id=' . $component_id . '&instance_id='.$instance_id . '&category=0');
	$xml .= '<gallery name="Images" url="'.$url.'"/>';
}

?>

<galleries>
<?=$xml?>
</galleries>