<?php

function UL_GetPagesDropdown($setting, $page_id)
{
	global $db;
	$output = '<select name="settings['.$setting.']">';
	
	$pages = $db->force_multi_assoc('SELECT * FROM `'.DB_PAGES_TABLE.'` ORDER BY `name` ASC');
	if (is_array($pages))
	{
		foreach ($pages as $page)
		{
			if ((isset($page_id)) && ($page_id == $page['page_id']))
			{
				$output .= '<option selected="selected" value='.$page['page_id'].'>'.$page['name'].'</option>';
			}
			else
			{
				$output .= '<option value='.$page['page_id'].'>'.$page['name'].'</option>';
			}
		}
	}
	$output .= '</select>';
	return $output;
}

function UL_LogUserIn($user_id)
{
	global $db;
	
	$session_id = session_id();
	$ip         = getenv('REMOTE_ADDR');
	$db->run('UPDATE `'.DB_USER_TABLE.'` SET `last_login`=?, `last_ip`=?, `session_id`=? WHERE `id`=?', time(), $ip, $session_id, $user_id);
	
	// establish cookie
	
	$session_data = array(
		'ip_address' => $ip,
		'session_id' => $session_id,
	);
	
	$sd = base64_encode(serialize($session_data));
	setcookie('keep_session', $sd, time()+1209600, '/', CookieDomain());
}
?>