<?php
chdir('../../../');
require_once('core.php');
require_once('includes/captcha.class.php');

$action = $_REQUEST['page_action'];

if ($action == 'update_options')
{
	if (USER_ACCESS < 3) { exit(); }
	
	$component_id  = $_POST['component_id'];
	$new_options   = $_POST['options'];
	$remove_button = $_POST['remove_button'];
	
	foreach ($new_options as $key=>$val)
	{
		if (is_string($val))
		{
			$new_options[$key] = stripslashes($val);
		}
		elseif (is_array($val))
		{
			if ($key == 'lists')
			{
				$new_val = array();
				foreach ($val as $_val)
				{
					if (strlen($_val) > 0)
					{
						$new_val[] = $_val;
					}
				}
				$new_options[$key] = $new_val;
			}
			else
			{
				$new_options[$key] = $val;
			}
		}
	}
	
	if ($remove_button == 1)
	{
		$new_options['submit_button'] = '';
	}
	
	$db->run('UPDATE `'.DB_COMPONENT_TABLE.'` SET `additional_info`=? WHERE `component_id`=?', serialize($new_options), $component_id);
	exit();
}

if ($action == 'signup')
{
	foreach ($_POST as $key => $val)
	{
		if (is_string($val))
		{
			$_POST[$key] = strip_tags(stripslashes($val));
		}
	}
	
	$component_id = $_POST['component_id'];
	$data   = $db->result('SELECT `additional_info` FROM `'.DB_COMPONENT_TABLE.'` WHERE `component_id`=?', $component_id);
	$options = unserialize($data);
	if (!is_array($options)) { $options = array(); }
	
	$verify         = $_POST['verify'];
	$first_name     = $_POST['first_name'];
	$last_name      = $_POST['last_name'];
	$email          = $_POST['email'];
	$selected_lists = $_POST['selected_lists'];
	
	// verify
	if ($options['layout'] == 'full')
	{
		$captcha = new Captcha('component_' . $component_id, $db, getenv('REMOTE_ADDR'));
		
		if (strlen($first_name) == 0)
		{
			echo 'Invalid first name'; exit();
		}
		elseif (strlen($last_name) == 0)
		{
			echo 'Invalid last name'; exit();
		}
		//elseif (!preg_match('/^[^0-9][A-z0-9_]+([.][A-z0-9_]+)*[@][A-z0-9_]+([.][A-z0-9_]+)*[.][A-z]{2,4}$/', $email))
		elseif (!preg_match('/^[0-9a-zA-Z]([-.\w]*[0-9a-zA-Z])*@([0-9a-zA-Z][-\w]*[0-9a-zA-Z]\.)+[a-zA-Z]{2,9}$/', $email))
		{
			echo 'Invalid e-mail address!!'; exit();
		}
		elseif (!$captcha->Verify($verify))
		{
			echo 'You have typed the verification image improperly.'; exit();
		}
	}
	else
	{
		if (!preg_match('/^[0-9a-zA-Z]([-.\w]*[0-9a-zA-Z])*@([0-9a-zA-Z][-\w]*[0-9a-zA-Z]\.)+[a-zA-Z]{2,9}$/', $email))
		{
			echo 'Invalid e-mail address'; exit();
		}
	}
	
	$lists = $options['lists'];
	for ($x = 0; $x < sizeof($lists); $x++)
	{
		if (sizeof($lists) == 1)
		{
			$list_number = $lists[$x];
		}
		else
		{
			if ($selected_lists[$x] == 1)
			{
				$list_number = $lists[$x];
			}
			else
			{
				$list_number = '';
			}
		}
		
		
		if (strlen($list_number) > 0)
		{
			if ($options['newsletter_portal'] == 'mc')
			{
				require_once('includes/content/external_newsletter/MCAPI.class.php');
				$api = new MCAPI($options['api_key']);
				
				if (!isset($first_name)) { $first_name = ''; }
				if (!isset($last_name))  { $last_name = ''; }
				
				$api->listSubscribe($list_number, $email, array('FNAME'=> $first_name, 'LNAME'=> $last_name));
				
				if ($api->errorCode)
				{
					echo "Error adding your email:\n";
					echo strip_tags($api->errorMessage);
					exit();
				}
			}
			elseif ($options['newsletter_portal'] == 'cc')
			{
				require_once('includes/content/external_newsletter/constantcontact.php');
				CC_SubmitAddress($email, $first_name, $last_name, $list_number, $options['api_username'], $options['api_password']);
			}
			elseif ($options['newsletter_portal'] == 'ic')
			{
				require_once('includes/content/external_newsletter/icontact.php');
				$result_str = '';
				
				$account_id = IContactGetAccountID($options['api_key'], $options['api_username'], $options['api_password']);
				if ($account_id == 0)
				{
					echo 'Unable to locate account ID'; exit();
				}
				IContactAddContact($account_id, $options['api_key'], $options['api_username'], $options['api_password'], $email, $first_name, $last_name, &$result_str);
				IContactSubscribe($account_id, $options['api_key'], $options['api_username'], $options['api_password'], $email, $list_number, &$result_str);
			}
		}
	}
}