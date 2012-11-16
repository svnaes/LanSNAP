<?php

$options = $db->result('SELECT `additional_info` FROM `'.DB_CONTENT.'` WHERE `component_id`=?', $component_id);
$settings = unserialize($options);
if (!is_array($settings)) { $settings = array(); }

$fv = array();

$swf_path = $body->url('includes/content/linkmenu/FileUploader.swf');
$upload_path = $body->url('includes/content/linkmenu/upload.php');

$fv['foo'] = 'bar'; // all, text, video, image
$fv['uploadType'] = 'all'; // all, text, video, image
$fv['fileCallback'] = 'LM_FontUploaded';
$fv['uploadPath'] = $upload_path;

$p = array();
foreach($fv as $key=>$val)
{
	$val = urlencode($val);
	$p[] = "$key=$val";
}
$fvText = implode('&', $p);

?>

<object classid="clsid:d27cdb6e-ae6d-11cf-96b8-444553540000" codebase="http://download.macromedia.com/pub/shockwave/cabs/flash/swflash.cab#version=8,0,0,0" width="500" height="100" id="live_preview" align="middle">
	<param name="allowScriptAccess" value="sameDomain" />
	<param name="allowFullScreen" value="false" />
	<param name="movie" value="<?=$swf_path?>" />
	<param name="loop" value="false" />
	<param name="menu" value="false" />
	<param name="quality" value="high" />
	<param name="wmode" value="transparent" />
	<param name="flashvars" value="<?=$fvText?>" />
	<embed src="<?=$swf_path?>" flashvars="uploadPath=<?=$fvText?>" loop="false" menu="false" quality="high" wmode="transparent" width="500" height="100" name="live_preview" align="middle" allowScriptAccess="sameDomain" allowFullScreen="false" type="application/x-shockwave-flash" pluginspage="http://www.macromedia.com/go/getflashplayer" />
</object>

<form id="linkmenu_fontsettings" method="post" action="<?=$body->url('includes/content/linkmenu/submit.php')?>" onsubmit="LM_UpdateSettings(this); return false">
<input type="hidden" name="instance_id"  value="<?=$instance_id?>" />
<input type="hidden" name="component_id" value="<?=$component_id?>" />
<input type="hidden" name="page_action"  value="font_options" />

<table border="0" cellpadding="3" cellspacing="0">
<tr>
	<td>Enabled</td>
	<td><input type="checkbox" value="yes" name="settings[enabled]" <?=($settings['enabled'] == 'yes') ? 'checked="checked"': '' ?> /></td>
</tr>
<tr>
	<td>Font Size</td>
	<td><input type="text" name="settings[font_size]" value="<?=$settings['font_size']?>" /></td>
</tr>
<tr>
	<td>Font Color</td>
	<td><input type="text" name="settings[font_color]" value="<?=$settings['font_color']?>" /></td>
</tr>
<tr>
	<td>Active Color</td>
	<td><input type="text" name="settings[active_color]" value="<?=$settings['active_color']?>" /></td>
</tr>
<tr>
	<td>Font</td>
	<td><input type="text" id="font_file" name="settings[font]" value="<?=$settings['font']?>" readonly="readonly" /></td>
</tr>
<tr>
	<td>Dropshadow</td>
	<td><input type="checkbox" value="yes" name="settings[dropshadow]" <?=($settings['dropshadow'] == 'yes') ? 'checked="checked"': '' ?> /></td>
</tr>
<tr>
	<td>LI Separator</td>
	<td><input type="text" name="settings[separator]" value="<?=$settings['separator']?>" /></td>
</tr>
</table>

<input type="submit" value="Update" name="submit_btn" />
</form>