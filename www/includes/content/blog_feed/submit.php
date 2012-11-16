<?php
chdir('../../../');
require_once('core.php');
if (USER_ACCESS < 3) { exit(); }

$action = $_REQUEST['page_action'];

if ($action == 'update')
{
	$instance_id = $_POST['instance_id'];
	
	$settings = $_POST['settings'];
	$settings['layout'] = stripslashes($settings['layout']);
	$settings['title']  = stripslashes($settings['title']);
	
	$result = $db->result('UPDATE `'.DB_CONTENT.'` SET `additional_info`=? WHERE `instance_id`=?', serialize($settings), $instance_id);
	if ($result === FALSE) { echo $db->error; }
	exit();
}

?>