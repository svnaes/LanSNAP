<?php
chdir('../../');
require_once('includes/uploader/folderthumb.php');
$base = 'upload/';

if (isset($_GET['path']))
{
	$path = urldecode($_GET['path']);
}

if (strlen($path) > 0)
{
	// show back
	$parts = explode('/', $path);
	array_pop($parts);
	array_pop($parts);

	$back = (sizeof($parts) > 0) ? implode('/', $parts) . '/' : '';
	
	echo '<div class="browse_back" onclick="Browser_LoadFolder(\''.$back.'\')">Back</div>';
}

// list all the folders

$full_path = $base . $path;

echo '<input type="hidden" id="browser_current_path" value="'.$path.'" />';

$dirs = array();
if ($handle = opendir($full_path))
{
    while (false !== ($file = readdir($handle)))
	{
        if ($file != "." && $file != "..")
		{
			$full_file = $full_path . $file;
			if (is_dir($full_file))
			{
				$dirs[] = $file;
			}
		}
	}
	closedir($handle);
}

natcasesort($dirs);

if (sizeof($dirs) > 0)
{
	foreach ($dirs as $dir)
	{
		$link = $path . $dir . '/';
		
		if ($_GET['mode'] == 'image')
		{
			$folder_thumb = basename(folder_thumb($link));
			echo '<div class="browse_folder" onclick="Browser_LoadFolder(\''.$link.'\')">
				<div class="thumb"><img src="thumbnails/'.$folder_thumb.'" /></div>
			'.$dir.'</div>';
		}
		else
		{
			$num_files = get_num_files($link);
			$file      = ($num_files == '1') ? 'file' : 'files';
			echo '<div class="browse_folder" onclick="Browser_LoadFolder(\''.$link.'\')">
				<div class="thumb_text">'.$num_files.' '.$file.'</div>
			'.$dir.'</div>';
		}
	}
}
else
{
	echo 'No subfolders';
}
?>