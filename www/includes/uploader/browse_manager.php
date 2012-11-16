<?php
chdir('../../');
require_once('core.php');
if (USER_ACCESS < 3) { exit(); }

$mode = isset($_GET['mode']) ? $_GET['mode'] : 'all';

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
<head>
	<title><?=$body->title?></title>
	<link href="<?=$body->url('includes/uploader/browse_manager.css')?>" type="text/css" rel="stylesheet" />
	<script type="text/javascript">
	/* global variables */
	var CURRENT_PAGE = 0;
	var CURRENT_ALIAS = '';
	var REQUEST_URI = '<?=$_SERVER['REQUEST_URI']?>';
	var BASE_URL = '<?=$body->base_url?>';
	</script>
	<script type="text/javascript" src="<?=$body->url('site/javascript.php?mode=reload')?>"></script>
</head>
<body onmousemove="Edit_CropDrag(event)">
	<input type="hidden" id="browse_mode" value="<?=$mode?>" />
	<div id="browse-manager">
		<?php
		$w = (isset($_GET['width'])) ? $_GET['width'] : 0;
		$h = (isset($_GET['height'])) ? $_GET['height'] : 0;
		$cb = urldecode($_GET['callback']);
		
		if ($function_num != null)
		{
			$cb = "window.opener.CKEDITOR.tools.callFunction(".$function_num.", '%1', ''); window.close()";
			$max_queue = 1;
		}
		
		echo '<input type="hidden" id="max_queue" value="'.$max_queue.'" />';
		echo '<input type="hidden" id="callback" value="'.$cb.'" />';
		echo '<input type="hidden" id="final_width" value="'.$w.'" />';
		echo '<input type="hidden" id="final_height" value="'.$h.'" />';
		echo '<input type="hidden" id="fn" value="'.$function_num.'" />';
		?>
		<div id="browse-folder"></div>
		<div id="new-folder">
			<input type="text" value="Enter new folder" onfocus="this.value=''" id="new_folder_name" /><br />
			<button onclick="Browser_NewFolder()">Make Folder</button>
		</div>
		
		<div id="browse-pane"></div>
		<div id="browse-upload">
			<?php include('includes/uploader/browse_upload.php'); ?>
		</div>
		
		<div id="browse-queue"></div>
		<div id="browse-actions"></div>
	</div>
	<script type="text/javascript">
	Browser_Load();
	</script>
</body>