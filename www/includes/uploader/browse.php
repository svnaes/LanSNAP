<?php

$mode = (isset($_GET['mode'])) ? $_GET['mode'] : 'all';
$function_num = (isset($_GET['CKEditorFuncNum'])) ? $_GET['CKEditorFuncNum'] : null;

if ($mode == 'image')
{
	include('browse_manager.php');
	return;
	
	?>
<frameset cols="150, *, 150">
	<frame src="browse_folder.php?mode=image" id="browse_folder" noresize="noresize" />
	<frameset rows="*, 150">
		<frame src="browse_pane.php" name="browse_pane" noresize="noresize" />
		<frame src="browse_upload.php?mode=image" name="browse_upload" noresize="noresize" />
	</frameset>
	<frame src="browse_queue.php" name="browse_queue" noresize="noresize" />
</frameset> 
	<?php
	return;
}
else
{
	include('browse_manager.php');
	return;
	
	?>
<frameset cols="150, *, 150">
	<frame src="browse_folder.php?mode=file" id="browse_folder" noresize="noresize" />
	<frameset rows="*, 150">
		<frame src="browse_pane.php" name="browse_pane" noresize="noresize" />
		<frame src="browse_upload.php?mode=file" name="browse_upload" noresize="noresize" />
	</frameset>
	<frame src="browse_queue.php" name="browse_queue" noresize="noresize" />
</frameset> 
	<?php
	return;
}


/*
$mode = (isset($_GET['mode'])) ? $_GET['mode'] : 'all';
$func = $_GET['CKEditorFuncNum'];
$ru   = $_GET['ru'];

if (!is_numeric($func)) { exit(); }

chdir('../../');
require_once('core.php');

if (USER_ACCESS == 0) { exit(); }

if (isset($_GET['dir']))
{
	$base_dir = $_GET['dir'];
	if (!file_exists($base_dir)) { exit(); }
}
else
{
	$base_dir = 'upload/';
}

if (substr($base_dir, 0, 7) != 'upload/') { exit(); }

if (isset($_FILES['upload']))
{
	if ($_FILES['upload']['error'] != 0)
	{
		$error = 'There was an error uploading your file. Code: ' . $_FILES['upload']['error'];
	}
	else
	{
		$target = $base_dir . $_FILES['upload']['name'];
		$continue = false;
		if (file_exists($target))
		{
			if (!is_writable($target))
			{
				$error = $_FILES['upload']['name'] . ' already exists and cannot be overwritten';
			}
			else
			{
				$continue = true;
			}
		}
		else
		{
			$continue = true;
		}
		
		if ($continue)
		{
			move_uploaded_file($_FILES['upload']['tmp_name'], $target);
		}
	}
}

if (isset($_POST['new_folder']))
{
	$new_folder = $base_dir . $_POST['new_folder'];
	if (file_exists($new_folder))
	{
		$error = 'That folder exists';
	}
	else
	{
		mkdir($new_folder);
	}
}

if (isset($_GET['delete']))
{
	$delete = urldecode($_GET['delete']);
	if (file_exists($delete))
	{
		unlink($delete);
	}
}

$base_url = $body->url('includes/uploader/browse.php?mode='.$mode.'&CKEditor=ck_text&CKEditorFuncNum='.$func.'&langCode='.$_GET['langCode']);

$files = array();
$dirs  = array();

if ($handle = opendir($base_dir))
{
    while (false !== ($file = readdir($handle)))
	{
        if ($file != "." && $file != "..")
		{
			$full_file = $base_dir . $file;
			if (is_dir($full_file))
			{
				$dirs[] = $file;
			}
			else
			{
				$files[] = $file;
			}
		}
	}
	closedir($handle);
}

$pieces = explode('/', $base_dir);
array_pop($pieces);
array_pop($pieces);
$back   = implode('/', $pieces) .'/';

$back_url = $base_url . '&dir=' . urlencode($back);

$list = (strlen($base_dir) > 7) ? '<li><a href="'.$back_url.'">... up one level</a></li>' : '';
natcasesort($dirs);
natcasesort($files);
if (sizeof($dirs) > 0)
{
	foreach ($dirs as $dir)
	{
		$list .= '<li><a href="'.$_SERVER['REQUEST_URI'].'&dir='.urlencode($base_dir.$dir.'/').'">['.$dir.']</a></li>';
	}
}

if (sizeof($files) > 0)
{
	if ($mode == 'image')
	{
		$allowed_exts = array('jpg', 'jpeg', 'gif', 'bmp', 'png');
		
		foreach ($files as $file)
		{
			$ext = strtolower(array_pop(explode('.', $file)));
			
			if (in_array($ext, $allowed_exts))
			{
				// only images
				
				
				$full_file = $body->url($base_dir . $file);
				$full_file = str_replace("'", "\\'", $full_file);
				
				$target_file = urlencode($base_dir . $file);
				
				$link = "window.opener.CKEDITOR.tools.callFunction($func, '$full_file', ''); window.close()";
				$img = '<span class="click" onclick="'.$link.'"><img src="'.$body->url('includes/uploader/thumbnail.php?target='.$target_file).'" /></span>';
				
				$url = $base_url . '&delete=' . $target_file . '&dir=' . urlencode($base_dir);
				$delete = (USER_ACCESS > 2) ? '<a href="'.$url.'" onclick="return confirm(\'Are you sure you want to delete this file?\')"><img border="0" class="icon click" src="'.$body->url('includes/icons/delete.png').'" /></a>' : '';
				
				$list .= '<li class="thumb"><div class="image"><table cellpadding="0" cellspacing="0" border="0"><tr><td>'.$img.'</td></tr></table></div><div class="text"><table border="0" cellpadding="0" cellspacing="1"><tr><td><span class="click" onclick="'.$link.'">'.$file.'</span></td><td>'.$delete.'</td></tr></table></div></li>';
			}
			
			//
			//$list .= '<li class="click" onclick="'.$link.'">'.$file.'</li>';
		}
	}
	else
	{
		// just list the files
		foreach ($files as $file)
		{
			$full_file = $body->url($base_dir . $file);
			$full_file = str_replace("'", "\\'", $full_file);
			
			$target_file = urlencode($base_dir . $file);
			$url = $base_url . '&delete=' . $target_file . '&dir=' . urlencode($base_dir);
			$delete = (USER_ACCESS > 2) ? '<a href="'.$url.'" onclick="return confirm(\'Are you sure you want to delete this file?\')"><img border="0" class="icon click" src="'.$body->url('includes/icons/delete.png').'" /></a>' : '';

			
			$link = "window.opener.CKEDITOR.tools.callFunction($func, '$full_file', ''); window.close()";
			$list .= '<li><table cellpadding="0" cellspacing="2" border="0"><tr><td><span class="click" onclick="'.$link.'">'.$file.'</span></td><td>'.$delete.'</td></tr></table></li>';
		}
	}
}

$error_msg = (isset($error)) ? '<div class="error">'.$error.'</div>' :'';

?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
<head>
	<title>Pico Uploader</title>
	<link href="<?=$body->url('includes/uploader/style.css')?>" type="text/css" rel="stylesheet" />
	<script type="text/javascript" src="<?=$body->url('site/javascript.php?page_id=0')?>"></script>
	<!--script type="text/javascript" src="<?=$body->url('includes/content/ckhtml/ckeditor/ckeditor.js')?>"></script-->
</head>
<body>
<?=$error_msg?>
<div id="upload_pane">
	<table border="0" cellpadding="2" cellspacing="0">
	<tr>
		<td>
			<form method="post" action="<?=$_SERVER['REQUEST_URI']?>" enctype="multipart/form-data">
			<input type="hidden" name="max_file_size" value="104857600" /><!-- 100 MB limit -->
			<input type="file" name="upload" /> <input type="submit" value="Upload" />
			</form>
		</td><td>&raquo; or &laquo;</td><td>
			<form method="post" action="<?=$_SERVER['REQUEST_URI']?>" enctype="multipart/form-data">
			New Folder: <input type="text" name="new_folder" value="" />
			<input type="submit" value="Submit" /></form>
		</td>
	</tr>
	</table>
</div>
<ul id="file_list">
<?=$list?>
</ul>
</body>
</html>
*/
?>