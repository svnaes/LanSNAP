<?php
chdir('../../../');
require_once('core.php');
if (USER_ACCESS < 3) { exit(); }

$action = $_REQUEST['page_action'];
$group_payment_settings = DB_PREFIX . 'user_group_payment_settings';

if ($action == 'update')
{
	$component_id = $_POST['component_id'];
	$settings     = $_POST['settings'];
	
	$settings['groups'] = serialize($settings['groups']);
	
	foreach ($settings as $key=>$val)
	{
		if (is_string($val))
		{
			$settings[$key] = stripslashes($val);
		}
	}
	
	$result = $db->result('UPDATE `'.DB_CONTENT.'` SET `additional_info`=? WHERE `component_id`=?', serialize($settings), $component_id);
	if ($result === FALSE) { echo $db->error; }
	exit();
}
elseif ( ($action == 'add_payment') or ($action == 'edit_payment') )
{
	foreach ($_POST as $key=>$val)
	{
		$$key = trim(stripslashes($val));
	}
	
	
	if ($action == 'add_payment')
	{
		$db->insert('INSERT INTO `'.$group_payment_settings.'` (`component_id`, `group_id`, `description`, `cost`, `duration`) VALUES (?,?,?,?,?)',
			$component_id, $group_id, $description, $cost, $duration
		);
	}
	else
	{
		$db->run('UPDATE `'.$group_payment_settings.'` SET `description`=?, `cost`=?, `duration`=? WHERE `entry_id`=?',
			$description, $cost, $duration, $edit_id
		);
	}
}
elseif ($action == 'delete_payment_entry')
{
	$entry_id = $_GET['entry_id'];
	$db->run('UPDATE `'.$group_payment_settings.'` SET `display`=? WHERE `entry_id`=?', 0, $entry_id);
}

?>