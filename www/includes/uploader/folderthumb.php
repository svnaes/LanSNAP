<?php

function folder_thumb($folder)
{
	$base   = 'upload/';

	$full_path = $base . $folder;
	$files = array();

	if (is_dir($full_path))
	{
		// open the dir, get up to 4 images, make thumbnail for folder
		if ($handle = opendir($full_path))
		{
			while (false !== ($file = readdir($handle)))
			{
				$full_file = $full_path . '/' . $file;
				if (is_file($full_file))
				{
					$extension = strtolower(array_pop(explode('.', $full_file)));
					$allowed = array('jpg', 'jpeg', 'png', 'gif');
					if (in_array($extension, $allowed))
					{
						$files[] = $file;
						if (sizeof($files) == 4)
						{
							break;
						}
					}
				}
			}
			closedir($handle);
		}
		
		// see if we have a cached_file
		
		$cached_location = 'includes/uploader/thumbnails/' . md5(serialize($files)) . '.png';
		if (file_exists($cached_location))
		{
			return $cached_location;
		}
		
		//print_r($files);
		
		$positions = array(
			array(0,0),
			array(51, 0),
			array(0, 31),
			array(51, 31)
		);
		
		// create thumbnail resource
		$new_im = imagecreatetruecolor(101, 61);
		// add transparency
		$trans_colour = imagecolorallocatealpha($new_im, 255, 255, 255, 127);
		imagesavealpha($new_im, true);
		imagefill($new_im, 0, 0, $trans_colour);
		
		$desired_width  = 50;
		$desired_height = 30;
		
		for ($x = 0; $x < sizeof($files); $x++)
		{
			$pos  = $positions[$x];
			$file = $full_path . '/' . $files[$x];
			
			$mini_thumb = folder_minithumb($file);
			imagecopyresampled($new_im, $mini_thumb, $pos[0], $pos[1], 0, 0, $desired_width, $desired_height, $desired_width, $desired_height);
		}
		
		imagepng($new_im, $cached_location, 5, PNG_NO_FILTER);
		return $cached_location;
	}

}

function folder_minithumb($full_path)
{
	//error_reporting(E_ALL);
	$i = imagecreatefromstring(file_get_contents($full_path));
	
	$desired_width  = 50;
	$desired_height = 30;
	
	$width  = imagesx($i);
	$height = imagesy($i);
	
	$original_width  = $width;
	$original_height = $height;
	
	if ($width > $desired_width)
	{
		$mod    = $desired_width / $width;
		$width  = $desired_width;
		$height = $height * $mod;
	}
	
	if ($height > $desired_height)
	{
		$mod    = $desired_height / $height;
		$height = $desired_height;
		$width  = $width * $mod;
	}
	
	$final_width  = round($width);
	$final_height = round($height);
	
	// get the trim
	$x_trim = ($desired_width - $final_width) / 2;
	$y_trim = ($desired_height - $final_height) / 2;
	
	$new_im = imagecreatetruecolor($final_width, $final_height);
	$trans_colour = imagecolorallocatealpha($new_im, 255, 255, 255, 127);
	imagefill($new_im, 0, 0, $trans_colour);
	
	imagecopyresampled($new_im, $i, 0, 0, 0, 0, $final_width, $final_height, $original_width, $original_height);
	//imagepng($new_im, $dest, 0, PNG_NO_FILTER); return;
	
	$final_im = imagecreatetruecolor($desired_width, $desired_height);
	// make it transparent bg
	
	$trans_colour = imagecolorallocatealpha($final_im, 255, 255, 255, 127);
	imagesavealpha($final_im, true);
	imagefill($final_im, 0, 0, $trans_colour);
	
	/*
	$white = imagecolorallocate($final_im, 255, 255, 255);
	imagefill($final_im, 0, 0, $white);*/
	
	imagecopyresampled($final_im, $new_im, $x_trim, $y_trim, 0, 0, $final_width, $final_height, $final_width, $final_height);
	return $final_im;
}

function get_num_files($folder)
{
	$base   = 'upload/';

	$full_path = $base . $folder;
	$files = array();
	$counter = 0;

	if (is_dir($full_path))
	{
		// open the dir, get up to 4 images, make thumbnail for folder
		if ($handle = opendir($full_path))
		{
			while (false !== ($file = readdir($handle)))
			{
				if (($file != '.') and ($file != '..'))
				{
					$full_file = $full_path . '/' . $file;
					if (is_file($full_file))
					{
						$counter++;
					}
				}
			}
		}
	}
	
	return $counter;
}

?>