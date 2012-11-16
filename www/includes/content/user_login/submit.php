<?php
chdir('../../../');
require_once('core.php');
if (USER_ACCESS < 3) { exit(); }

$action = $_REQUEST['page_action'];

if ($action == 'update')
{
	$component_id = $_POST['component_id'];
	$settings     = $_POST['settings'];
	
	$result = $db->result('UPDATE `'.DB_CONTENT.'` SET `additional_info`=? WHERE `component_id`=?', serialize($settings), $component_id);
	if ($result === FALSE) { echo $db->error; }
	exit();
}

?>