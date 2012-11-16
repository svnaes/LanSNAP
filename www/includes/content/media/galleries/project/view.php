<?php
$media_files      = DB_PREFIX . 'pico_media_files';
$media_categories = DB_PREFIX . 'pico_media_categories';
?>
<div class="project_gallery">
	<div class="categories">
<?php
// list categories

global $params;

$categories = $db->force_multi_assoc('SELECT * FROM `'.$media_categories.'` WHERE `component_id`=? ORDER BY `position` ASC', $component_id);
$counter = 0;
if ( (is_array($categories)) and (sizeof($categories) > 0) )
{
	echo '<ul>';
	foreach ($categories as $category)
	{
		$link = $body->url(CURRENT_ALIAS . '/category/' . $category['category_id']);
		
		if (!isset($category_id))
		{
			$category_id  = ($params[1] == 'category') ? $params[2] : $category['category_id'];
		}
		$name   = $category['title'];
		$class  = ($counter == 0) ? 'active' : '';
		$counter++;
		echo '<li class="click catlist" onclick="MG_ShowProjectCategory('.$component_id.', '.$category['category_id'].')"><a class="'.$class.'" href="'.$link.'" onclick="MG_Active(this); return false">'.$name.'</a></li>';
	}
	echo '</ul>';
}
	
?>
	</div>
	<div id="gallery_description_<?=$component_id?>">
<?php
// include first category
	$image_count = ($params[3] == 'image') ? $params[4] : 0;
	include('includes/content/media/galleries/project/show_category.php');
?>
	</div>
	<div class="clear"></div>
</div>