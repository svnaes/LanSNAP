<?php

if ($_GET['reload'] == 1)
{
	$component_id = $_GET['component_id'];
	chdir('../../../');
	require_once('core.php');
}

if ( (!defined('USER_ACCESS')) or (USER_ACCESS < 3) ) { exit(); }

$menu_items  = DB_PREFIX . 'menu_items';
$menu_orders = DB_PREFIX . 'menu_orders';
$order_items = DB_PREFIX . 'menu_order_items';

if ($_GET['edit_id'] != 0)
{
	$edit_id = $_GET['edit_id'];
	$extra   = '<input type="hidden" name="item_id" value="'.$edit_id.'" />';
	$action  = 'edit';
	$header  = 'Edit Item';
	$item    = $db->assoc('SELECT * FROM `'.$menu_items.'` WHERE `item_id`=?', $edit_id);
}
else
{
	$extra  = '';
	$action = 'add';
	$header = 'Add Item';
	$item   = array();
}

$db->run(<<<SQL
CREATE TABLE IF NOT EXISTS `$menu_items` (
	`item_id` BIGINT(11) AUTO_INCREMENT,
	`item_name` VARCHAR(50),
	`price` FLOAT,
	`component_id` BIGINT(11) NOT NULL,
	PRIMARY KEY (`item_id`)
)
SQL
);

$db->run(<<<SQL
CREATE TABLE IF NOT EXISTS `$menu_orders` (
	`order_id` BIGINT(11) AUTO_INCREMENT,
	`timestamp` BIGINT(11),
	`user_id` BIGINT(11),
	`total` FLOAT,
	`transaction_id` VARCHAR(50),
	`pay_method` VARCHAR(10),
	`status` TINYINT(1) NOT NULL DEFAULT 0,
	`deliver` TINYINT(1) NOT NULL DEFAULT 0,
	`component_id` BIGINT(11),
	`notes` TEXT,
	PRIMARY KEY (`order_id`)
)
SQL
);

$db->run(<<<SQL
CREATE TABLE IF NOT EXISTS `$order_items` (
	`entry_id` BIGINT(11) AUTO_INCREMENT,
	`item_id` BIGINT(11),
	`qty` INT(2),
	`order_id` BIGINT(11),
	PRIMARY KEY (`entry_id`)
)
SQL
);

?>

<div class="ap_overflow">
	<?php
	if ($action == 'add')
	{
		echo '<h3>Current Menu</h3>';
		$items = $db->force_multi_assoc('SELECT * FROM `'.$menu_items.'` WHERE `component_id`=? AND `display`=? ORDER BY `item_name` ASC', $component_id, 1);
		if (is_array($items))
		{
			$output  = '<table cellpadding="0" cellspacing="1" border="0" class="admin_list">';
			$output .= '<tr><th>Name</th><th>Price</th><th>Actions</th></tr>';
			$counter = 0;
			foreach ($items as $_item)
			{
				$class   = ($counter % 2 == 0) ? 'a' : 'b'; $counter++;
				$edit    = '<img src="'.$body->url('includes/icons/edit.png').'" class="icon click" onclick="MTO_Reload('.$component_id.', '.$_item['item_id'].')" />';
				$delete  = '<img src="'.$body->url('includes/icons/delete.png').'" class="icon click" onclick="MTO_Delete('.$component_id.', '.$_item['item_id'].')" />';
				$output .= '<tr class="'.$class.'"><td>'.$_item['item_name'].'</td><td>$'.number_format($_item['price'],2).'</td><td>'.$edit.$delete.'</td></tr>';
			}
			$output .= '</table>';
			
			echo $output;
		}
	}
	?>
	
	<h3><?=$header?></h3>
	
	<form method="post" action="<?=$body->url('includes/content/mto/submit.php')?>" onsubmit="MTO_Submit(this); return false" style="height: auto">
	<input type="hidden" name="component_id" value="<?=$component_id?>" />
	<input type="hidden" name="page_action" value="<?=$action?>" />
	<?=$extra?>
	<table cellpadding="0" cellspacing="1" border="0" class="admin_list">
	<tr class="a">
		<td>Menu Item Name</td>
		<td><input type="text" name="item_name" value="<?=$item['item_name']?>" /></td>
	</tr>
	<tr class="b">
		<td>Price</td>
		<td><input type="text" name="item_price" value="<?=$item['price']?>" /></td>
	</tr>
	</table>
	<input type="submit" value="<?=$header?>" />
	</form>
	
	<h3>Settings</h3>
	
	<?php
	$ai   = $db->result('SELECT `additional_info` FROM `'.DB_COMPONENT_TABLE.'` WHERE `component_id`=?', $component_id);
	$settings = unserialize($ai);
	if (!is_array($settings)) { $settings = array(); }
	?>
	
	<form method="post" action="<?=$body->url('includes/content/mto/submit.php')?>" onsubmit="MTO_SaveSettings(this); return false" />
	<input type="hidden" name="component_id" id="component_id" value="<?=$component_id?>" />
	<input type="hidden" name="page_action" value="settings" />
	<table border="0" cellpadding="2" cellspacing="1" class="admin_list">
	<tr class="b">
		<td>Deliver enabled</td>
		<td><input type="checkbox" name="settings[delivery_enabled]" value="1" <?=($settings['delivery_enabled']==1) ? 'checked="checked"' : '' ?>/></td>
	</tr>
	<tr class="a">
		<td>Menu enabled</td>
		<td><input type="checkbox" name="settings[enabled]" value="1" <?=($settings['enabled']==1) ? 'checked="checked"' : '' ?>/></td>
	</tr>
	<tr class="b">
		<td>Order placed</td>
		<td><textarea class="ap_textarea" name="settings[order_placed]"><?=htmlspecialchars($settings['order_placed'])?></textarea></td>
	</tr>
	<tr class="a">
		<td>Order in progress</td>
		<td><textarea class="ap_textarea" name="settings[order_in_progress]"><?=htmlspecialchars($settings['order_in_progress'])?></textarea></td>
	</tr>
	<tr class="b">
		<td>Menu Closed</td>
		<td><textarea class="ap_textarea" name="settings[menu_closed]"><?=htmlspecialchars($settings['menu_closed'])?></textarea></td>
	</tr>
	<tr class="a">
		<td>Special Notes</td>
		<td><textarea class="ap_textarea" name="settings[special_notes]"><?=htmlspecialchars($settings['special_notes'])?></textarea></td>
	</tr>
	
	</table>
	<input type="submit" value="Save" />
	</form>
</div>