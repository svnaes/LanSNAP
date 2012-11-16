<?php
// get amount
//echo '<pre>'.print_r($_POST, true).'</pre>';
//echo '<pre>'.print_r($payment_details, true).'</pre>';

$pp_user = $payment_config['pp_api_user'];
$pp_pass = $payment_config['pp_api_pass'];
$pp_sig  = $payment_config['pp_api_signature'];

if ($params[2] == 'paypal')
{
	list ($check, $variables) = explode('?', $params[3]);
	
	if ($check == 'continue')
	{
		$vars = explode('&', $variables);
		$pp_return = array();
		foreach ($vars as $var)
		{
			list($key, $val) = explode('=', $var);
			$val = urldecode($val);
			$pp_return[$key] = $val;
		}
		
		// submit payment immediately
		$curl_post = array();
		$curl_post['METHOD'] = 'DoExpressCheckoutPayment';
		$curl_post['VERSION'] = '52.0';
		$curl_post['USER'] = $pp_user;
		$curl_post['PWD'] = $pp_pass;
		$curl_post['SIGNATURE'] = $pp_sig;
		$curl_post['TOKEN'] = $pp_return['token'];
		$curl_post['PAYERID'] = $pp_return['PayerID'];
		
		$curl_post['PAYMENTACTION'] = 'Sale';
		$curl_post['AMT'] = $payment_details['cost'];
		
		$pp_response = Pico_SubmitPaypalRequest($payment_config['test_mode'], $curl_post);
		//echo '<pre>'.print_r($pp_response, true).'</pre>';
		
		if ($pp_response['ACK'] == 'Success')
		{
			// make user active
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
			
			// log the transaction
			$db->run('INSERT INTO `'.DB_TRANSACTION_LOG.'` (`user_id`, `component_id`, `timestamp`, `transaction_id`, `test_mode`, `amount_gross`, `amount_net`,
			`fee`, `note`, `custom_status`, `payment_type`, `payment_method`) VALUES (?,?,?,?,?,?,?,?,?,?,?,?)',
				$cookie['user_id'], $component_id, time(), $pp_response['TRANSACTIONID'], $payment_config['test_mode'], $pp_response['AMT'], $pp_response['AMT'],
				0, '', 0, $pp_response['PAYMENTTYPE'], 'paypal'
			);
			
			if ($settings['log_user_in'] == 1)
			{
				require_once('includes/content/user_login/functions.php');
				UL_LogUserIn($cookie['user_id']);
			}
			
			// display redirect
			$output = str_replace('LINK', $settings['redirection_link'], $settings['step3']);
			echo '<p class="instructions">'.nl2br($output).'</p>';
			echo '<meta http-equiv="refresh" content="10;url='.$settings['redirection_link'].'" /> ';
			setcookie($cookie_key, '', time() - 3600, '/', CookieDomain());
			
			// eat bacon
		}
	}
	return;
}

$curl_post = array();
$curl_post['USER'] = $pp_user;
$curl_post['PWD'] = $pp_pass;
$curl_post['SIGNATURE'] = $pp_sig;
$curl_post['VERSION'] = '52.0';
$curl_post['PAYMENTACTION'] = 'Sale';
$curl_post['AMT'] = $payment_details['cost'];
$curl_post['RETURNURL'] = 'http://' . $_SERVER['SERVER_NAME'] . $body->url(CURRENT_ALIAS . '/finish/paypal/continue');
$curl_post['CANCELURL'] = 'http://' . $_SERVER['SERVER_NAME'] . $body->url(CURRENT_ALIAS . '/finish/paypal/cancel');
$curl_post['METHOD'] = 'SetExpressCheckout';
$curl_post['DESC'] = $settings['invoice_description'];

$pp_response = Pico_SubmitPaypalRequest($payment_config['test_mode'], $curl_post);

if ($pp_response['ACK'] == 'Success')
{
	$redirect_url = ($payment_config['test_mode'] == 1) ? 'https://www.sandbox.paypal.com/cgi-bin/webscr?cmd=_express-checkout&token=' . $pp_response['TOKEN'] : 'https://www.paypal.com/cgi-bin/webscr?cmd=_express-checkout&token=' . $pp_response['TOKEN'];
	echo '<a href="'.$redirect_url.'"><img src="https://www.paypal.com/en_US/i/btn/btn_xpressCheckout.gif" align="left" style="margin-right:7px;" border="0"></a>';
}
else
{
	echo 'There was a problem contacting PayPal. Please try later';
	return;
}


?>


