<?php
chdir('../../../');
require_once('core.php');
if (USER_ACCESS < 3) { exit(); }

$action = $_REQUEST['page_action'];

if ($action == 'update')
{
	$instance_id = $_POST['instance_id'];
	$result = $db->result('UPDATE `'.DB_CONTENT.'` SET `additional_info`=? WHERE `instance_id`=?', serialize($_POST['settings']), $instance_id);
	if ($result === FALSE) { echo $db->error; }
	exit();
}

?>