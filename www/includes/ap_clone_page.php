<?php
chdir('../');
require_once('core.php');
if (USER_ACCESS < 3) { exit(); }

?>
<form method="post" action="<?=$body->url('includes/ap_actions.php')?>" onsubmit="Pico_AddPageSubmit(this); return false" id="page_form">
<input type="hidden" name="page_id" value="<?=$_GET['page_id']?>" />

<?php
if ($_GET['bulk'] == 1)
{
?>
<input type="hidden" name="ap_action" value="bulk_clone" />
<table border="0" cellpadding="2" cellspacing="1">
<tr>
	<td>Pages</td>
	<td>
		<textarea name="pages_to_clone" class="ap_textarea"></textarea>
	</td>
</tr>
</table>
<input name="submitbtn" type="submit" value="Clone Pages" />
<?php
}
else
{
?>
<input type="hidden" name="ap_action" value="clone_page" />
<table border="0" cellpadding="2" cellspacing="1">
<tr>
	<td>New Page Name</td>
	<td><input type="text" class="ap_text float_left" name="page[name]" onchange="Pico_CheckPage(this.value, '<?=$page_action?>')" /><div id="page_indicator" class="indicator"></div></td>
</tr>
</table>

<input name="submitbtn" type="submit" value="Clone Page" disabled="disabled" />
<?php
}
?>
</form>