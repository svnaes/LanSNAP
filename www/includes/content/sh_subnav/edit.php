<?php
$additional_info  = $db->result('SELECT `additional_info` FROM `'.DB_CONTENT.'` WHERE `instance_id`=?', $instance_id);
$settings         = unserialize($additional_info);
if (!is_array($settings)) { $settings = array(); }
?>
<div class="ap_overflow">
	<form method="post" action="<?=$body->url('includes/content/sh_subnav/submit.php')?>" onsubmit="new Ajax.Form(this); return false">
	<input type="hidden" name="page_action" value="update" />
	<input type="hidden" name="component_id" value="<?=$component_id?>" />
	<table border="0" cellpadding="2" cellspacing="1">
	<tr>
		<td>Title</td>
		<td><input type="text" name="settings[title]" value="<?=$settings['title']?>" /></td>
	</tr>
	<tr>
		<td>Main Nav</td>
		<td><input type="checkbox" name="settings[main_nav]" value="1" <?=(($settings['main_nav']==1)?'checked="checked"' : '')?>/></td>
	</tr>
	<tr>
		<td>Dropdown</td>
		<td><input type="checkbox" name="settings[dropdown]" value="1" <?=(($settings['dropdown']==1)?'checked="checked"' : '')?>/></td>
	</tr>
	<tr>
		<td>Test Mode</td>
		<td><input type="checkbox" name="settings[test_mode]" value="1" <?=(($settings['test_mode']==1)?'checked="checked"' : '')?>/></td>
	</tr>
	</table>
	<input type="submit" value="Update" />
	</form>
	
	<p><span class="click" onclick="Pico_SiteHeirarchy()">Go To Site Hierarchy</span></p>
</div>