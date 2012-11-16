<?php

$group_payment_settings = DB_PREFIX . 'user_group_payment_settings';
$group_table            = DB_PREFIX . 'pico_groups';

require_once('includes/captcha.class.php');

$captcha         = new Captcha($instance_id, $db, getenv('REMOTE_ADDR'));
$cookie          = unserialize(decrypt(base64_decode($_COOKIE[$cookie_key])));

if (!is_array($cookie))
{
	echo 'Check that cookies are enabled in your browser before continuing';
	return;
}

$register_option = $cookie['register_option'];
$payment_details = $db->assoc('SELECT * FROM `'.$group_payment_settings.'` WHERE `entry_id`=?', $register_option);
$group_id        = $payment_details['group_id'];

// get profile id associated with this group
$profile_id = $db->result('SELECT `profile_id` FROM `'.$group_table.'` WHERE `group_id`=?', $group_id);
if (!is_numeric($profile_id))
{
	echo 'Unable to complete your registration';
	return;
}

// display the form to fill out

if (($_POST['page_action'] == 'register') and ($_POST['register_option'] == $register_option))
{
	// verify form
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
	
	if (!preg_match($patterns['email'], $email_address))
	{
		$errors[] = 'Invalid Email Address';
	}
	
	if (strlen($password) < 6)
	{
		$errors[] = 'Invalid Password';
	}
	
	if ($password != $confirm_password)
	{
		$errors[] = 'Passwords do not match';
	}
	
	// verify other fields
	
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
	
	
	if (sizeof($errors) == 0)
	{
		// make sure this e-mail address is not used
		$check = $db->result('SELECT count(1) FROM `'.DB_USER_TABLE.'` WHERE `email_address` LIKE ?', $email_address);
		if ($check != 0)
		{
			$errors[] = 'That e-mail address is already in use. Please try another';
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
		// next
		if (!$captcha->Verify($_POST['captcha_verify']))
		{
			echo '<p class="error">You did not type the image verification properly.</p>';
		}
		else
		{
			// make user
			$email_address = strtolower($email_address);
			
			$user_id = $db->insert('INSERT INTO `'.DB_USER_TABLE.'` (`username`, `password`, `access`, `email_address`, `first_name`, `last_name`, `user_active`) VALUES (?,?,?,?,?,?,?)',
				$email_address, md5($password), 1, $email_address, $first_name, $last_name, 0
			);
			
			$user_additional_table = DB_PREFIX . 'user_profile_values_' . $profile_id;
			$db->run('INSERT INTO `'.$user_additional_table .'` (`user_id`, `created`) VALUES (?,?)', $user_id, time());
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
				
				//if (is_array($value)) { $value = serialize($value); }
				if ($value == null) { $value = ''; }
				
				$db->run('UPDATE `'.$user_additional_table .'` SET `'.$field.'`=? WHERE `user_id`=? LIMIT 1', $value, $user_id);
			}
			
			// put in group
			$group_users = $db->result('SELECT `users` FROM `'.$group_table.'` WHERE `group_id`=?', $group_id);
			if (strlen($group_users) == 0)
			{
				$group_users = $user_id;
			}
			else
			{
				$gu = explode(',', $group_users);
				$gu[] = $user_id;
				$group_users = implode(',', $gu);
			}
			
			$db->run('UPDATE `'.$group_table.'` SET `users`=? WHERE `group_id`=?', $group_users, $group_id);
			
			// set cookie
			$cookie['user_id'] = $user_id;
			$cookie = base64_encode(encrypt(serialize($cookie)));
			setcookie($cookie_key, $cookie, time()+1209600, '/', CookieDomain());
			
			header('Location: ' . $body->url(CURRENT_ALIAS.'/finish'));
			exit();
			
			// reload
		}
	}
}

if (!isset($profile_data))
{
	$profile_data = Pico_GetProfileFieldData($profile_id);
}

echo '<p class="instructions">'.nl2br($settings['step1']).'</p>';


$output = '
<form method="post" action="'.$_SERVER['REQUEST_URI'].'">
<input type="hidden" name="page_action" value="register" />
<input type="hidden" name="register_option" value="'.$register_option.'" />

<table class="user_register" border="0" cellpadding="0" cellspacing="0">
<tr>
	<td class="left">First Name *</td>
	<td class="right"><input type="text" name="first_name" value="'.$first_name.'" /></td>
</tr>
<tr>
	<td class="left">Last Name *</td>
	<td class="right"><input type="text" name="last_name" value="'.$last_name.'" /></td>
</tr>
<tr>
	<td class="left">E-mail Address *</td>
	<td class="right"><input type="text" name="email_address" value="'.$email_address.'" /></td>
</tr>
<tr>
	<td class="left">Choose a password *</td>
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
</tr>

';

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

$captcha_img = '<img src="'.$body->url($captcha->Image()).'" />';

$output .= '
<tr>
	<td class="left">Image Verification *</td>
	<td class="right">
		'.$captcha_img.'
		<div class="caption">Please type the image above into the box below.</div>
		<input type="text" name="captcha_verify" maxlength="5" />
	</td>
</tr>
</table>
<input type="submit" value="Continue" />
</form>';

echo $output;

?>