<?php
chdir('../');
require_once('core.php');
if (USER_ACCESS < 3) { exit(); }

$payment_settings     = DB_PREFIX . 'pico_payment_settings';
$payment_transactions = DB_PREFIX . 'pico_payment_transactions';

$db->run(<<<SQL
CREATE TABLE IF NOT EXISTS `$payment_settings` (
	`payment_method` VARCHAR(20),
	`test_mode` TINYINT(1) NOT NULL DEFAULT 0,
	`admin_email` VARCHAR(255),
	`pp_address` VARCHAR(255),
	`pp_api_user` VARCHAR(255),
	`pp_api_pass` VARCHAR(255),
	`pp_api_signature` VARCHAR(255),
	`authnet_api_login_id` VARCHAR(255),
	`authnet_api_transaction_key` VARCHAR(255),
	`vm_merchant_id` VARCHAR(255),
	`vm_user_id` VARCHAR(255),
	`vm_pin` VARCHAR(255),
	`shipping_method` VARCHAR(20),
	`fedex_settings` BLOB,
	`ship_settings` BLOB
)
SQL
);

$db->run(<<<SQL
CREATE TABLE IF NOT EXISTS `$payment_transactions` (
	`entry_id` BIGINT(11) AUTO_INCREMENT,
	`user_id` BIGINT(11) NOT NULL DEFAULT 0,
	`component_id` BIGINT(11) NOT NULL,
	`timestamp` BIGINT(11) NOT NULL,
	`transaction_id` VARCHAR(255),
	`test_mode` TINYINT(1) NOT NULL DEFAULT 0,
	`amount_gross` FLOAT,
	`amount_net` FLOAT,
	`fee` FLOAT,
	`note` TEXT,
	`custom_status` BIGINT(11),
	`payment_type` TEXT,
	`payment_method` VARCHAR(20),
	PRIMARY KEY (`entry_id`)
)
SQL
);

$settings = $db->assoc('SELECT * FROM `'.$payment_settings.'`');
if (!is_array($settings))
{
	$db->run('INSERT INTO `'.$payment_settings.'` (`payment_method`) VALUES (?)', 'none');
	$settings = array();
}

$settings['fedex_settings'] = unserialize($settings['fedex_settings']);
if (!is_array($settings['fedex_settings'])) { $settings['fedex_settings'] = array(); }

$settings['ship_settings'] = unserialize($settings['ship_settings']);
if (!is_array($settings['ship_settings'])) { $settings['ship_settings'] = array(); }

?>
<div class="ap_overflow">
	<p>Here you can configure the payment settings for your site. Components that require user payments will use the settings set here</p>
	<form method="post" action="<?=$body->url('includes/ap_actions.php')?>" onsubmit="Pico_PaymentSettingsUpdate(this); return false">
	<input type="hidden" name="ap_action" value="update_payment_settings" />
	
	
	<table border="0" cellpadding="0" cellspacing="0">
	<tr>
		<td valign="top" width="50%">
			<table border="0" cellpadding="0" cellspacing="0" class="admin_list">
			<tr>
				<td class="bold">Payment Gateway</td>
				<td>
					<select name="payment_method">
						<option <?=($settings['payment_method'] == 'paypal') ? 'selected="selected"' : ''?> value="paypal">PayPal</option>
						<option <?=($settings['payment_method'] == 'authnet') ? 'selected="selected"' : ''?> value="authnet">Authorize.net</option>
						<option <?=($settings['payment_method'] == 'virtual_merchant') ? 'selected="selected"' : ''?> value="virtual_merchant">Virtual Merchant</option>
					</select>
				</td>
			</tr>
			<tr>
				<td class="bold">Test Mode</td>
				<td>
					<select name="test_mode">
						<option <?=($settings['test_mode'] == 0) ? 'selected="selected"' : ''?> value="0">Off</option>
						<option <?=($settings['test_mode'] == 1) ? 'selected="selected"' : ''?> value="1">On</option>
					</select>
				</td>
			</tr>
			<tr>
				<td class="bold">Admin Email Address</td>
				<td><input type="text" name="admin_email" value="<?=$settings['admin_email']?>" /></td>
			</tr>
			<tr>
				<td class="bold">PayPal Address</td>
				<td><input type="text" name="pp_address" value="<?=$settings['pp_address']?>" /></td>
			</tr>
			<tr>
				<td class="bold">PayPal Api User</td>
				<td><input type="text" name="pp_api_user" value="<?=$settings['pp_api_user']?>" /></td>
			</tr>
			<tr>
				<td class="bold">PayPal Api Password</td>
				<td><input type="text" name="pp_api_pass" value="<?=$settings['pp_api_pass']?>" /></td>
			</tr>
			<tr>
				<td class="bold">PayPal Api Signature</td>
				<td><input type="text" name="pp_api_signature" value="<?=$settings['pp_api_signature']?>" /></td>
			</tr>
			<tr>
				<td class="bold"0>Authorize.net - API Login ID</td>
				<td><input type="text" name="authnet_api_login_id" value="<?=$settings['authnet_api_login_id']?>" /></td>
			</tr>
			<tr>
				<td class="bold">Authorize.net - API Transaction Key</td>
				<td><input type="text" name="authnet_api_transaction_key" value="<?=$settings['authnet_api_transaction_key']?>" /></td>
			</tr>
			<tr>
				<td class="bold">Virtual Merchant - Merchant ID</td>
				<td><input type="text" name="vm_merchant_id" value="<?=$settings['vm_merchant_id']?>" /></td>
			</tr>
			<tr>
				<td class="bold">Virtual Merchant - User ID</td>
				<td><input type="text" name="vm_user_id" value="<?=$settings['vm_user_id']?>" /></td>
			</tr>
			<tr>
				<td class="bold">Virtual Merchant - Pin</td>
				<td><input type="text" name="vm_pin" value="<?=$settings['vm_pin']?>" /></td>
			</tr>
			</table>
		</td>
		<td valign="top" style="padding-left: 10px">
			<table border="0" cellpadding="0" cellspacing="0" class="admin_list">
			<tr>
				<td class="bold">Shipping Name</td>
				<td><input type="text" name="ship_settings[name]" value="<?=$settings['ship_settings']['name']?>" /></td>
			</tr>
			<tr>
				<td class="bold">Shipping Address</td>
				<td><input type="text" name="ship_settings[address]" value="<?=$settings['ship_settings']['address']?>" /></td>
			</tr>
			<tr>
				<td class="bold">Shipping City</td>
				<td><input type="text" name="ship_settings[city]" value="<?=$settings['ship_settings']['city']?>" /></td>
			</tr>
			<tr>
				<td class="bold">Shipping State</td>
				<td><input type="text" name="ship_settings[state]" value="<?=$settings['ship_settings']['state']?>" size="2" maxlength="2" /></td>
			</tr>
			<tr>
				<td class="bold">Shipping Zip</td>
				<td><input type="text" name="ship_settings[zip]" value="<?=$settings['ship_settings']['zip']?>" size="5" maxlength="5" /></td>
			</tr>
			<tr>
				<td class="bold">Shipping Gateway</td>
				<td>
					<select name="shipping_method">
						<option <?=($settings['shipping_method'] == 'fedex_express') ? 'selected="selected"' : ''?> value="fedex_express">FedEx Express</option>
						<option <?=($settings['shipping_method'] == 'fedex_ground') ? 'selected="selected"' : ''?> value="fedex_ground">FedEx Ground</option>
						<option <?=($settings['shipping_method'] == 'usps') ? 'selected="selected"' : ''?> value="usps">USPS</option>
					</select>
				</td>
			</tr>
			<tr>
				<td class="bold">FedEx Authentication Key</td>
				<td><input type="text" name="fedex_settings[auth_key]" value="<?=$settings['fedex_settings']['auth_key']?>" /></td>
			</tr>
			<tr>
				<td class="bold">FedEx Account Number</td>
				<td><input type="text" name="fedex_settings[account_number]" value="<?=$settings['fedex_settings']['account_number']?>" /></td>
			</tr>
			<tr>
				<td class="bold">FedEx Production Password</td>
				<td><input type="text" name="fedex_settings[password]" value="<?=$settings['fedex_settings']['password']?>" /></td>
			</tr>
			<tr>
				<td class="bold">FedEx Meter Number</td>
				<td><input type="text" name="fedex_settings[meter_number]" value="<?=$settings['fedex_settings']['meter_number']?>" /></td>
			</tr>
			</table>
		</td>
	</tr>
	</table>
		
	<input type="submit" value="Update" />
	</form>
</div>