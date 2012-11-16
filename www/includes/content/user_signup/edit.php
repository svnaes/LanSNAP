<?php

if ($_GET['reload'] == 1)
{
	$component_id = $_GET['component_id'];
	chdir('../../../');
	require_once('core.php');
}

if ((!defined('USER_ACCESS')) or (USER_ACCESS < 3)) { exit(); }

$group_payment_settings = DB_PREFIX . 'user_group_payment_settings';
$db->run(<<<SQL
CREATE TABLE IF NOT EXISTS `$group_payment_settings` (
	`entry_id` BIGINT(11) AUTO_INCREMENT,
	`component_id` BIGINT(11) NOT NULL,
	`group_id` BIGINT(11) NOT NULL,
	`duration` BIGINT(11) NOT NULL,
	`cost` FLOAT,
	`display` TINYINT(1) NOT NULL DEFAULT 1,
	`description` TEXT,
	PRIMARY KEY (`entry_id`)
)
SQL
);

$additional_info  = $db->result('SELECT `additional_info` FROM `'.DB_CONTENT.'` WHERE `component_id`=?', $component_id);
$settings         = unserialize($additional_info);
if (!is_array($settings)) { $settings = array(); }

// get all the groups
$group_table  = DB_PREFIX . 'pico_groups';
$groups = $db->force_multi_assoc('SELECT * FROM `'.$group_table.'` ORDER BY `name` ASC');

$output = '';
if (is_array($groups))
{
	$output .= '<table border="0" cellpadding="2" cellspacing="1">';
	$output .= '<tr><th>Group</th><th>Enabled</th><th>Actions</th></tr>';
	
	$enabled_groups = unserialize($settings['groups']);
	if (!is_array($enabled_groups)) { $enabled_groups = array(); }
	
	foreach ($groups as $group)
	{
		$edit = (in_array($group['group_id'], $enabled_groups)) ? '<img src="'.$body->url('includes/icons/content.png').'" title="Payment Settings" class="icon click" onclick="US_PaymentSettings('.$component_id.', '.$group['group_id'].')" />' : '';
	
		$checked = (in_array($group['group_id'], $enabled_groups)) ? 'checked="checked"' : '';
		$output .= '<tr>
			<td>'.$group['name'].'</td>
			<td><input type="checkbox" name="settings[groups][]" value="'.$group['group_id'].'" '.$checked.' /></td>
			<td>'.$edit.'</td>
		</tr>';
	}
	
	$output .= '</table>';
}

?>
<div class="ap_overflow">
	<p>From here you can configure what User Groups you will allow access to.</p>
	<form method="post" action="<?=$body->url('includes/content/user_signup/submit.php')?>" onsubmit="US_UpdateSettings(this); return false">
		<input type="hidden" name="page_action" value="update" />
		<input type="hidden" name="component_id" value="<?=$component_id?>" />
		<?=$output?>
		
		<p class="bold">Step 1 - Intro Text</p>
		<textarea name="settings[step1]" class="ap_textarea"><?=$settings['step1']?></textarea>
		<p class="bold">Step 2 - Make Payment</p>
		<textarea name="settings[step2]" class="ap_textarea"><?=$settings['step2']?></textarea>
		<p class="bold">Step 3 - Complete</p>
		<textarea name="settings[step3]" class="ap_textarea"><?=$settings['step3']?></textarea>
		<p class="bold">Invoice Description</p>
		<p class="small">What will be shown to the user when completing their transaction (in PayPal)</p>
		<input type="text" name="settings[invoice_description]" value="<?=$settings['invoice_description']?>" />
		<p class="bold">Redirection Link</p>
		<p class="small">Where users will be sent upon successful registration</p>
		<input type="text" name="settings[redirection_link]" value="<?=$settings['redirection_link']?>" />
		<p class="small">Log user in when registered <input type="checkbox" name="settings[log_user_in]" value="1" <?=($settings['log_user_in'] == 1)? 'checked="checked"' : ''?>></p>
		<input type="submit" value="Update" />
	</form>
</div>