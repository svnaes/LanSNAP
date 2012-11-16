<?php

if (USER_ACCESS != 0)
{
	echo '<p class="warn">You are logged in. If you wish to continue you will have to <a href="'.$body->url('logout').'">log out</a> first.</p>';
	return;
}

require_once('includes/content/user_signup/functions.php');
$additional_info  = $db->result('SELECT `additional_info` FROM `'.DB_CONTENT.'` WHERE `component_id`=?', $component_id);
$settings         = unserialize($additional_info);
if (!is_array($settings)) { $settings = array(); }

global $params;
$cookie_key = 'user_signup_' . $component_id;

if ($params[1] == 'register')
{
	if ($_POST['page_action'] == 'user_register')
	{
		if ($_POST['component_id'] != $component_id)
		{
			return;
		}
		
		if (!is_numeric($_POST['register_option']))
		{
			echo '<p class="error">Please choose a membership type before continuing</p>';
		}
		else
		{
			// establish signup cookie
			
			$cookie_data = array(
				'register_option' => $_POST['register_option'],
			);
			
			$cookie = base64_encode(encrypt(serialize($cookie_data)));
			setcookie($cookie_key, $cookie, time()+1209600, '/', CookieDomain());
			
			header('Location: ' . $_SERVER['REQUEST_URI']);
			exit();
		}
	}
	
	if (isset($_COOKIE[$cookie_key]))
	{
		include('includes/content/user_signup/register.php');
		return;
	}
}
elseif ($params[1] == 'finish')
{
	if (isset($_COOKIE[$cookie_key]))
	{
		include('includes/content/user_signup/finish.php');
	}
	return;
}
else
{
	if (isset($_COOKIE[$cookie_key]))
	{
		$cookie = unserialize(decrypt(base64_decode($_COOKIE[$cookie_key])));
		if (is_numeric($cookie['user_id']))
		{
			header('Location: '. $body->url(CURRENT_ALIAS . '/finish'));
		}
	}
}

$group_payment_settings = DB_PREFIX . 'user_group_payment_settings';

// get all the groups
$group_table  = DB_PREFIX . 'pico_groups';
$groups = $db->force_multi_assoc('SELECT * FROM `'.$group_table.'` ORDER BY `name` ASC');

$output = '';

$show_groups = array();

if (is_array($groups))
{
	$enabled_groups = unserialize($settings['groups']);
	if (!is_array($enabled_groups)) { $enabled_groups = array(); }
	
	foreach ($groups as $group)
	{
		if (in_array($group['group_id'], $enabled_groups))
		{
			$show_groups[] = array(
				'name' => $group['name'],
				'id'=> $group['group_id']
			);
		}
	}
}

if (sizeof($show_groups) > 0)
{
	echo '<form method="post" action="'.$body->url(CURRENT_ALIAS . '/register').'">';
	echo '<input type="hidden" name="component_id" value="'.$component_id.'" />';
	echo '<input type="hidden" name="page_action" value="user_register" />';

	foreach($show_groups as $group)
	{
		// see if this group has any settings
		$payment_configs = $db->force_multi_assoc('SELECT * FROM `'.$group_payment_settings.'` WHERE `component_id`=? AND `group_id`=? AND `display`=? ORDER BY `duration` ASC',
			$component_id, $group['id'], 1
		);
		
		if (sizeof($payment_configs) > 0)
		{
			echo '<div class="group_title">'.$group['name'].'</div>';
			
			foreach ($payment_configs as $config)
			{
				if ($config['cost'] == 0)
				{
					$cost = 'Free for ';
				}
				else
				{
					$cost = '$' . number_format($config['cost']) . ' for ';
				}
				
				$duration = $config['duration'];
				
				if ($duration == 0)
				{
					$duration_text = 'Lifetime';
				}
				else
				{
					$num_years = floor($duration / 365);
					$num_days = $duration - ($num_years * 365);
					
					$duration_text = '';
					if ($num_years != 0)
					{
						$duration_text .= $num_years . ' years';
					}
					
					if ($num_days > 0)
					{
						if (strlen($duration_text) > 0)
						{
							$duration_text .= ', ';
						}
						
						$duration_text .= $num_years . ' days';
					}
				}
				
				//$link = $body->url(CURRENT_ALIAS . '/register/'.$config['entry_id']);
				
				echo '<div class="user_signup_option">
					<div class="uso_left">
					<input type="radio" name="register_option" value="'.$config['entry_id'].'" />
					</div>
					<div class="uso_right">
						<div class="description">'.$config['description'].'</div>
						<div class="cost">'.$cost.$duration_text.'</div>
					</div>
					<div class="clear"></div>
				</div>';
			}
		}
	}
	
	echo '<input type="submit" value="Continue" />';
	echo '</form>';
}
?>