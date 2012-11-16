<?php
chdir('../../');
require_once('core.php');
if (USER_ACCESS < 3) { exit(); }

$action = $_REQUEST['page_action'];

if ($action == 'new_folder')
{
	$base = 'upload/';
	$path = urldecode($_GET['path']);

	$full_path = $base . $path;
	chdir($full_path);
	
	$folder_name = trim(urldecode($_GET['folder_name']));
	if (strlen($folder_name) > 0)
	{
		mkdir($folder_name);
		chmod($folder_name, 0777);
	}
}
elseif ($action == 'new_file')
{
	$base = 'upload/';
	$path = urldecode($_GET['path']);

	$full_path = $base . $path;
	
	$filename = urldecode($_GET['filename']);
	$full_file = 'includes/uploader/tmp/' . $filename;
	$new_file  = $full_path . $filename;
	if (file_exists($full_file))
	{
		rename($full_file, $new_file);
		chmod($new_file, 0666);
	}
}
elseif ($action == 'delete_file')
{
	$base = 'upload/';
	$path = urldecode($_GET['path']);

	$full_path = $base . $path;
	
	$filename = urldecode($_GET['filename']);
	$full_file = $full_path . $filename;
	
	if (file_exists($full_file))
	{
		unlink($full_file);
	}
}
elseif ($action == 'edit_image')
{
	echo '<div class="click" onclick="Browse_BackFromEdit()">[back]</div>';
	$filename = urldecode($_GET['filename']);
	
	$full_image = 'upload/' . urldecode($_GET['path']) . $filename;
	if (file_exists($full_image))
	{
		list($width, $height) = getimagesize($full_image);
		echo '<input type="hidden" id="image_orig_src" value="'.$full_image.'" />';
		echo '<div id="edit_canvas" style="width: '.$width.'px; height: '.$height.'px;">
			<img src="'.$body->url($full_image).'" id="edit_image" onclick="Edit_ImageDragToggle(event)" onmouseout="Edit_ImageDragStop()" />
			<div id="edit_crop" onclick="Edit_CropDragToggle(event)" onmouseout="Edit_CropDragStop()"></div>
		</div>';
	}
	else
	{
		echo 'Invalid Image';
	}
}
elseif ($action == 'resample')
{
	$image_path = urldecode($_GET['file']);
	$width = $_GET['width'];
	$height = $_GET['height'];
	require_once('includes/content/media/functions.php');
	header('Content-type: image/png');
	make_finite_image($image_path, null, $width, $height);
	exit();
}
elseif ($action == 'image_controls')
{
	$filename = urldecode($_GET['filename']);
	$full_image = 'upload/' . urldecode($_GET['path']) . $filename;
	if (file_exists($full_image))
	{
		$final_width  = $_GET['final_width'];
		$final_height = $_GET['final_height'];
		
		$disabled = ( ($final_width == 0) and ($final_height == 0) ) ? 'disabled="disabled"' : '';
		$save_disabled = ( ($final_width != 0) or ($final_height != 0) ) ? 'disabled="disabled"' : '';
	
		list($width, $height) = getimagesize($full_image);
		echo '
		<div class="canvas_size">
			<input type="hidden" id="orig_width" value="'.$width.'" />
			<input type="hidden" id="orig_height" value="'.$height.'" />
			<input type="hidden" id="current_image_width" value="'.$width.'" />
			<input type="hidden" id="current_image_height" value="'.$height.'" />
			<input type="hidden" id="current_canvas_width" value="'.$width.'" />
			<input type="hidden" id="current_canvas_height" value="'.$height.'" />
			<table border="0" cellpadding="0" cellspacing="1">
			<tr>
				<td width="50">Width</td>
				<td><input tabindex="1" type="text" size="5" id="edit_width" value="'.$width.'" onkeyup="Edit_Calculate(\'w\')" onchange="Edit_Calculate(\'w\')" /></td>
				<td rowspan="3" style="text-align: center">
					<input type="radio" name="canvas_or_image" onclick="Edit_UpdateSizes()" id="coi1" value="0" /> Canvas
					<input type="radio" name="canvas_or_image" onclick="Edit_UpdateSizes()" id="coi2" value="1" /> Image
					<input type="radio" name="canvas_or_image" onclick="Edit_UpdateSizes()" id="coi3" value="2" checked="checked" /> Both
					<br />
					<button onclick="Edit_Resize()">Resize</button>
					<button '.$disabled.' onclick="Edit_Optimize()">Optimize Image</button><br />
					<div style="width: 10px; float: left">-</div>
					<button class="small" onclick="Edit_ResizePercent(.25)">25%</button>
					<button class="small" onclick="Edit_ResizePercent(.50)">50%</button>
					<button class="small" onclick="Edit_ResizePercent(.75)">75%</button>
					<button class="small" onclick="Edit_ResizePercent(.90)">90%</button><br />
					<div style="width: 10px; float: left">+</div>
					<button class="small" onclick="Edit_ResizePercent(1.10)">10%</button>
					<button class="small" onclick="Edit_ResizePercent(1.25)">25%</button>
					<button class="small" onclick="Edit_ResizePercent(1.50)">50%</button>
					<button class="small" onclick="Edit_ResizePercent(2)">2 x</button>
				</td>
			</tr>
			<tr>
				<td width="50">Height</td>
				<td><input tabindex="2" type="text" size="5" id="edit_height" value="'.$height.'" onkeyup="Edit_Calculate(\'h\')" onchange="Edit_Calculate(\'h\')"  /></td>
			</tr>
			<tr>
				<td colspan="2">Lock Aspect Ratio <input type="checkbox" id="ratio_lock" checked="checked" /></td>
			</tr>
			</table>
		</div>
		<button id="save_button" '.$save_disabled.' onclick="Edit_Save(\''.$filename.'\')">Save and Add</button>
		
		<!--div class="crop">
			<div class="bold">Crop Image</div>
			<table border="0" cellpadding="0" cellspacing="1">
			<tr>
				<td>Width</td>
				<td><input tabindex="3" type="text" size="5" id="crop_width" value="'.$crop_width.'" /></td>
				<td rowspan="2">
					<button onclick="Edit_Crop()">Show/Hide Crop Tool</button>
					<button onclick="Edit_CropComplete()">Crop Selection</button>
				</td>
			</tr>
			<tr>
				<td>Height</td>
				<td><input tabindex="4" type="text" size="5" id="crop_height" value="'.$crop_height.'" /></td>
			</tr>
			</table>
		</div-->
		';
		
	}
}
elseif ($action == 'get_image_name')
{
	$path     = urldecode($_GET['path']);
	$filename = urldecode($_GET['filename']);
	
	$parts = explode('.', $filename);
	array_pop($parts);
	$file_no_ext = implode('.', $parts);
	
	$ext = 'png';
	$count = 1;
	
	do
	{
		$new_file = 'upload/' . $path . $file_no_ext . ' ('.$count.').' . $ext;
		$count++;
	} while (file_exists($new_file));
	
	echo basename($new_file);
	exit();
}
elseif ($action == 'save_image')
{
	// size of the container, final image size
	$canvas_width  = $_GET['canvas_width'];
	$canvas_height = $_GET['canvas_height'];
	
	// size of the image INSIDE of the container, will have to crop/etc to make fit in the above container as desired
	$image_width  = $_GET['image_width'];
	$image_height = $_GET['image_height'];
	
	// path, filename, unedited source to the original file
	$path     = urldecode($_GET['path']);
	$filename = urldecode($_GET['filename']);
	$source   = urldecode($_GET['source']);
	
	// x,y to where the above image will start in canvas
	$left = $_GET['left'];
	$top  = $_GET['top'];
	
	// make new filename
	$count = 1;
	
	$parts = explode('.', $filename);
	array_pop($parts);
	$file_no_ext = implode('.', $parts);
	
	$ext = 'png';
	
	$finished_filename = urldecode($_GET['finished_filename']);
	$full_finished_filename = 'upload/' . $path . $finished_filename;
	
	if (!file_exists($full_finished_filename))
	{
		$new_file = $full_finished_filename;
	}
	else
	{
		do
		{
			$new_file = 'upload/' . $path . $file_no_ext . ' ('.$count.').' . $ext;
			$count++;
		} while (file_exists($new_file));
	}
	
	$im = imagecreatefromstring(file_get_contents($source));
	
	$original_width  = imagesx($im);
	$original_height = imagesy($im);
	
	// need to copy to temp image with sizes $image_width and $image_height
	$temp_im = imagecreatetruecolor($image_width, $image_height);
	imagecopyresampled($temp_im, $im, 0, 0, 0, 0, $image_width, $image_height, $original_width, $original_height);
	imagedestroy($im);
	
	$new_im = imagecreatetruecolor($canvas_width, $canvas_height);
	//$white = imagecolorallocate($new_im, 255, 255, 255);
	//imagefill($new_im, 0, 0, $white);
	// add transparency
	
	$trans_colour = imagecolorallocatealpha($new_im, 255, 255, 255, 127);
	imagesavealpha($new_im, true);
	imagefill($new_im, 0, 0, $trans_colour);
	
	// copy $temp_im, into $new_im
	
	// if left is less than zero, the image is actually behind the page
	if ($left < 0)
	{
		$src_x = abs($left);
		$dest_x = 0;
	}
	else
	{
		$src_x = 0;
		$dest_x = $left;
	}
	
	if ($top < 0)
	{
		$src_y = abs($top);
		$dest_y = 0;
	}
	else
	{
		$src_y = 0;
		$dest_y = $top;
	}
	
	$check_x = $dest_x + $image_width - $start_x;
	// if that turns out to be too big for the canvas, it has to be reduced
	if ($check_x > $canvas_width)
	{
		$dst_w = $canvas_width;
	}
	else
	{
		$dst_w = $image_width;
	}
	
	$check_y = $dest_y + $image_height - $start_y;
	// if that turns out to be too big for the canvas, it has to be reduced
	if ($check_y > $canvas_height)
	{
		$dst_h = $canvas_height;
	}
	else
	{
		$dst_h = $image_height;
	}
	
	/*
	$src_w = $start_x + $canvas_width;
	$src_h = $start_y + $canvas_height;
	
	if ($src_w > $image_width) { $src_w = $image_width; }
	if ($src_h > $image_height) { $src_h = $image_height; }*/
	
	$src_w = ($image_width > $canvas_width) ? $canvas_width : $image_width;
	$src_h = ($image_height > $canvas_height) ? $canvas_height : $image_height;
	
	/*
	echo "
	dest_x: $dest_x,
	dest_y: $dest_y,
	src_x: $src_x,
	src_y: $src_y,
	dst_w: $dst_w,
	dst_h: $dst_h,
	src_w: $src_w,
	src_h: $src_h,
	";*/
	
	if (substr($new_file, -4) != '.png')
	{
		$new_file .= '.png';
	}
	
	imagecopyresampled($new_im, $temp_im, $dest_x, $dest_y, $src_x, $src_y, $dst_w, $dst_h, $src_w, $src_h);
	imagepng($new_im, $new_file);
	imagedestroy($temp_im);
	
	echo $new_file;
	chmod($new_file, 0666);
}
?>