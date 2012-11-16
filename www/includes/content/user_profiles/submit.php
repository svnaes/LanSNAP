<?php
chdir('../../../');
require_once('core.php');
if (USER_ACCESS < 3) { exit(); }

$action = $_REQUEST['page_action'];
$pu_table = DB_PREFIX . 'user_signups';

if ($action == 'update_details')
{
	$component_id = $_POST['component_id'];
	$source = $_POST['source'];
	
	$ai = serialize(array(
		'source'=>$source
	));
	
	$db->run('UPDATE `'.DB_CONTENT.'` SET `additional_info`=? WHERE `component_id`=? LIMIT 1', $ai, $component_id);
	exit();
}

?>