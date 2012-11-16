<?php
if (!defined('USER_ACCESS'))
{
	chdir('../../');
	require_once('core.php');
	$mode = $_GET['mode'];
}
if (USER_ACCESS < 3) { exit(); }

$upload_path = $body->url('includes/uploader/upload.php');
if ($mode == 'image')
{
	$uploader = new Uploader($upload_path, 'Browse_FileUploaded', 'Browse_FilesUploaded', '.jpg, .png, .gif', 'Image Files (jpg/png/gif)', '000000', 'cccccc');
}
else
{
	$uploader = new Uploader($upload_path, 'Browse_FileUploaded', 'Browse_FilesUploaded', '.*', 'All Files', '000000', 'cccccc');
}
echo $uploader->Output();

?>