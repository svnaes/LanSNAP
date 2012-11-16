<?php

$func = 'file_upload_' . $component_id;

$user_image_table = DB_PREFIX . 'user_images';

require_once('includes/content/media/functions.php');

$upload_path = $body->url('includes/content/user_gallery/upload.php');
$uploader    = new Uploader($upload_path, $func, 'UG_FilesUploaded', '.jpg, .png, .gif, .jpeg', 'Image Files (jpg/png/gif)', '000000', 'ffffff');

// i dont feel like making this configurable right now

$images_per_row  = 4;
$rows_per_page   = 5;
$preview_width   = 160;
$preview_height  = 120;
$images_per_page = $images_per_row * $rows_per_page;

?>
<?=$uploader->Output()?>
<script type="text/javascript">
function <?=$func?>(filename) {
	UG_FileUploaded(filename, <?=$component_id?>);
}
</script>
<div class="clear"></div>
<?php
// show images, simply link to larger images

global $params;
$page_number = ($params[1] == 'page') ? $params[2] : 1;
$limit = ($page_number - 1) * $images_per_page;

$total_images = $db->result('SELECT count(1) FROM `'.$user_image_table.'` WHERE `component_id`=?', $component_id);

// build nav
$nav = '';

if ($limit != 0)
{
	// show back link
	$nav .= '<a href="'.$body->url(CURRENT_ALIAS . '/page/'.($page_number - 1)).'">[Back]</a> ';
}

if ($limit + $images_per_page < $total_images)
{
	// show next link
	$nav .= '<a href="'.$body->url(CURRENT_ALIAS . '/page/'.($page_number + 1)).'">[Next]</a> ';
}

$number_of_pages = ceil($total_images / $images_per_page);

for ($x = 0; $x < $number_of_pages; $x++)
{
	$p = $x + 1;
	$class = ($p == $page_number) ? 'active' : 'normal';
	$nav .= '<a class="'.$class.'" href="'.$body->url(CURRENT_ALIAS . '/page/'.$p).'">'.$p.'</a> ';
}

if ($number_of_pages > 1) { echo '<div class="ug_nav">'.$nav.'</div>'; }

$image_list = $db->force_multi_assoc('SELECT * FROM `'.$user_image_table.'` WHERE `component_id`=? ORDER BY `timestamp` DESC LIMIT '.$limit.', '.$images_per_page, $component_id);

if (!is_array($image_list))
{
	return;
}

echo '<div class="user_gallery">';

for ($x = 0; $x < sizeof($image_list); $x++)
{
	$image_info = $image_list[$x];
	$filename   = $image_info['filename'];
	$id         = $image_info['image_id'];
	
	// thumbnail is a png
	
	$source    = 'includes/content/user_gallery/storage/'.$id.'/'.$filename;
	$comp      = $id . '_' . $preview_width . '_' . $preview_height;
	$thumbnail = 'includes/content/user_gallery/thumbnails/' . md5($comp) . '.png';
	if (!is_file($thumbnail))
	{
		// make it
		make_new_image_ws($source, $thumbnail, $preview_width, $preview_height);
	}
	
	echo '<div class="thumbnail"><a href="'.$body->url($source).'"><img src="'.$body->url($thumbnail).'" /></a></div>';
	
	if ((($x+1) % $images_per_row == 0) and ($x > 0)) 
	{
		echo '<div class="clear"></div>';
	}
}

if ($x % $images_per_row != 0)
{
	echo '<div class="clear"></div>'; // final clear
}

echo '</div>';

if ($number_of_pages > 1) { echo '<div class="ug_nav">'.$nav.'</div>'; }

?>