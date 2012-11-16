<?php

if ($_GET['reload'] == 1)
{
	chdir('../../../');
	require_once('core.php');
	$component_id = $_GET['component_id'];
}

if ( (!defined('USER_ACCESS')) or (USER_ACCESS < 4) ) { exit(); }


include('includes/content/lansnap/database.php');
require_once('includes/content/lansnap/functions.php');

// get all settings

$num_addresses = LS_GetSetting('num_addresses');

?>
<div class="ap_overflow">
	<form method="post" action="<?=$body->url('includes/content/lansnap/submit.php')?>" onsubmit="LS_UpdateSettings(this); return false" style="height: auto" />
	<input type="hidden" name="page_action" value="update_settings" /> 
	<table border="0" cellpadding="2" cellspacing="1" class="admin_list">
		<tr class="a">
			<td>Lansnap TLD</td>
			<td><input type="text" name="settings[domain_tld]" value="<?=LS_GetSetting('domain_tld')?>" />.lan</td>
		</tr>
		<!--tr class="b">
			<td>DNS Server 1</td>
			<td><input type="text" name="settings[dns_server1]" value="<?=LS_GetSetting('dns_server1')?>" /></td>
		</tr>
		<tr class="a">
			<td>DNS Server 2</td>
			<td><input type="text" name="settings[dns_server2]" value="<?=LS_GetSetting('dns_server2')?>" /></td>
		</tr-->
		<tr class="b">
			<td>Number of Addresses</td>
			<td>
				<select name="settings[num_addresses]">
					<option value="512" <?=($num_addresses==512) ? 'selected="selected"' : ''?>>512</option>
					<option value="1024" <?=($num_addresses==1024) ? 'selected="selected"' : ''?>>1024</option>
					<option value="2048" <?=($num_addresses==2048) ? 'selected="selected"' : ''?>>2048</option>
				</select>
			</td>
		</tr>
		<!--tr class="a">
			<td>External DB Host</td>
			<td><input type="text" name="settings[externaldb_host]" value="<?=LS_GetSetting('externaldb_host')?>" /></td>
		</tr>
		<tr class="b">
			<td>External DB Name</td>
			<td><input type="text" name="settings[externaldb_name]" value="<?=LS_GetSetting('externaldb_name')?>" /></td>
		</tr>
		<tr class="a">
			<td>External DB Username</td>
			<td><input type="text" name="settings[externaldb_user]" value="<?=LS_GetSetting('externaldb_user')?>" /></td>
		</tr>
		<tr class="b">
			<td>External DB Password</td>
			<td><input type="text" name="settings[externaldb_pass]" value="<?=LS_GetSetting('externaldb_pass')?>" /></td>
		</tr>
		<tr class="a">
			<td>External DB Table</td>
			<td><input type="text" name="settings[externaldb_table]" value="<?=LS_GetSetting('externaldb_table')?>" /></td>
		</tr-->
		<tr class="b">
			<td>Layout Width</td>
			<td><input type="text" name="settings[layout_width]" value="<?=LS_GetSetting('layout_width')?>" /></td>
		</tr>
		<tr class="a">
			<td>Layout Height</td>
			<td><input type="text" name="settings[layout_height]" value="<?=LS_GetSetting('layout_height')?>" /></td>
		</tr>
		<tr class="b">
			<td>User Register Message</td>
			<td><textarea class="ap_textarea" name="settings[user_register_message]"><?=LS_GetSetting('user_register_message')?></textarea></td>
		</tr>
		<tr class="a">
			<td>User Complete Registration</td>
			<td><textarea class="ap_textarea" name="settings[user_complete_registration]"><?=LS_GetSetting('user_complete_registration')?></textarea></td>
		</tr>
	</table>
	<input type="submit" value="Update" />
	</form>

<?php

$layout_url = $body->url('includes/content/lansnap/layout/index.php?component_id='.$component_id);
echo <<<HTML
<ul>
<li class="click" onclick="window.open('$layout_url','lansnap_layout','toolbar=no,scrollbars=yes,resizable=yes,menubar=no,location=no')">Launch Layout Editor</li>
<li class="click" onclick="LS_ExternalDB($component_id)">External Database Config</li>
<li class="click" onclick="LS_BindAllIPs($component_id, 0)">Bind All IPs</li>
<li class="click" onclick="LS_BindAllIPs($component_id, 1)">Unbind All IPs</li>
</ul>

HTML;
?>
</div>