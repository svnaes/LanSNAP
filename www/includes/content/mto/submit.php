<?php
chdir('../../../');
require_once('core.php');
if (USER_ACCESS < 3) { exit(); }

$action = $_REQUEST['page_action'];

$menu_items  = DB_PREFIX . 'menu_items';
$menu_orders = DB_PREFIX . 'menu_orders';
$order_items = DB_PREFIX . 'menu_order_items';

if ($action == 'add')
{
	$item_name    = trim(stripslashes($_POST['item_name']));
	$item_price   = trim(stripslashes($_POST['item_price']));
	$component_id = $_POST['component_id'];
	
	$db->run('INSERT INTO `'.$menu_items.'` (`item_name`, `price`, `component_id`) VALUES (?,?,?)',
		$item_name, $item_price, $component_id
	);
}
elseif ($action == 'edit')
{
	$item_name    = trim(stripslashes($_POST['item_name']));
	$item_price   = trim(stripslashes($_POST['item_price']));
	$item_id      = trim(stripslashes($_POST['item_id']));
	
	$db->run('UPDATE `'.$menu_items.'` SET `item_name`=?, `price`=? WHERE `item_id`=?',
		$item_name, $item_price, $item_id
	);
}
elseif ($action == 'delete_item')
{
	$item_id = $_GET['item_id'];
	$db->run('UPDATE `'.$menu_items.'` SET `display`=? WHERE `item_id`=?', 0, $item_id);
}
elseif ($action == 'close_order')
{
	$db->run('UPDATE `'.$menu_orders.'` SET `status`=? WHERE `order_id`=?', 2, $_GET['order_id']);
}
elseif ($action == 'cancel_order')
{
	$db->run('UPDATE `'.$menu_orders.'` SET `status`=? WHERE `order_id`=?', 3, $_GET['order_id']);
}
elseif ($action == 'settings')
{
	$settings     = $_POST['settings'];
	$component_id = $_POST['component_id'];
	foreach ($settings as $key=>$val)
	{
		$settings[$key] = trim(stripslashes($val));
	}
	
	$db->run('UPDATE `'.DB_COMPONENT_TABLE.'` SET `additional_info`=? WHERE `component_id`=?', serialize($settings), $component_id);
}
?>