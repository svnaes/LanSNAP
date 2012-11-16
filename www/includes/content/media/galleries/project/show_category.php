<?php
if (isset($_GET['category']))
{
	chdir('../../../../../');
	require_once('core.php');
	$category_id  = $_GET['category'];
	$component_id = $_GET['component_id'];
	$image_count  = $_GET['image_count'];
	$alias        = urldecode($_GET['alias']);
}
else
{
	$alias = CURRENT_ALIAS;
}

require_once('includes/content/media/functions.php');

$media_files      = DB_PREFIX . 'pico_media_files';
$media_categories = DB_PREFIX . 'pico_media_categories';


$num_images = $db->result('SELECT count(1) FROM `'.$media_files.'` WHERE `category_id`=?', $category_id);
$nav = '';

if ($image_count > 0)
{
	// prev
	$nav .= '<div class="prev click" onclick="MG_ShowProjectCategory('.$component_id.', '.$category_id.', '.($image_count-1).')"></div>';
}
else
{
	$nav .= '<div class="prev"></div>';
}

if ($num_images > $image_count+1)
{
	// next
	$nav .= '<div class="next click" onclick="MG_ShowProjectCategory('.$component_id.', '.$category_id.', '.($image_count+1).')"></div>';
}
else
{
	$nav .= '<div class="next"></div>';
}


for ($x = 0; $x < $num_images; $x++)
{
	$class = ($x == $image_count) ? 'active' : '';
	$link = $body->url($alias . '/category/' . $category_id . '/image/' . $x);
	$nav .= '<span class="click '.$class.'" onclick="MG_ShowProjectCategory('.$component_id.', '.$category_id.', '.$x.')"><a href="'.$link.'" onclick="return false">'.($x+1).'</a></span> ';
}

$image_info = $db->assoc('SELECT * FROM `'.$media_files.'` WHERE `category_id`=? AND `position`=?', $category_id, $image_count);

// format description

$desc = $image_info['description'];
//$desc = nl2br($desc);
$lines = explode("\n", $desc);
$desc  = '';
for ($x = 0; $x < sizeof($lines); $x++)
{
	if ($x == 0)
	{
		$desc .= '<b>'.$lines[$x].'</b><br />';
	}
	else
	{
		$desc .= $lines[$x].'<br />';
	}
}

?>
<div class="left_pane">
	<div class="image">
		<?php
		$image_file = get_gallery_image($image_info['file_id']);
		$image_path = $body->url($image_file);

		echo '<img src="'.$image_path.'" />';
		
		?>
	</div>
	<div class="description">
		<div class="nav"><?=$nav?></div>
		<?=$desc?>
	</div>
</div>
<div class="right_pane">
	<div id="project_category_description"><?=$db->result('SELECT `html` FROM `'.$media_categories.'` WHERE `category_id`=?', $category_id)?></div>
</div>
<div class="clear"></div>