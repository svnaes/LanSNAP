<?php
chdir('../../../');
require_once('core.php');
if (USER_ACCESS < 3) { exit(); }

$quote_table = DB_PREFIX . 'quote_table';

$action = $_REQUEST['page_action'];

if ($action == 'add_quote')
{
	$quote = trim(stripslashes($_POST['quote']));
	$who   = trim(stripslashes($_POST['who']));
	$instance_id = trim(stripslashes($_POST['instance_id']));
	
	if (strlen($quote) > 0)
	{
		$db->run('INSERT INTO `'.$quote_table.'` (`quote`, `who`, `instance_id`) VALUES (?,?,?)',
			$quote, $who, $instance_id
		);
	}
}
elseif ($action == 'edit_quote')
{
	$id = $_POST['quote_id'];
	$quote = trim(stripslashes($_POST['quote']));
	$who   = trim(stripslashes($_POST['who']));
	
	if (strlen($quote) > 0)
	{
		$db->run('UPDATE `'.$quote_table.'` SET `quote`=?, `who`=? WHERE `id`=?',
			$quote, $who, $id
		);
	}
}
elseif ($action == 'delete')
{
	$id = $_GET['quote_id'];
	$db->run('DELETE FROM `'.$quote_table.'` WHERE `id`=?', $id);
}
?>