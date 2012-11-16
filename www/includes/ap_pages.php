<?php
chdir('../');
require_once('core.php');
if (USER_ACCESS < 3) { exit(); }

$page_action = (is_numeric($_GET['edit'])) ? 'edit_page' : 'add_page';

if ($page_action == 'edit_page')
{
	$page_details = $db->assoc('SELECT * FROM `'.DB_PAGES_TABLE.'` WHERE `page_id`=?', $_GET['edit']);
	if ($page_details == FALSE) { exit('Invalid Page ID'); }
	$disabled = '';
}
else
{
	$page_details = array();
	$disabled = 'disabled="disabled"';
}

?>
<form method="post" action="<?=$body->url('includes/ap_actions.php')?>" onsubmit="Pico_AddPageSubmit(this); return false" id="page_form">
<input type="hidden" name="ap_action" value="<?=$page_action?>" />

<table border="0" cellpadding="2" cellspacing="1">
<tr>
	<td>Page Name</td>
	<td><input type="text" class="ap_text float_left" name="page[name]" value="<?=$page_details['name']?>" onchange="Pico_CheckPage(this.value, '<?=$page_action?>')" /><div id="page_indicator" class="indicator"></div></td>
</tr>
<tr>
	<td>Theme</td>
	<td>
		<?=ThemeDrop('page[theme]', $page_details['theme'])?>
	</td>
</tr>
<tr>
	<td>&lt;title&gt; tag</td>
	<td><input type="text" name="page[www_title]" class="ap_text" value="<?=$page_details['www_title']?>" /></td>
</tr>
<tr>
	<td>Meta Keywords</td>
	<td>
		<textarea class="ap_textarea" name="page[keywords]"><?=$page_details['keywords']?></textarea>
	</td>
</tr>
<tr>
	<td>Meta Description</td>
	<td>
		<textarea class="ap_textarea" name="page[description]"><?=$page_details['description']?></textarea>
	</td>
</tr>
<tr>
	<td>Required Access</td>
	<td>
		<?=AccessDrop('page[user_access]', $page_details['user_access'])?>
	</td>
</tr>
<tr>
	<td>Group Access</td>
	<td>
		<?=GroupDrop($_GET['edit'], 'group_access')?>
	</td>
</tr>
<tr>
	<td>Home Page</td>
	<td><input type="checkbox" name="page[is_default]" value="1" <?=($page_details['is_default'])?'checked="checked"':''?>/></td>
</tr>
</table>
<?php
if ($page_action == 'edit_page')
{
?>
<input type="hidden" name="current_page" value="<?=$_GET['edit']?>" />
<?php
}
?>
<input name="submitbtn" type="submit" value="<?=PrettyVar($page_action)?>" <?=$disabled?> />
</form>