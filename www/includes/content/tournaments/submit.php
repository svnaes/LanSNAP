<?php
chdir('../../../');
require_once('core.php');

if ((!defined('USER_ACCESS')) or (USER_ACCESS < 3)) { exit(); }

$tourny_table = DB_PREFIX . 'lantourny_list';

$action = $_REQUEST['page_action'];

if ( ($action == 'add_tourny') or ($action == 'edit_tourny') )
{
	// insert
	foreach ($_POST as $key=>$val)
	{
		$$key = trim(stripslashes($val));
	}
	
	if ($action == 'add_tourny')
	{
		$db->run('INSERT INTO `'.$tourny_table.'` (`name`, `team_or_single`, `elimination`, `max_teams`, `team_size`, `description`, `rules`, `time`, `component_id`) VALUES (?,?,?,?,?,?,?,?,?)',
			$name, $team_or_single, $elimination, $max_teams, $team_size, $description, $rules, $time, $component_id
		);
	}
	else
	{
		$db->run('UPDATE `'.$tourny_table.'` SET `name`=?, `team_or_single`=?, `elimination`=?, `max_teams`=?, `team_size`=?, `description`=?, `rules`=?, `time`=? WHERE `id`=?',
			$name, $team_or_single, $elimination, $max_teams, $team_size, $description, $rules, $time, $id
		);
	}
}
elseif ($action == 'delete_tourny')
{
	$tourny_id = $_GET['tourny_id'];
	$db->run('DELETE FROM `'.$tourny_table.'` WHERE `id`=?', $tourny_id);
}

?>