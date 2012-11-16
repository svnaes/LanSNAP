<?php

if ($_GET['reload'] == 1)
{
	$component_id = $_GET['component_id'];
	chdir('../../../');
	require_once('core.php');
}

if ((!defined('USER_ACCESS')) or (USER_ACCESS < 3)) { exit(); }

$survey_questions = DB_PREFIX . 'lansnap_survey';
$survey_results   = DB_PREFIX . 'lansnap_survey';

if ($_GET['edit_id'] != 0)
{
	$edit_id = $_GET['edit_id'];
	$extra   = '<input type="hidden" name="edit_id" value="'.$edit_id.'" />';
	$header  = 'Edit Question';
	$action  = 'edit_question';
	$values  = $db->assoc('SELECT * FROM `'.$survey_questions.'` WHERE `question_id`=?', $edit_id);
}
else
{
	$extra   = '';
	$header  = 'Add Question';
	$action  = 'add_question';
	$values  = array();
}

?>
<div class="ap_overflow">
	
	<h3>Question List</h3>
	
	<?php
	if ($action == 'add_question')
	{
		// show list
		echo '<table border="0" cellpadding="2" cellspacing="1" border="0" class="admin_list">';
		echo '<tr><th>Question</th><th>Type</th><th>Actions</th></tr>';
		
		$question_list = $db->force_multi_assoc('SELECT * FROM `'.$survey_questions.'` WHERE `component_id`=? ORDER BY `question_id` ASC',
			$component_id
		);
		
		if (is_array($question_list))
		{
			$counter = 0;
			foreach ($question_list as $question)
			{
				$class = ($counter % 2 == 0) ? 'a' : 'b';  $counter++;
				
				$type = ($question['question_type'] == 0) ? 'Choice' : 'Open Ended';
				$edit = '<img src="'.$body->url('includes/icons/edit.png').'" class="icon click" onclick="LS_LoadSurvey('.$component_id.', '.$question['question_id'].')" />';
				$delete = '<img src="'.$body->url('includes/icons/delete.png').'" class="icon click" onclick="LS_DeleteSurveyQuestion('.$component_id.', '.$question['question_id'].')" />';
				
				echo '<tr class="'.$class.'">';
				echo '<td>'.$question['question'].'</td>';
				echo '<td>'.$type.'</td>';
				echo '<td>'.$edit.$delete.'</td>';
				echo '<tr>';
			}
		}
		
		echo '</table>';
	}
	?>
	
	<h3><?=$header?></h3>
	
	<form method="post" action="<?=$body->url('includes/content/lansnap/submit.php')?>" onsubmit="LS_Survey(this); return false">
	<input type="hidden" name="component_id" value="<?=$component_id?>" />
	<input type="hidden" name="page_action" value="<?=$action?>" />
	<?=$extra?>
	<table border="0" cellpadding="2" cellspacing="1" border="0" class="admin_list">
	<tr class="a">
		<td>Question</td>
		<td><input type="text" name="question" value="<?=$values['question']?>" /></td>
	</tr>
	<tr class="b">
		<td>Type</td>
		<td>
			<input type="radio" name="question_type" value="0" <?=($values['question_type'] == 0) ? 'checked="checked"' : ''?> /> Choice<br />
			<input type="radio" name="question_type" value="1" <?=($values['question_type'] == 1) ? 'checked="checked"' : ''?> /> Open Ended<br />
		</td>
	</tr>
	<tr class="a">
		<td>Answers</td>
		<td>
			<textarea name="answers" class="ap_textarea"><?=$values['answers']?></textarea>
		</td>
	</tr>
	</table>
	<input type="submit" value="<?=$header?>" />
	</form>
	
</div>