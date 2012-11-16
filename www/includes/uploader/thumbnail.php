<?php
chdir('../../');
$target = urldecode($_GET['target']);
if (!file_exists($target)) { exit(); }

$allowed_exts = array('jpg', 'jpeg', 'gif', 'bmp', 'png');
$ext = strtolower(array_pop(explode('.', $target)));

if (!in_array($ext, $allowed_exts)) { exit(); }

make_thumbnail($target, 100, 100);

function make_thumbnail($source, $max_width = 0, $max_height = 0)
{
	$i = imagecreatefromstring(file_get_contents($source));
	
	$original_width  = imagesx($i);
	$original_height = imagesy($i);
	
	if ( ($original_width > $max_width) and ($max_width != 0) )
	{
		$mod = $max_width / $original_width;
		$final_width = $max_width;
		$final_height = round($original_height * $mod);
	}
	else
	{
		// this is just here for the height portion of this code
		$final_width  = $original_width;
		$final_height = $original_height;
	}
	
	if ( ($final_height > $max_height) and ($max_height != 0) )
	{
		$mod = $max_height / $final_height;
		$final_height = $max_height;
		$final_width  = round($final_width * $mod);
	}
	
	$new_im = imagecreatetruecolor($final_width, $final_height);
	imagecopyresampled($new_im, $i, 0, 0, 0, 0, $final_width, $final_height, $original_width, $original_height);
	header('Content-type: image/jpeg');
	imagejpeg($new_im, null, 100);
}

?>