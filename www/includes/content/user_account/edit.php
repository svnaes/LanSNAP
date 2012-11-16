<?php
$additional_info  = $db->result('SELECT `additional_info` FROM `'.DB_CONTENT.'` WHERE `component_id`=?', $component_id);
$settings         = unserialize($additional_info);
if (!is_array($settings)) { $settings = array(); }
?>

<div class="ap_overflow">
	<form method="post" action="<?=$body->url('includes/content/user_login/submit.php')?>" onsubmit="UL_UpdateSettings(this); return false">
		<input type="hidden" name="page_action" value="update" />
		<input type="hidden" name="component_id" value="<?=$component_id?>" />
		<table border="0" cellpadding="2" cellspacing="1">
		<tr>
			<td>Welcome Text</td>
			<td><textarea class="ap_textarea" name="settings[welcome-back]"><?=$settings['welcome-back']?></textarea><br />
			FIRST_NAME, LAST_NAME, EMAIL</td>
		</tr>


		</table>
		<input type="submit" value="Update" />
	</form>
</div>