<?php

if ($_GET['reload'] == 1)
{
	chdir('../../../');
	$component_id = $_GET['component_id'];
	require_once('core.php');
}

$contact_table = DB_PREFIX . 'pico_contact_form';
$history_table = DB_PREFIX . 'pico_contact_history';

if ( (!defined('USER_ACCESS')) or (USER_ACCESS < 0) ) { exit(); }

$table_query = <<<SQL
CREATE TABLE IF NOT EXISTS `$contact_table` (
	`component_id` BIGINT(11) NOT NULL,
	`recipient_address` VARCHAR(255) NOT NULL,
	`from_subject` VARCHAR(255) NOT NULL,
	`complete_message` TEXT,
	`fields` BLOB,
	`layout` VARCHAR(10),
	`submit_button` TEXT,
	`preview_message` TEXT,
	PRIMARY KEY (`component_id`)
);
SQL;
$new_table = $db->run($table_query);

$db->run(<<<SQL
CREATE TABLE IF NOT EXISTS `$history_table` (
	`entry_id` BIGINT(11) AUTO_INCREMENT,
	`component_id` BIGINT(11) NOT NULL,
	`timestamp` BIGINT(11) NOT NULL,
	`log_data` blob,
	`attachments` longblob,
	PRIMARY KEY (`entry_id`)
);
SQL
);

$settings = $db->assoc('SELECT * FROM `'.$contact_table.'` WHERE `component_id`=?', $component_id);
if (!is_array($settings)) { $settings = array(); }
?>
<div class="ap_overflow">
<h3>Settings</h3>
<form id="cf_settings" method="post" action="<?=$body->url('includes/content/contact/submit.php')?>" onsubmit="CF_UpdateSettings(this); return false">
<input type="hidden" name="component_id" value="<?=$component_id?>" />
<input type="hidden" name="page_action"  value="update" />
<table border="0" cellpadding="2" cellspacing="1">
<tr>
	<td class="bold">From Subject</td>
	<td><input type="text" class="ap_text" name="from_subject" value="<?=$settings['from_subject']?>" /></td>
</tr>
<tr>
	<td class="bold">Recipient Address</td>
	<td><input type="text" class="ap_text" name="recipient_address" value="<?=$settings['recipient_address']?>" /></td>
</tr>
<tr>
	<td colspan="2">
		<p class="bold">Form Preview Message</p>
		<input type="hidden" name="preview_message" value="" />
		<textarea class="ap_textarea_lg" name="preview_message_editor"><?=$settings['preview_message']?></textarea>
		<br />The above is what the end-user will see before they submit the form
	</td>
</tr>
<tr>
	<td colspan="2">
		<p class="bold">Form Complete Message</p>
		<input type="hidden" name="complete_message" value="" />
		<textarea class="ap_textarea_lg" name="complete_message_editor"><?=$settings['complete_message']?></textarea>
		<br />The above is what the end-user will see when they successfully complete the form
	</td>
</tr>
<tr>
	<td class="bold">Contact Form Layout</td>
	<td>
		<select name="layout">
			<option value="classic" <?=($settings['layout']=='classic') ? 'selected="selected"' : '' ?>>Classic</option>
			<option value="stacked" <?=($settings['layout']=='stacked') ? 'selected="selected"' : '' ?>>Stacked</option>
			<option value="compact" <?=($settings['layout']=='compact') ? 'selected="selected"' : '' ?>>Compact</option>
		</select>
	</td>
</tr>
<tr>
	<td class="bold">Contact Form Submit Button</td>
	<td>
	<?php
	$upload_path = $body->url('includes/content/contact/button_upload.php');
	$uploader = new Uploader($upload_path, 'CF_SubmitButton', '', '.jpg, .png, .gif', 'Image Files (jpg/png/gif)', '000000', 'ffffff');
	
	if (strlen($settings['submit_button']) > 0)
	{
		$file = 'includes/content/contact/storage/buttons/'.$settings['submit_button'];
		if (is_file($file))
		{
			echo '<img src="'.$body->url($file).'" />';
		}
	}
	
	echo '<p><input type="checkbox" name="remove_button" value="1" /> Remove Uploaded Image</p>';
	echo $uploader->Output();
	echo '<input type="hidden" name="submit_button" id="contact_submit_button" value="'.$settings['submit_button'].'" />';
	?>
	</td>
</tr>
</table>
<input type="submit" value="Update" />
</form>
</div>