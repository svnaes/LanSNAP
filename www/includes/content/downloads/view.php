<?php
$download_files = DB_PREFIX . 'download_files';
$files = $db->force_multi_assoc('SELECT * FROM `'.$download_files.'` WHERE `instance_id`=? ORDER BY `position` ASC', $instance_id);

require_once('includes/content/media/functions.php');
if ( (is_array($files)) and (sizeof($files) > 0) ){
	echo '<ul>';
	foreach ($files as $file)
	{
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
		$i = (strlen($file['description']) > 0) ? $file['description'] : $file['file_name'];
		$link = $body->url('includes/content/downloads/download.php?id='.$file['file_id']);
		//$line = '<li><table border="0" cellpadding="0" cellspacing="0"><tr><td>'.$extra.'</td><td>Catalog Number: '.$i.' - </td></tr></table></li>';
		
		$line = '<li>'.$file['html_description'].'
			<table border="0" cellpadding="1" cellspacing="1"><tr><td>'.$extra.'</td><td>'.$file['file_name'].'</td><td><a href="'.$link.'">[download]</a></td></tr></table>';
		echo $line;	
	}
echo '</ul>';
}?>