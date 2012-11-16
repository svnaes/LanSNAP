<?php

require_once('includes/content/media/functions.php');
$media_files = DB_PREFIX . 'pico_media_files';
$image_data = $db->force_multi_assoc('SELECT * FROM `'.$media_files.'` WHERE `instance_id`=? ORDER BY `position` ASC', $instance_id);

$settings = gallery_get_settings($component_id);
//echo '<pre>'.print_r($settings, TRUE).'</pre>';

$output = '';
$image_output = '';

if ( (is_array($image_data)) and (sizeof($image_data) > 0) )
{
	$output = '<table class="grid_gallery" cellpadding="0" cellspacing="0" border="0"><tr><td class="upper outer_left"></td>';
	for ($x = 0; $x < $settings['num_per_row']; $x++)
	{
		$output .= '<td class="upper"></td>';
	}
	$output .= '<td class="upper outer_right"></td></tr>';
	
	$counter = 0;
	foreach ($image_data as $i)
	{
		if ($counter == 0)
		{
			$output .= '<tr><td class="mid outer_left"></td>';
		}
		$image_file = get_gallery_image($i['file_id']);
		$thumb_file = get_gallery_thumb($i['file_id']);
		
		if ($image_file != false)
		{
			$image_path  = $body->url($image_file);
			$thumb_path  = $body->url($thumb_file);
			
			$desc = $i['description'];
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
			
			
			$output .= '<td class="thumbnail"><img src="'.$thumb_path.'" onmouseover="MG_ShowGridImage('.$i['file_id'].')" /></td>';
			
			$image_output .= '<div id="grid'.$i['file_id'].'" class="grid_image" style="display: none">
			<div class="image"><img src="'.$image_path.'" /></div>
			<div class="description">'.$desc.'</div>
			</div>';
		}
		
		$counter++;
		if ($counter == $settings['num_per_row'])
		{
			$output .= '<td class="mid outer_right"></td></tr>';
			$counter = 0;
		}
	}
	
	if ($counter != 0)
	{
		$remaining = $settings['num_per_row'] - $counter;
		for ($x = 0; $x < $remaining; $x++)
		{
			$output .= '<td class="thumbnail empty">&nbsp;</td>';
		}
		$output .= '<td class="mid outer_right"></td></tr>';
	}
	
	// final row
	
	$output .= '<td class="lower_outer_left"></td>';
	for ($x = 0; $x < $settings['num_per_row']; $x++)
	{
		$output .= '<td class="lower"></td>';
	}
	$output .= '<td class="lower_outer_right"></td></tr>';
	
	$output .= '</table>';
	
	
	
	//echo $output;
}
?>
<div class="gallery_display">
	<?=$image_output?>
</div>
<div class="gallery_preview">
	<?=$output?>
</div>
<div class="clear"></div>
