<?php

// gets the folder of a given gallery by component_id
function get_gallery_folder($component_id)
{
	global $db, $body;
	// using globals to store this info as we need it an up to infinite number of times
	$key = 'gallery_folder_'.$component_id;
	if (!isset($GLOBALS[$key]))
	{
		$data = $db->result('SELECT `additional_info` FROM `'.DB_COMPONENT_TABLE.'` WHERE `component_id`=?', $component_id);
		$gallery_options = unserialize($data);
		$GLOBALS[$key] = $gallery_options['gallery_style'];
	}
	
	return $GLOBALS[$key];
}

// gets the base options stored in the config file of each gallery
function gallery_get_base_options($component_id)
{
	global $db, $body;
	// using globals to store this info as we need it an up to infinite number of times
	$folder = get_gallery_folder($component_id);
	$key    = 'gallery_options_' . $component_id;
	
	if (!isset($GLOBALS[$key]))
	{
		$config_file = 'includes/content/media/galleries/'.$folder.'/config.php';
		include($config_file);
		$GLOBALS[$key] = $options;
	}
	return $GLOBALS[$key];
}

// gets the user configurable settings for each gallery
function gallery_get_settings($component_id)
{
	global $db, $body;
	// using globals to store this info as we need it an up to infinite number of times
	$key = 'gallery_settings_' . $component_id;
	if (!isset($GLOBALS[$key]))
	{
		$folder      = get_gallery_folder($component_id);
		$config_file = 'includes/content/media/galleries/'.$folder.'/config.php';
		include($config_file);
		$values = array();
		
		// this loads the defaults in case they haven't been set yet
		if (sizeof($settings) > 0)
		{
			foreach ($settings as $key=>$data)
			{
				$val = (isset($gallery_options[$key])) ? $gallery_options[$key] : $data['default'];
				$values[$key] = $val;
			}
		}
	
		$GLOBALS[$key] = $values;
	}
	return $GLOBALS[$key];
}

// creates an image with a scaled width OR height
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

// creates an image with a given width and height
function make_finite_image($source, $dest, $final_width, $final_height)
{
	$i = imagecreatefromstring(file_get_contents($source));
	
	$original_width  = imagesx($i);
	$original_height = imagesy($i);
	
	$new_im = imagecreatetruecolor($final_width, $final_height);
	$trans_colour = imagecolorallocatealpha($new_im, 255, 255, 255, 127);
	imagesavealpha($new_im, true);
	imagefill($new_im, 0, 0, $trans_colour);
	
	imagecopyresampled($new_im, $i, 0, 0, 0, 0, $final_width, $final_height, $original_width, $original_height);
	imagepng($new_im, $dest);
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

// creates an image with a finite size, sections out what it can to produce the most logical image for given size, adds transparency where needed
function make_new_image_ws($full_path, $dest, $desired_width = 320, $desired_height = 140)
{
	//error_reporting(E_ALL);
	$i = imagecreatefromstring(file_get_contents($full_path));
	
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
	
	imagecopyresampled($final_im, $new_im, $x_trim, $y_trim, 0, 0, $final_width, $final_height, $final_width, $final_height);
	imagepng($final_im, $dest, 5, PNG_NO_FILTER);
}

function file_extension($filename)
{
	return strtolower(array_pop(explode('.', $filename)));
}

// same as get_gallery_image() but will check for thumbnail... not all galleries have thumbnails so this is a separate function
function get_gallery_thumb($image_id)
{
	global $db, $body;
	$media_files = DB_PREFIX . 'pico_media_files';
	
	// see if its cached
	
	if (isset($GLOBALS['image_info_'.$image_id]))
	{
		$image_info = $GLOBALS['image_info_'.$image_id];
	}
	else
	{
		$image_info = $db->assoc('SELECT * FROM `'.$media_files.'` WHERE `file_id`=?', $image_id);
		$GLOBALS['image_info_'.$image_id] = $image_info;
	}
	
	if ($image_info === false) { return false; }
	
	$instance_id  = $image_info['instance_id'];
	if (isset($GLOBALS['instance_component_'.$instance_id]))
	{
		$component_id = $GLOBALS['instance_component_'.$instance_id];
	}
	else
	{
		$component_id = $db->result('SELECT `component_id` FROM `'.DB_CONTENT.'` WHERE `instance_id`=?', $instance_id);
		$GLOBALS['instance_component_'.$instance_id] = $component_id;
	}
	
	$folder       = get_gallery_folder($component_id);
	$options      = gallery_get_base_options($component_id); // options set by the config file, may or may not be user configurable
	
	$master_file = 'includes/content/media/files/'.$image_id .'.'.$image_info['extension']; // these extension variables different on purpose
	$thumb_file  = 'includes/content/media/galleries/'.$folder.'/files/'.$image_id .'_thumb.jpg';
	$image_size  = @getimagesize($thumb_file);
	
	if ( (!file_exists($thumb_file)) or ($image_size[0] != $options['thumb_width']) or ($image_size[1] != $options['thumb_height']) )
	{
		// if not make a new one
		make_new_image($master_file, $thumb_file, $options['thumb_width'], $options['thumb_height']);
	}
	return $thumb_file;
}

// This function will check a given image id, ensure it exists, ensure that it's the right size, and if not
// will remake it so that it is
function get_gallery_image($image_id)
{
	global $db, $body;
	$media_files = DB_PREFIX . 'pico_media_files';
	
	if (isset($GLOBALS['image_info_'.$image_id]))
	{
		$image_info = $GLOBALS['image_info_'.$image_id];
	}
	else
	{
		$image_info = $db->assoc('SELECT * FROM `'.$media_files.'` WHERE `file_id`=?', $image_id);
		$GLOBALS['image_info_'.$image_id] = $image_info;
	}
	
	if ($image_info === false) { return false; }
	
	$instance_id  = $image_info['instance_id'];
	if (isset($GLOBALS['instance_component_'.$instance_id]))
	{
		$component_id = $GLOBALS['instance_component_'.$instance_id];
	}
	else
	{
		$component_id = $db->result('SELECT `component_id` FROM `'.DB_CONTENT.'` WHERE `instance_id`=?', $instance_id);
		$GLOBALS['instance_component_'.$instance_id] = $component_id;
	}
	
	$component_id = $db->result('SELECT `component_id` FROM `'.DB_CONTENT.'` WHERE `instance_id`=?', $instance_id);
	$folder       = get_gallery_folder($component_id);
	
	$options  = gallery_get_base_options($component_id); // options set by the config file, may or may not be user configurable
	$settings = gallery_get_settings($component_id); // end user configurable options, we are only using this to see if the user has selected a method of image resizing
	
	//echo '<pre>'.print_r($options, TRUE).'</pre>';
	
	// get the master image, and destination image
	$extension   = ($settings['imageUploadMode'] == 'pad') ? '.png' : '.jpg';
	$master_file = 'includes/content/media/files/'.$image_id .'.'.$image_info['extension']; // these extension variables different on purpose
	$image_file  = 'includes/content/media/galleries/'.$folder.'/files/'.$image_id . $extension;
	
	
	$image_size  = @getimagesize($image_file);
	
	if ( ($options['img_width'] != 0) and ($options['img_height'] != 0) )
	{
		if ( ($options['img_width'] != $image_size[0]) or ($options['img_height'] != $image_size[1]) )
		{
			if ($settings['imageUploadMode'] == 'pad')
			{
				make_new_image_ws($master_file, $image_file, $options['img_width'], $options['img_height']);
			}
			else
			{
				make_new_image($master_file, $image_file, $options['img_width'], $options['img_height']);
			}
		}
	}
	elseif ( ($options['img_width'] != 0) and ($options['img_height'] == 0) )
	{
		// scale by width
		if ($options['img_width'] != $image_size[0])
		{
			make_resized_image($master_file, $image_file, $options['img_width'], 0);
		}
	}
	elseif ( ($options['img_width'] == 0) and ($options['img_height'] != 0) )
	{
		// scale by height
		if ($options['img_height'] != $image_size[0])
		{
			make_resized_image($master_file, $image_file, 0, $options['img_height']);
		}
	}
	else
	{
		// just copy
		if (!file_exists($image_file))
		{
			copy($master_file, $image_file);
		}
	}
	
	return $image_file;
}
?>