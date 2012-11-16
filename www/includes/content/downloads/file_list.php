<?php

if (isset($_GET['instance_id']))
{
	$instance_id = $_GET['instance_id'];
	chdir('../../../');
	require_once('core.php');
	if (USER_ACCESS < 3) { exit(); }
	$download_files = DB_PREFIX . 'download_files';
}

require_once('includes/content/media/functions.php');

$files = $db->force_multi_assoc('SELECT * FROM `'.$download_files.'` WHERE `instance_id`=? ORDER BY `position` ASC', $instance_id);

$counter = 0;

if ( (is_array($files)) and (sizeof($files) > 0) )
{
	
	foreach ($files as $file)
	{
		$class = ($counter %2 == 0) ? 'file_a' : 'file_b';
		$counter++;
		$ext = strtolower(array_pop(explode('.', $file['file_name'])));
		
		if ($ext == 'jpg')
		{
			$filename = md5($file['file_name']) . '.jpg';
			$full_file = 'includes/content/downloads/thumbs/' . $filename;
			if (!file_exists($full_file))
			{
				$source = 'includes/content/downloads/storage/'.$file['file_id'].'/'.$file['file_name'];
				make_new_image_ws($source, $full_file, 30, 30);
			}
			$extra = '<img src="'.$body->url($full_file).'" hspace="3" />';
		}
		else
		{
			$extra = '';
		}
		$up     = '<img class="click icon" src="'.$body->url('includes/icons/arrow-up.png').'"   onclick="DL_MoveFile('.$file['file_id'].', \'up\')" />';
		$down   = '<img class="click icon" src="'.$body->url('includes/icons/arrow-down.png').'" onclick="DL_MoveFile('.$file['file_id'].', \'down\')" />';
		$delete = '<img class="click icon" src="'.$body->url('includes/icons/delete.png').'"     onclick="DL_DeleteFile('.$file['file_id'].')" />';
		$desc   = '<img class="click icon" src="'.$body->url('includes/icons/content.png').'" onclick="DL_EditDescription('.$file['file_id'].')" />';
		$description = $file['description'];
		$link = $body->url('includes/content/downloads/download.php?id='.$file['file_id']);
		
		
		
		echo '<div class="'.$class.'"><table border="0" cellpadding="0" cellspacing="0"><tr><td>'.$extra.'</td><td>'.$up.$down.$delete.$desc.'<a href="'.$link.'">'.$file['file_name'].'</a> Downloads: '.$file['num_downloads'].'</td></tr></table></div>';
	}
	
}
else
{
	echo $db->error;
}

//echo '<pre>'.print_r($files, TRUE).'</pre>';
?>