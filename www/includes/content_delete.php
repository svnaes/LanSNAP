<?php
chdir('../');
require_once('core.php');
if (USER_ACCESS < 3) { exit(); }

$component_id = $_GET['component_id'];
$page_id      = $_GET['page_id'];

$component_info = $db->assoc('SELECT * FROM `'.DB_COMPONENT_TABLE.'` WHERE `component_id`=?', $component_id);

if ($component_info['delete_lock'] == 1)
{
	$disabled = 'disabled="disabled"';
	$style = 'italic';
}
else
{
	$style = 'normal';
}
?>
<form method="post" action="<?=$body->url('includes/ap_actions.php')?>" onsubmit="Pico_DeleteContentSubmit(this); return false" id="delete_content_form">
<input type="hidden" name="ap_action" value="delete_content" />
<input type="hidden" name="component_id" value="<?=$component_id?>" />
<input type="hidden" name="page_id" value="<?=$page_id?>" />
<div class="bold">Choose an action...</div>
<input type="radio" name="delete_action" value="0" onchange="this.parentNode.elements.submitbtn.disabled=false" /> Delete from this page<br />
<input type="radio" name="delete_action" value="1" onchange="this.parentNode.elements.submitbtn.disabled=false" <?=$disabled?> /> <span style="font-style: <?=$style?>">Delete from all pages</span><br />
<input type="radio" name="delete_action" value="2" onchange="this.parentNode.elements.submitbtn.disabled=false" <?=$disabled?> /> <span style="font-style: <?=$style?>">Completely remove (this cannot be undone!)</span><br />
<input name="submitbtn" type="submit" value="Delete" disabled="disabled" />
</form>