<?php

$answers  = array(); // make sure its set 

if ($_POST['page_action'] == 'complete_survey')
{
	$question_list = $db->force_multi_assoc('SELECT * FROM `'.$survey_questions.'` WHERE `component_id`=? ORDER BY `question_id` ASC',
		$component_id
	);
	
	$continue = true;
	// make sure they have an answer for each question
	
	foreach ($question_list as $question)
	{
		$question_id = $question['question_id'];
		$user_answer = trim(stripslashes($_POST['answers'][$question_id]));
		
		if (strlen($user_answer) == 0)
		{
			$continue = false;
		}
		else
		{
			$answers[$question_id] = $user_answer;
		}
	}
	
	if ($continue)
	{
		$_SESSION['survey_complete'] = true;
		$_SESSION['survey_answers'] = $answers;
		// redirect
		header('Location: ' . $_SERVER['REQUEST_URI']);
		exit();
	}
	else
	{
		echo '<p class="error">Please enter/choose an answer for all questions</p>';
	}
}
elseif ($_POST['page_action'] == 'register')
{
	$email    = trim(stripslashes($_POST['email']));
	$password = trim(stripslashes($_POST['password']));
	
	$user_id = $db->result('SELECT `user_id` FROM `'.$user_table.'` WHERE `email_address` LIKE ? AND `password`=?',
		$email, md5($password)
	);
	
	if (!is_numeric($user_id))
	{
		echo '<p class="error">Invalid E-mail or Password</p>';
	}
	else
	{
		$command = '/usr/sbin/arp -a '.$ip .' | cut -d\' \' -f4';
		$mac = exec($command, $array);
		$mac = trim(str_replace(':', '', $mac));
		
		$user_info = $db->assoc('SELECT * FROM `'.$user_table.'` WHERE `user_id`=?', $user_id);
		// see if this user has an entry in the system, if it does, update it
		
		$entry_id = $db->result('SELECT `entry_id` FROM `'.$ip_table.'` WHERE `user_id`=? AND `source`=?', $user_id, 0);
		if (is_numeric($entry_id))
		{
			$db->run('UPDATE `'.$ip_table.'` SET `mac_address`=?, `status`=? WHERE `entry_id`=?', $mac, 1, $entry_id);
		}
		else
		{
			// put ip and mac into database
			$user_ip = LS_GetIPAddress($user_info['ip_group']);
			LS_UnbindAddress($user_ip);
			
			$entry_id = $db->insert('INSERT INTO `'.$ip_table.'` (`user_id`, `mac_address`, `ip_address`, `status`, `source`) VALUES (?,?,?,?,?)',
				$user_id, $mac, $user_ip, 1, 0
			);
			
			$db->run('UPDATE `'.$user_table.'` SET `date_registered`=? WHERE `user_id`=?',
				time(), $user_id
			);
			
			if (!is_numeric($entry_id))
			{
				echo 'Error: ' . $db->error . ' ' . $db->query;
				return;
			}
		}
		
		// see if they completed the survey
		if ($_SESSION['survey_complete'] == true)
		{
			// save results
			$check = $db->insert('INSERT INTO `'.$survey_results.'` (`user_id`, `answers`, `component_id`) VALUES (?,?,?)',
				$user_id, serialize($_SESSION['survey_answers']), $component_id
			);
			
			unset($_SESSION['survey_complete']);
			
			if (!is_numeric($check))
			{
				echo $db->error . '<br /> ' .$db->query;
				return;
			}
		}
		
		// redirect to thank you
		header('Location: ' . $body->url(CURRENT_ALIAS . '/thank-you'));
	}
}

// see if there are any survey questions to ask

$question_list = $db->force_multi_assoc('SELECT * FROM `'.$survey_questions.'` WHERE `component_id`=? ORDER BY `question_id` ASC',
	$component_id
);

if (is_array($question_list))
{
	// see if this person has completed the pre-survey
	if (!isset($_SESSION['survey_complete'])) { $_SESSION['survey_complete'] = false; }
	
	if ($_SESSION['survey_complete'] == false)
	{
		// show the survey
		
		$output  = '<form method="post" action="'.$_SERVER['REQUEST_URI'].'">';
		$output .= '<input type="hidden" name="page_action" value="complete_survey" />';
		
		foreach ($question_list as $question)
		{
			$question_id = $question['question_id'];
			if ($question['question_type'] == 0)
			{
				// choice
				$choices = explode("\n", $question['answers']);
				$answer = '<select name="answers['.$question_id.']">';
				foreach ($choices as $choice)
				{
					$selected = ($answers[$question_id] == $choice) ? 'selected="selected"' : '';
					$answer .= '<option value="'.$choice.'" '.$selected.'>'.$choice.'</option>';
				}
				$answer .= '</select>';
			}
			else
			{
				// open
				$answer = '<input type="text" name="answers['.$question_id.']" value="'.$answers[$question_id].'" />';
			}
		
			$output .= '<div class="survey_question">
			<div class="question">'.$question['question'].'</div>
			<div class="answer">'.$answer.'</div>
			</div>';
		}
		
		$output .= '<input type="submit" value="Complete Survey" /></form>';
		
		echo $output;
		return;
	}
}

?>
<h1>Register</h1>
<p><?=LS_GetSetting('user_register_message')?></p>
<form method="post" action="<?=$_SERVER['REQUEST_URI']?>">
<input type="hidden" name="page_action" value="register" />
<table class="register" cellpadding="0" cellspacing="0" border="0">
<tr>
	<td class="left">E-mail Address</td>
	<td class="right"><input type="text" name="email" value="" /></td>
</tr>
<tr>
	<td class="left">Password</td>
	<td class="right"><input type="password" name="password" value="" /></td>
</tr>
</table>
<input type="submit" value="Continue" />
</form>