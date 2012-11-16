<?php
chdir('../../');
require_once('core.php');
require_once('includes/content/media/functions.php');

function my_asort($a, $b)
{
	$a = strtolower($a);
	$b = strtolower($b);
	return strcmp($a, $b);
}

function format_bytes($size) {
    $units = array(' B', ' KB', ' MB', ' GB', ' TB');
    for ($i = 0; $size >= 1024 && $i < 4; $i++) $size /= 1024;
    return round($size, 2).$units[$i];
}


if (USER_ACCESS < 3) { exit(); }

$base = 'upload/';
$path = urldecode($_GET['path']);

$full_path = $base . $path;

$mode = $_GET['mode'];
$fn   = $_GET['fn'];

$files = array();

if ($handle = opendir($full_path))
{
    while (false !== ($file = readdir($handle)))
	{
        if ($file != "." && $file != "..")
		{
			$full_file = $full_path . $file;
			if (is_file($full_file))
			{
				$files[] = $file;
			}
		}
	}
	closedir($handle);
}

usort($files, 'my_asort');

if (sizeof($files) > 0)
{
	/*
	$class = ($mode == 'image') ? 'thumbnail' : 'file';
	foreach ($files as $file)
	{
		echo '<div class="'.$class.'">'.$file.'</div>';
	}*/
	
	if ($mode != 'image')
	{
		echo '<table border="0" cellpadding="0" cellspacing="1" class="file_list">';
		echo '<tr><th width="20"></th><th>Filename</th><th width="100">File Size</th><th width="100">Last Modified</th></tr>';
	}
	
	foreach ($files as $file)
	{
		if ($mode == 'image')
		{
			$source = $full_path . $file;
			$mtime = filemtime($source);
			$extension = file_extension($file);
			$thumbnail = 'includes/uploader/thumbnails/ ' . (md5($mtime . '-' . $file) . '.png');
			
			$can_has_thumbs = array('png', 'jpeg', 'jpg', 'gif');
			$has_thumb = (in_array($extension, $can_has_thumbs)) ? TRUE : FALSE;
			
			$link = "window.opener.CKEDITOR.tools.callFunction($fn, '".addslashes($body->url($source))."', ''); window.close()";
			
			if ($has_thumb)
			{
				if (!file_exists($thumbnail))
				{
					make_new_image_ws($source, $thumbnail, 100, 100);
					chmod($thumbnail, 0666);
				}
				
				echo '<div class="thumbnail">
					<div class="delete_image" onclick="Browse_DeleteFile(\''.$file.'\')"></div>
					<div class="edit_image" onclick="Browse_EditFile(\''.$file.'\')"></div>
				<span class="click" onclick="'.$link.'"><img src="'.$body->url($thumbnail).'" /></span><br /><span class="click" onclick="'.$link.'">'.$file.'</span></div>';
			}
			else
			{
				echo '<div class="thumbnail">
					<div class="delete_image" onclick="Browse_DeleteFile(\''.$file.'\')"></div>
					<div class="edit_image" onclick="Browse_EditFile(\''.$file.'\')"></div>
				<div class="noprev">No preview available</div><br /><span class="click" onclick="'.$link.'">'.$file.'</span></div>';
			}
			
		}
		else
		{
			$source = $full_path . $file;
			$mtime = filemtime($source);
			$extension = file_extension($file);
			
			$link = "window.opener.CKEDITOR.tools.callFunction($fn, '".$body->url($source)."', ''); window.close()";
			
			// can do popup thumbnails, SHURE WHY NOT!
			
			$can_has_thumbs = array('png', 'jpeg', 'jpg', 'gif');
			$has_thumb = (in_array($extension, $can_has_thumbs)) ? TRUE : FALSE;
			
			if ($has_thumb)
			{
				$thumbnail = 'includes/uploader/thumbnails/' . (md5($mtime . '-' . $file) . '.png');
				if (!file_exists($thumbnail))
				{
					make_new_image_ws($source, $thumbnail, 100, 100);
					chmod($thumbnail, 0666);
				}
				list($width, $height) = getimagesize($source);
			}
			
			echo '
			<tr>
				<td><div class="delete_file" onclick="Browse_DeleteFile(\''.$file.'\')"></div></td>
				<td><span class="click '.(($has_thumb) ? 'show_thumb' : 'no_thumb').'" onclick="'.$link.'">
				'.(($has_thumb) ? '<div class="popup_thumb"><img src="'.$body->url($thumbnail).'" /><br />'.$width.' x '.$height.'</div>' : '').'
				'.$file.'</span>
				</td>
				<td>'.format_bytes(filesize($source)).'</td>
				<td>'.date('m/d/y h:i', $mtime).'</td>
			</tr>';
		}
	}
	
	if ($mode != 'image')
	{
		echo '</table>';
	}
}
else
{
	echo 'No files to display';
}
	

?>