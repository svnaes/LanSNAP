<?php
if (!defined('USER_ID')) { exit(); }

$profile_id = Pico_GetUserProfileId(USER_ID);

if (!is_numeric($profile_id))
{
	echo '<p>This user account is not configured to use this area</p>';
	return;
}

$user_profile_table = DB_PREFIX . 'user_profile_values_' . $profile_id;
$user_details = $db->assoc('SELECT * FROM `'.$user_profile_table.'` WHERE `user_id`=?', USER_ID);

if ($_POST['page_action'] == 'update_account')
{
	$profile_data = Pico_GetProfileFieldData($profile_id, $_POST);
	$patterns = array(
		'email'    => '/^[0-9a-zA-Z]([-.\w]*[0-9a-zA-Z])*@([0-9a-zA-Z][-\w]*[0-9a-zA-Z]\.)+[a-zA-Z]{2,9}$/',
		'phone'    => '/[0-9\-\(\)\s]/',
		'alpha'    => '/[A-z\s]/',
		'numeric'  => '/[0-9]/',
		'alphanum' => '/[A-z0-9\s]/',
	);
	
	foreach ($_POST as $key=>$val)
	{
		if (is_string($val))
		{
			$$key = trim(stripslashes(strip_tags($val)));
		}
		else
		{
			$$key = $val;
		}
	}
	
	$errors = array();
	
	if (strlen($first_name) < 1)
	{
		$errors[] = 'Invalid First Name';
	}
	
	if (strlen($last_name) < 2)
	{
		$errors[] = 'Invalid Last Name';
	}
	
	
	if ( (strlen($password) > 0) and (strlen($password) < 6) )
	{
		$errors[] = 'Invalid Password';
	}
	elseif ( (strlen($password) > 0) and ($password != $confirm_password) )
	{
		$errors[] = 'Passwords do not match';
	}

	$required_types = array('text', 'radio', 'checkbox', 'terms', 'dropdown', 'lg_text', 'date');
	
	foreach ($profile_data as $item)
	{
		$post = 'field_' . $item['id'];
		
		if ($item['required'] == 1)
		{
			if (in_array($item['type'], $required_types))
			{
				$value = $$post;
				
				if ($item['type'] == 'date')
				{
					foreach ($value as $key=>$val)
					{
						if (strlen($val) == 0)
						{
							$errors[] = 'Missing '.$item['name'];
							break;
						}
					}
				}
				else
				{
					if ($value == null)
					{
						$errors[] = 'Missing '.$item['name'];
					}
				}
			}
		}
		
		if ( ($item['type'] == 'text') and ($item['pattern'] != 'none') )
		{
			if (strlen($$post) > 0)
			{
				// verify patterns
				$pattern = $patterns[$item['pattern']];
				if (!preg_match($pattern, $$post))
				{
					$errors[] = 'Invalid ' .$item['name'];
				}
			}
		}
	}
	
	if (sizeof($errors) > 0)
	{
		foreach ($errors as $error)
		{
			echo '<p class="error">'.$error.'</p>';
		}
	}
	else
	{
		// update user
		$db->run('UPDATE `'.DB_USER_TABLE.'` SET `first_name`=?, `last_name`=? WHERE `id`=?', $first_name, $last_name, USER_ID);
		if (strlen($password) > 0)
		{
			$db->run('UPDATE `'.DB_USER_TABLE.'` SET `password`=? WHERE `id`=?', md5($password), USER_ID);
		}
		
		foreach ($profile_data as $item)
		{
			$field = 'field_' . $item['id'];
			$value = $$field;
			
			if ($item['type'] == 'date')
			{
				$value = mktime(0, 0, 0, $value['month'], $value['day'], $value['year']);
			}
			elseif ($item['type'] == 'check_list')
			{
				$value = serialize($value);
			}
			
			if ($value == null) { $value = ''; }
			
			$db->run('UPDATE `'.$user_profile_table .'` SET `'.$field.'`=? WHERE `user_id`=? LIMIT 1', $value, USER_ID);
		}
		
		echo '<p>Your settings have been updated successfully.</p>';
		return;
	}
}
else
{
	$profile_data = Pico_GetProfileFieldData($profile_id, $user_details);
}

$output = '
<form method="post" action="'.$_SERVER['REQUEST_URI'].'">
<input type="hidden" name="page_action" value="update_account" />
<input type="hidden" name="register_option" value="'.$register_option.'" />

<table class="user_account" border="0" cellpadding="0" cellspacing="0">
<tr>
	<td class="left">First Name *</td>
	<td class="right"><input type="text" name="first_name" value="'.$user_info['first_name'].'" /></td>
</tr>
<tr>
	<td class="left">Last Name *</td>
	<td class="right"><input type="text" name="last_name" value="'.$user_info['last_name'].'" /></td>
</tr>
<tr>
	<td class="left">New password *<br />(Leave blank for no change)</td>
	<td class="right">
		<input type="password" name="password" class="text" />
		<div class="caption">Passwords must be 6 or more characters in length</div>
	</td>
</tr>
<tr>
	<td class="left">Re-type Password *</td>
	<td class="right">
		<input type="password" name="confirm_password" class="text" />
		<div class="caption">For verification purposes</div>
	</td>
</tr>';

// required: users must include first name, last name, email, and password.

foreach ($profile_data as $item)
{
	if ($item['type'] != 'info')
	{
		$required = ($item['required'] == 1) ? ' *' : '';
		$caption  = ( (strlen($item['caption']) > 0) and ($item['type'] != 'terms') ) ? '<div class="caption">'.$item['caption'].'</div>' : '';
		$output .= '<tr>
			<td class="left">'.$item['name'].$required.'</td>
			<td class="right">'.$item['html'].$caption.'</td>
		</tr>';
	}
	else
	{
		$output .= '<tr><td colspan="2" class="caption">'.$item['options'].'</td></tr>';
	}
}

// captcha



$output .= '
</table>
<input type="submit" value="Continue" />
</form>';

echo $output;
?>