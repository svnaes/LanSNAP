<?php
function make_resized_image($source, $dest, $desired_width = 0, $desired_height = 0)
{
	$i = imagecreatefromstring(file_get_contents($source));
	
	$original_width  = imagesx($i);
	$original_height = imagesy($i);
	
	if ($desired_width != 0)
	{
		// scale by width
		$mod = $desired_width / $original_width;
		$final_width = $desired_width;
		$final_height = round($original_height * $mod);
	}
	else
	{
		// scale by height
		$mod = $desired_height / $original_height;
		$final_height = $desired_height;
		$final_width = round($original_width * $mod);
	}
	
	$new_im = imagecreatetruecolor($final_width, $final_height);
	imagecopyresampled($new_im, $i, 0, 0, 0, 0, $final_width, $final_height, $original_width, $original_height);
	imagejpeg($new_im, $dest, 100);
}

// creates an image with a finite size, sections out what it can to produce the most logical image for given size
function make_new_image($full_path, $dest, $final_width = 320, $final_height = 140)
{
	$i = imagecreatefromstring(file_get_contents($full_path));
	
	$original_width  = imagesx($i);
	$original_height = imagesy($i);
	
	$x_mod = $final_width / $original_width;
	$new_width  = $final_width;
	$new_height = $original_height * $x_mod;
	
	if ($new_height > $final_height)
	{
		$y_trim = ($new_height - $final_height) / 2;
		$x_trim = 0;
	}
	else
	{
		$y_trim = 0;
		if ($new_height < $final_height)
		{
			$y_mod = $final_height / $new_height;
			$new_width = $y_mod * $new_width;
			$x_trim = ($new_width - $final_width) / 2;
			$new_height = $final_height;
		}
	}
	
	$new_width = round($new_width);
	$new_height = round($new_height);
	
	$new_im = imagecreatetruecolor($new_width, $new_height);
	imagecopyresampled($new_im, $i, 0, 0, 0, 0, $new_width, $new_height, $original_width, $original_height);
	
	$final_im = imagecreatetruecolor($final_width, $final_height);
	imagecopyresampled($final_im, $new_im, 0, 0, $x_trim, $y_trim, $final_width, $final_height, $final_width, $final_height);
	
	imagejpeg($final_im, $dest, 100);
}
?>