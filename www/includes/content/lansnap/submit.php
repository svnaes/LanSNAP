<?php
chdir('../../../');
require_once('core.php');

if ( (!defined('USER_ACCESS')) or (USER_ACCESS < 4) ) { exit(); }

require_once('includes/content/lansnap/functions.php');

$settings_table   = DB_PREFIX . 'lansnap_settings';
$ticket_table     = DB_PREFIX . 'lansnap_tickets';
$survey_questions = DB_PREFIX . 'lansnap_survey';
$survey_results   = DB_PREFIX . 'lansnap_survey_results';

$action = $_REQUEST['page_action'];

if ($action == 'update_settings')
{
	$settings = $_POST['settings'];
	foreach ($settings as $key=>$val)
	{
		//$settings[$key] = trim(stripslashes($val));
		$val = trim(stripslashes($val));
		
		// see if this key exists
		$check = $db->result('SELECT count(1) FROM `'.$settings_table.'` WHERE `setting_name`=?',
			$key
		);
		
		if ($check == 1)
		{
			// update
			$db->run('UPDATE `'.$settings_table.'` SET `setting_value`=? WHERE `setting_name`=?',
				$val, $key
			);
		}
		else
		{
			// insert
			$db->run('INSERT INTO `'.$settings_table.'` (`setting_name`, `setting_value`) VALUES (?,?)',
				$key, $val
			);
		}
		
	}
}
elseif ($action == 'update_tickets')
{
	$new_ticket = $_POST['new_ticket'];
	$name = trim(stripslashes($new_ticket['name']));
	$price = $new_ticket['price'];
	if (!is_numeric($price)) { $price = 0; }
	
	if (strlen($name) > 0)
	{
		$db->run('INSERT INTO `'.$ticket_table.'` (`name`, `price`) VALUES (?,?)',
			$name, $price
		);
	}
}
elseif ($action == 'delete_ticket')
{
	$ticket_id = $_GET['ticket_id'];
	$db->run('DELETE FROM `'.$ticket_table.'` WHERE `ticket_id`=?', $ticket_id);
}
elseif ( ($action == 'add_question') or ($action == 'edit_question') )
{
	foreach ($_POST as $key=>$val)
	{
		$$key = trim(stripslashes($val));
	}
	
	if ($action == 'add_question')
	{
		// add
		$db->run('INSERT INTO `'.$survey_questions.'` (`question`, `question_type`, `answers`, `component_id`) VALUES (?,?,?,?)',
			$question, $question_type, $answers, $component_id
		);
	}
	else
	{
		// update
		$db->run('UPDATE `'.$survey_questions.'` SET `question`=?, `question_type`=?, `answers`=? WHERE `question_id`=?',
			$question, $question_type, $answers, $edit_id
		);
	}
}
elseif ($action == 'delete_question')
{
	$question_id = $_GET['question_id'];
	$db->run('DELETE FROM `'.$survey_questions.'` WHERE `question_id`=?', $question_id);
}
elseif ($action == 'import_lanfest')
{
	$user   = LS_GetSetting('externaldb_user');
	$pass   = LS_GetSetting('externaldb_pass');
	$dbname = LS_GetSetting('externaldb_name');
	$table  = LS_GetSetting('externaldb_table');
	
	$external_db = new DataBase($host, $user, $pass, $dbname);
	$file = 'includes/content/lansnap/upload/'.urldecode($_GET['filename']);
	
	// see if file exists
	if (!is_file($file))
	{
		exit('File not found: ' . $file);
	}
	else
	{
		$lines = file($file);
		
		// clear out the table, if we have a new file then we have all new data, 
		// shouldn't update the old... just clear it and lets move on with our lives
		$external_db->run('DELETE FROM `'.$table.'`');
		
		foreach ($lines as $line)
		{
			$line = trim($line);
			$cells = explode(',', $line);
			if (is_numeric($cells[0]))
			{
				// process this line
				$barcode       = $cells[1];
				$seat          = $cells[7];
				$alias         = $cells[8];
				$first_name    = $cells[9];
				$last_name     = $cells[10];
				$email_address = $cells[11];
				$ticket_type   = $cells[2];
				
				// see if this user exists
				$check = $external_db->result('SELECT count(1) FROM `'.$table.'` WHERE `barcode`=?', $barcode);
				if ($check == 0)
				{
					$external_db->insert('INSERT INTO `'.$table.'` (`barcode`, `seat`, `alias`, `first_name`, `last_name`, `email_address`, `ticket_type`) VALUES (?,?,?,?,?,?,?)',
						$barcode, $seat, $alias, $first_name, $last_name, $email_address, $ticket_type
					);
				}
			}
		}
	}
}
elseif ($action == 'bind_all_ips')
{
	$component_id = $_GET['component_id']; // this isn't used at all yet
	$bind         = $_GET['bind']; // 0 = bind, 1 = unbind
	LS_BindAllUnusedAddresses($bind);
}
?>