<?php
chdir('../../../');
require_once('core.php');
if (USER_ACCESS < 3) { exit(); }

$action = $_REQUEST['page_action'];
$faq_table = DB_PREFIX . 'faq_data';

if ($action == 'add_faq')
{
	$instance_id = $_POST['instance_id'];
	$question    = stripslashes($_POST['question']);
	$answer      = stripslashes($_POST['answer']);
	
	$position = $db->result('SELECT `position` FROM `'.$faq_table.'` ORDER BY `position` DESC LIMIT 1');
	$position = (is_numeric($position)) ? $position + 1 : 0;
	
	if  ( (strlen($question) > 0) and (strlen($answer) > 0) )
	{
		$db->run('INSERT INTO `'.$faq_table.'` (`instance_id`, `question`, `answer`, `position`) VALUES (?,?,?,?)', $instance_id, $question, $answer, $position);
	}
	exit();
}

if ($action == 'edit_faq')
{
	$question = stripslashes($_POST['question']);
	$answer   = stripslashes($_POST['answer']);
	$faq_id   = $_POST['faq_id'];
	
	if (!is_numeric($faq_id)) { exit(); }
	if  ( (strlen($question) > 0) and (strlen($answer) > 0) )
	{
		$db->run('UPDATE `'.$faq_table.'` SET `question`=?, `answer`=? WHERE `faq_id`=?', $question, $answer, $faq_id);
	}
	exit();
}

if ($action == 'delete')
{
	$faq_id = $_GET['faq_id'];
	if (!is_numeric($faq_id)) { exit(); }
	$db->run('DELETE FROM `'.$faq_table.'` WHERE `faq_id`=? LIMIT 1', $faq_id);
	exit();
}

if ($action == 'move')
{
	$faq_id    = $_GET['faq_id'];
	$direction = $_GET['direction'];
	if (!is_numeric($faq_id)) { exit(); }
	
	$current_pos = $db->result('SELECT `position` FROM `'.$faq_table.'` WHERE `faq_id`=?', $faq_id);
	$max_pos     = $db->result('SELECT `position` FROM `'.$faq_table.'` ORDER BY `position` DESC LIMIT 1');
	
	$new_pos = ($direction == 'up') ? $current_pos - 1 : $current_pos + 1;
	if ( ($new_pos >= 0) and ($new_pos <= $max_pos) )
	{
		$move_id = $db->result('SELECT `faq_id` FROM `'.$faq_table.'` WHERE `position`=?', $new_pos);
		$db->run('UPDATE `'.$faq_table.'` SET `position`=? WHERE `faq_id`=?', $new_pos, $faq_id);
		$db->run('UPDATE `'.$faq_table.'` SET `position`=? WHERE `faq_id`=?', $current_pos, $move_id);
	}
}
?>