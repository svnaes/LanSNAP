<?php
chdir('../');
require_once('core.php');
if (USER_ACCESS < 4) { exit(); }

$sh_table = DB_PREFIX . 'pico_site_heirarchy';

$db->run(<<<SQL
CREATE TABLE IF NOT EXISTS `$sh_table` (
	`entry_id` BIGINT(11) AUTO_INCREMENT,
	`page_id` BIGINT(11) NOT NULL DEFAULT 0,
	`text` VARCHAR(255) NOT NULL DEFAULT '',
	`parent` BIGINT(11) NOT NULL DEFAULT 0,
	`external_url` TEXT,
	`position` BIGINT(11) NOT NULL DEFAULT 0,
	`show_in_nav` TINYINT(1) NOT NULL DEFAULT 1,
	`linked` TINYINT(1) NOT NULL DEFAULT 1,
	PRIMARY KEY (`entry_id`)
)
SQL
);

// get all items in the heirarchy
$heirarchy_data = $db->force_multi_assoc('SELECT * FROM `'.$sh_table.'` ORDER BY `parent` ASC, `position` ASC');
$all_items = array();
if ( (sizeof($heirarchy_data) > 0) and (is_array($heirarchy_data)) )
{
	foreach($heirarchy_data as $item)
	{
		if (is_numeric($item['page_id'])) // put all pages into $all_items so we can reference it later
		{
			$entry_id = $item['entry_id'];
			$all_items[$entry_id] = $item['page_id'];
		}
	}
}

// get all pages
$page_data = $db->force_multi_assoc('SELECT * FROM `'.DB_PAGES_TABLE.'` ORDER BY `name` ASC');
$pages = array();
if ( (sizeof($page_data) > 0) and (is_array($page_data)) )
{
	foreach($page_data as $page)
	{
		$page_id = $page['page_id'];
		if (!in_array($page_id, $all_items)) // we only want pages that have NOT been included in the heirarchy
		{
			$pages[$page_id] = $page['name'];
		}
	}
}

// now we can build a dropdown of available pages

$page_dropdown = '<select name="page_ids[]" multiple="multiple" size="20" style="width: 250px">';
if (sizeof($pages) > 0)
{
	foreach ($pages as $id=>$name)
	{
		$page_dropdown .= '<option value="'.$id.'">'.$name.'</option>';
	}
}
$page_dropdown .= '</select>';

$site_heirarchy = SiteGetHeirarchy();

$form_url = $body->url('includes/ap_actions.php');

$form = <<<HTML
<form method="post" action="$form_url" onsubmit="Pico_SHSubmit(this); return false" id="add_sh_form" style="display: none">
	<input type="hidden" name="parent" value="0" />
	<input type="hidden" name="ap_action" value="add_sh_item" />
	<p class="bold">Select pages</p>
	$page_dropdown
	<div class="mini">(hold down CTRL to select multiple items)</div>
	<p class="bold">Or add a menu item</p>
	<table border="0" cellpadding="2" cellspacing="1">
	<tr><td>Name</td><td><input type="text" name="item_name" value="" /></td></tr>
	<tr><td>URL</td><td><input type="text" name="item_url" value="" /></td></tr>
	</table>
	<input type="submit" value="Add" />
</form>
HTML;

//echo '<pre>'.print_r($site_heirarchy, true).'</pre>';

$show = $_GET['show'];
if ($show == 1)
{
	echo SiteHeirarchyDisplay($site_heirarchy);
}
elseif ($show == 2)
{
	echo $form;
}
else
{
?>
<div style="float: left; width: 500px; height: 300px" id="ap_sh_list">
	<div class="ap_overflow">
		<div id="site_heirarchy_display"><?=SiteHeirarchyDisplay($site_heirarchy)?></div>
		<div class="click" onclick="Pico_SHAddItem(0)">Add Top Level Item</div>
	</div>
</div>
<div style="float: right; width: 250px; padding-right: 10px;">
	<div id="site_heirarchy_form">
		<?=$form?>
	</div>
</div>
<?php
}
?>