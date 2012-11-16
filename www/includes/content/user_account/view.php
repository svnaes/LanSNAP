<?php
global $params;

if (!defined('USER_ID'))
{
	return;
}

$additional_info  = $db->result('SELECT `additional_info` FROM `'.DB_CONTENT.'` WHERE `component_id`=?', $component_id);
$settings         = unserialize($additional_info);
if (!is_array($settings)) { $settings = array(); }

$user_info = $db->assoc('SELECT * FROM `'.DB_USER_TABLE.'` WHERE `id`=?', USER_ID);

if ($params[1] == 'account-settings')
{
	echo '<h3>Account Settings</h3>';
	echo '<p class="back_link"><a href="'.$body->url(CURRENT_ALIAS).'">Back</a></p>';
	
	include('includes/content/user_account/account_settings.php');
}
elseif ($params[1] == 'transaction-history')
{
	echo '<h3>Transaction History</h3>';
	echo '<p class="back_link"><a href="'.$body->url(CURRENT_ALIAS).'">Back</a></p>';
	
	include('includes/content/user_account/transaction-history.php');
}
else
{
	$output = $settings['welcome-back'];
	
	
	$output = str_replace('FIRST_NAME', $user_info['first_name'], $output);
	$output = str_replace('LAST_NAME', $user_info['last_name'], $output);
	$output = str_replace('EMAIL', $user_info['email_address'], $output);
	
	echo nl2br($output);
	
	echo '<ul class="my_account_menu">
	<li><a href="'.$body->url(CURRENT_ALIAS . '/transaction-history').'">Transaction History</a></li>
	<li><a href="'.$body->url(CURRENT_ALIAS . '/account-settings').'">Account Settings</a></li>
	</ul>';
}
?>