<?php
chdir('../../../');
require_once('core.php');

$action = $_REQUEST['page_action'];

$user_image_table = DB_PREFIX . 'user_images';

if ($action == 'upload_file')
{
	$filename     = strip_tags(urldecode($_GET['filename']));
	$component_id = $_GET['component_id'];
	
	$source = 'includes/tmp/'.$filename;
	if (!is_file($source)) { exit('Unable to find uploaded file'); }
	if (!is_numeric($component_id)) { exit('Invalid component id'); }
	$name = basename($source);
	
	// see if this file exists...
	
	$file_md5 = md5(file_get_contents($source));
	$check = $db->result('SELECT count(1) FROM `'.$user_image_table.'` WHERE `md5`=? AND `component_id`=?', $file_md5, $component_id);
	if ($check != 0) { exit('This file already exists ('.$name.')'); }
	
	// insert file 
	
	$image_id = $db->insert('INSERT INTO `'.$user_image_table.'` (`filename`, `component_id`, `timestamp`, `ip_address`, `md5`) VALUES (?,?,?,?,?)',
		$filename, $component_id, time(), $_SERVER['HTTP_X_FORWARDED_FOR'], $file_md5
	);
	
	if (!is_numeric($image_id)) { exit('Error uploading file'); }
	
	$file_dir = 'includes/content/user_gallery/storage/'.$image_id.'/';
	@mkdir($file_dir);
	
	if (!is_dir($file_dir)) { exit('Unable to create file directory'); }
	chmod($file_dir, 0777);
	
	$new_file = $file_dir . $name;
	rename($source, $new_file);
	chmod($new_file, 0666);
}
?>
