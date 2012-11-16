<?php

$payment_settings = DB_PREFIX . 'pico_payment_settings';
$payment_config   = $db->assoc('SELECT * FROM `'.$payment_settings.'`');

$pp_user = $payment_config['pp_api_user'];
$pp_pass = $payment_config['pp_api_pass'];
$pp_sig  = $payment_config['pp_api_signature'];

global $params;
if ($params[1] == 'paypal')
{
	list ($check, $variables) = explode('?', $params[2]);
	if ($check == 'finish')
	{
		$vars = explode('&', $variables);
		$pp_return = array();
		foreach ($vars as $var)
		{
			list($key, $val) = explode('=', $var);
			$val = urldecode($val);
			$pp_return[$key] = $val;
		}
		
		$total = $order_info['total'] + 1;
		
		$curl_post = array();
		$curl_post['METHOD'] = 'DoExpressCheckoutPayment';
		$curl_post['VERSION'] = '52.0';
		$curl_post['USER'] = $pp_user;
		$curl_post['PWD'] = $pp_pass;
		$curl_post['SIGNATURE'] = $pp_sig;
		$curl_post['TOKEN'] = $pp_return['token'];
		$curl_post['PAYERID'] = $pp_return['PayerID'];
		
		$curl_post['PAYMENTACTION'] = 'Sale';
		$curl_post['AMT'] = $total;
		
		$pp_response = Pico_SubmitPaypalRequest($payment_config['test_mode'], $curl_post);
		
		if ($pp_response['ACK'] == 'Success')
		{
			// update payment status
			
			// log the transaction - pico transaction table
			$pico_trans_id = $db->insert('INSERT INTO `'.DB_TRANSACTION_LOG.'` (`user_id`, `component_id`, `timestamp`, `transaction_id`, `test_mode`, `amount_gross`, `amount_net`,
			`fee`, `note`, `custom_status`, `payment_type`, `payment_method`) VALUES (?,?,?,?,?,?,?,?,?,?,?,?)',
				$user_id, $component_id, time(), $pp_response['TRANSACTIONID'], $payment_config['test_mode'], $pp_response['AMT'], $pp_response['AMT'],
				0, '', 0, $pp_response['PAYMENTTYPE'], 'paypal'
			);
			
			// log the transaction - food table
			$db->run('UPDATE `'.$menu_orders.'` SET `status`=?, `pay_method`=?, `transaction_id`=? WHERE `order_id`=?', 1, 'paypal', $pico_trans_id, $order_id);

			// display thank you.
			
			echo $settings['order_placed'];
			echo '<meta http-equiv="refresh" content="5;URL=\''.$body->url(CURRENT_ALIAS).'\'">';
			
			return;
		}
		else
		{
			echo '<p>There was an error processing your payment and you have not been charged. <a href="'.$body->url(CURRENT_ALIAS).'">Click here to try again</a></p>';
		}
	}
}

if ($_POST['page_action'] == 'payment_option')
{
	$ok_methods = array('cash', 'paypal');
	$method = $_POST['payment_option'];
	
	if (!in_array($method, $ok_methods))
	{
		echo '<p class="error">Invalid payment method</p>';
	}
	else
	{
		if ($method == 'cash')
		{
			// update order status
			$db->run('UPDATE `'.$menu_orders.'` SET `status`=?, `pay_method`=? WHERE `order_id`=?', 1, 'cash', $order_id);
			
			echo $settings['order_placed'];
			echo '<meta http-equiv="refresh" content="5;URL=\''.$body->url(CURRENT_ALIAS).'\'">';
			return;
			
			// display thank you
		}
		else
		{
			$total = $order_info['total'] + 1;
			// paypal it up!
			$curl_post = array();
			$curl_post['USER'] = $pp_user;
			$curl_post['PWD'] = $pp_pass;
			$curl_post['SIGNATURE'] = $pp_sig;
			$curl_post['VERSION'] = '52.0';
			$curl_post['PAYMENTACTION'] = 'Sale';
			$curl_post['AMT'] = $total;
			$curl_post['RETURNURL'] = 'http://' . $_SERVER['SERVER_NAME'] . $body->url(CURRENT_ALIAS . '/paypal/finish');
			$curl_post['CANCELURL'] = 'http://' . $_SERVER['SERVER_NAME'] . $body->url(CURRENT_ALIAS . '/paypal/cancel');
			$curl_post['METHOD'] = 'SetExpressCheckout';
			$curl_post['DESC'] = 'MLP Food Order';
			
			$pp_response = Pico_SubmitPaypalRequest($payment_config['test_mode'], $curl_post);

			if ($pp_response['ACK'] == 'Success')
			{
				$redirect_url = ($payment_config['test_mode'] == 1) ? 'https://www.sandbox.paypal.com/cgi-bin/webscr?cmd=_express-checkout&token=' . $pp_response['TOKEN'] : 'https://www.paypal.com/cgi-bin/webscr?cmd=_express-checkout&token=' . $pp_response['TOKEN'];
				header('Location: '. $redirect_url);
			}
			else
			{
				if (USER_ACCESS > 3)
				{
					//echo '<pre>'.print_r($payment_config, true).'</pre>';
					//echo '<pre>'.print_r($curl_post, true).'</pre>';
					//echo '<pre>'.print_r($pp_response, true).'</pre>';
				}
				//
				echo '<p>There was a problem contacting PayPal. Please try later</p>';
				return;
			}
		}
	}
}

?>
<h3>Payment Options</h3>
<p class="bold">Total: $<?=number_format($order_info['total'], 2)?></p>
<p>Please choose a payment method</p>
<form method="post" action="<?=$_SERVER['REQUEST_URI']?>">
<input type="hidden" name="page_action" value="payment_option" />
<input type="radio" name="payment_option" value="cash" /> Cash<br />
<input type="radio" name="payment_option" value="paypal" /> PayPal (+$1 fee)<br />
<p><input type="submit" value="Continue" /></p>
</form>