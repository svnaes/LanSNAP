<?php
$cookie = unserialize(decrypt(base64_decode($_COOKIE[$cookie_key])));
$group_payment_settings = DB_PREFIX . 'user_group_payment_settings';

if ( (!is_array($cookie)) or (!is_numeric($cookie['user_id'])) )
{
	echo 'Check that cookies are enabled in your browser before continuing';
	return;
}

$register_option = $cookie['register_option'];
$payment_details = $db->assoc('SELECT * FROM `'.$group_payment_settings.'` WHERE `entry_id`=?', $register_option);

//echo '<pre>'.print_r($payment_details, true).'</pre>';

if ($payment_details['cost'] == 0)
{
	// free!
	
	if ($settings['log_user_in'] == 1)
	{
		require_once('includes/content/user_login/functions.php');
		UL_LogUserIn($cookie['user_id']);
	}
	
	$output = str_replace('LINK', $settings['redirection_link'], $settings['step3']);
	echo '<p class="instructions">'.nl2br($output).'</p>';
	echo '<meta http-equiv="refresh" content="10;url='.$settings['redirection_link'].'" /> ';
	
	setcookie($cookie_key, '', time() - 3600, '/', CookieDomain());
	
	if ($payment_details['duration'] == 0)
	{
		// forever!
		$db->run('UPDATE `'.DB_USER_TABLE.'` SET `user_active`=? WHERE `id`=?', 1, $cookie['user_id']);
	}
	else
	{
		// limit
		$date = time() + (86400 * $payment_details['duration']);
		$db->run('UPDATE `'.DB_USER_TABLE.'` SET `registration_active`=? WHERE `id`=?', $date, $cookie['user_id']);
	}
}
else
{
	if (!isset($params[2]))
	{
		echo '<p class="instructions">'.nl2br($settings['step2']).'</p>';
	}
	
	$payment_settings = DB_PREFIX . 'pico_payment_settings';
	$payment_config   = $db->assoc('SELECT * FROM `'.$payment_settings.'`');
	
	//echo '<pre>'.print_r($payment_config, true).'</pre>';
	
	if ($payment_config['payment_method'] == 'paypal')
	{
		include('includes/content/user_signup/payments_paypal.php');
	}
}

?>