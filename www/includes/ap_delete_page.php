<?php
chdir('../');
require_once('core.php');
if (USER_ACCESS < 3) { exit(); }

$page_to_delete = $_GET['page_id'];

// get page info

$page_details = $db->assoc('SELECT * FROM `'.DB_PAGES_TABLE.'` WHERE `page_id`=?', $page_to_delete);
if ($page_details['is_default'] == 1)
{
	$pages = $db->force_multi_assoc('SELECT `page_id`, `name` FROM `'.DB_PAGES_TABLE.'` ORDER BY `name` ASC');
	$page_drop = '<select name="new_home_page">';
	if (sizeof($pages) > 0)
	{
		foreach ($pages as $page)
		{
			$page_drop .= '<option value="'.$page['page_id'].'">'.$page['name'].'</option>';
		}
	}
	$page_drop .= '</select>';
	$extra = '<div class="spaced">New Home Page: '.$page_drop.'</div>';
}


?>
<div class="center">
	<form method="post" action="<?=$body->url('includes/ap_actions.php')?>" onsubmit="Pico_DeletePageSubmit(this); return false" id="delete_form">
	<input type="hidden" name="ap_action" value="delete_page" />
	<input type="hidden" name="page_id" value="<?=$page_to_delete?>" />
	<div class="spaced">Are you sure you want to delete this page?</div>
	<?=$extra?>
	<input type="submit" value="Yes" />
	<button onclick="Pico_CloseAP(); return false">Cancel</button>
	</form>
</div>