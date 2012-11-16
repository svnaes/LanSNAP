<?php

echo '???';
return;

// get all the files
require_once('includes/content/media/functions.php');

$gallery_settings  = gallery_get_settings($component_id); // loads the settings as well as defaults if needed
$media_files       = DB_PREFIX . 'pico_media_files';

$images  = $db->force_multi_assoc('SELECT * FROM `'.$media_files.'` WHERE `instance_id`=? ORDER BY `position` ASC', $instance_id);

$image_output = '';
$thumb_output = '';

if ( (sizeof($images) > 0) and (is_array($images)) )
{
	$counter = 0;
	$row_num = 0;
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
			$title       = (strlen($image['title']) > 0) ? $image['title'] : '';
			
			$extra = ($counter == 0) ? 'style="display: block"' : '';
			
			if ($counter % $gallery_settings['num_thumbnails'] == 0)
			{
				$thumb_output .= '<div class="jscript_thumbrow" id="jscript_thumbrow_'.$row_num.'" '.$extra.'>';
			}
			
			//$description = nl2br($description);
			$description = str_replace('[link]', '<a href="'.$url.'">', $description);
			$description = str_replace('[/link]', '</a>', $description);
			
			$image_output .= '<div class="jscript_image" '.$extra.' id="jscript_'.$image['file_id'].'">
				<input type="hidden" id="jscript_image_id_'.$counter.'" value="'.$image['file_id'].'" />
				<div class="title">'.$title.'</div>
				<div class="description">'.$description.'</div>
				<img src="'.$image_path.'" />
			</div>';
			$thumb_output .= '<div class="thumbnail" onclick="JScriptG_ShowImage('.$image['file_id'].', 1)"><img src="'.$thumb_path.'" /></div>';
			$counter++;
			
			if (($counter % $gallery_settings['num_thumbnails'] == 0) and ($counter > 0))
			{
				$thumb_output .= '</div>';
				$row_num++;
			}
		}
	}
	if ($counter % $gallery_settings['num_thumbnails'] != 0)
	{
		$thumb_output .= '</div>';
	}
}

echo '<div class="jscript_gallery">
	<input type="hidden" id="jscript_page_no" value="0" />
	<div class="images">'.$image_output.'</div>
	<div class="thumbnails">
		<div class="previous" onclick="JScriptG_ShowThumbPrevious()"><img src="'.$body->url('includes/content/media/galleries/jscript/previous.png').'" /></div>
		<div class="next" onclick="JScriptG_ShowThumbNext()"><img src="'.$body->url('includes/content/media/galleries/jscript/next.png').'" /></div>
		'.$thumb_output.'
	</div>
</div>';
?>