<?php
chdir('../../../');
require_once('core.php');
if (USER_ACCESS < 3) { exit(); }

$action = $_REQUEST['page_action'];

if ($action == 'update_links')
{
	$content = implode(',', $_POST['current_links']);
	$instance_id  = $_POST['instance_id'];
	$component_id = $_POST['component_id'];
	$result = $db->run('UPDATE `'.DB_CONTENT.'` SET `content`=? WHERE `instance_id`=?', $content, $instance_id);
	exit();
}

if ($action == 'add_link')
{
	foreach ($_POST as $key=>$val)
	{
		$_POST[$key] = stripslashes($val);
	}
	if ( (strlen($_POST['url']) > 0) and (strlen($_POST['name']) > 0) )
	{
		$insert = $db->insert('INSERT INTO `'.DB_LINKS.'` (`url`, `target`, `caption`, `name`) VALUES (?,?,?,?)', 
			$_POST['url'], $_POST['target'], $_POST['caption'], $_POST['name']
		);
	}
	exit();
}

if ($action == 'edit_link')
{
	foreach ($_POST as $key=>$val)
	{
		$_POST[$key] = stripslashes($val);
	}
	
	if ( (strlen($_POST['url']) > 0) and (strlen($_POST['name']) > 0) and (is_numeric($_POST['link_id'])) )
	{
		$update = $db->run('UPDATE `'.DB_LINKS.'` SET `url`=?, `target`=?, `caption`=?, `name`=? WHERE `link_id`=?',
			$_POST['url'], $_POST['target'], $_POST['caption'], $_POST['name'], $_POST['link_id']
		);
	}
	exit();
}

if ($action == 'delete_link')
{
	if (is_numeric($_GET['link_id']))
	{
		$delete = $db->run('DELETE FROM `'.DB_LINKS.'` WHERE `link_id`=? LIMIT 1', $_GET['link_id']);
	}
	include('includes/content/linkmenu/external_links.php');
	exit();
}

if ($action == 'get_links')
{
	include('includes/content/linkmenu/external_links.php');
	exit();
}

if ($action == 'get_edit')
{
	$component_id = $_GET['component_id'];
	$instance_id  = $_GET['instance_id'];
	include('includes/content/linkmenu/edit.php');
	exit();
}

if ($action == 'font_options')
{
	$settings = serialize($_POST['settings']);
	$component_id = $_POST['component_id'];
	$options = $db->run('UPDATE `'.DB_CONTENT.'` SET `additional_info`=? WHERE `component_id`=?', $settings, $component_id);
}
?>