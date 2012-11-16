<?php

$ai       = $db->result('SELECT `additional_info` FROM `'.DB_COMPONENT_TABLE.'` WHERE `component_id`=?', $component_id);
$settings = unserialize($ai);
if (!is_array($settings)) { $settings = array(); }

if (USER_ACCESS >= 2)
{
	global $params;
	if ($params[1] == 'order-window')
	{
		echo '<div id="order_window"></div>
		<script type="text/javascript">
			var func = function() {
				MTO_OrderRefresh('.$component_id.');
			}
			add_load_event(func);
		</script>
		';
		return;
	}
	else
	{
		echo '<a href="'.$body->url(CURRENT_ALIAS . '/order-window').'">Order Window</a>';
	}
}

if ($settings['enabled'] != 1)
{
	echo $settings['menu_closed'];
	return;
}
//echo "in";

//echo '<pre>'.print_r($_SERVER, true).'</pre>';

require_once('includes/content/lansnap/functions.php');
$user_id = LS_GetUserIDByIP($_SERVER['HTTP_X_FORWARDED_FOR']);
if ($user_id == 0) { return; }
//if ($user_id == 0) { $user_id = 1;}

// see if this user has an order open

$menu_items  = DB_PREFIX . 'menu_items';
$menu_orders = DB_PREFIX . 'menu_orders';
$order_items = DB_PREFIX . 'menu_order_items';

$order_id = $db->result('SELECT `order_id` FROM `'.$menu_orders.'` WHERE `component_id`=? AND `status` < ? AND `user_id`=?', $component_id, 2, $user_id);

if (is_numeric($order_id))
{
	// get order info
	$order_info = $db->assoc('SELECT * FROM `'.$menu_orders.'` WHERE `order_id`=?', $order_id);
	
	if ($order_info['status'] == 0)
	{
		// show payment options
		include('includes/content/mto/payment.php');
	}
	elseif ($order_info['status'] == 1)
	{
		echo $settings['order_in_progress'];
		//echo '<p>Your order is in progress. Please check in about 10 minutes unless you are having your food delivered to your seat. If this is an error please see a staff member.</p>';
		
		// show order deets
		
		$food_items = $db->force_multi_assoc('SELECT * FROM `'.$order_items.'` WHERE `order_id`=?', $order_info['order_id']);
		$food       = '';
		foreach ($food_items as $item)
		{
			$item_name = $db->result('SELECT `item_name` FROM `'.$menu_items.'` WHERE `item_id`=?', $item['item_id']);
			if ($item['qty'] > 0)
			{
				$food .= '<div class="food_item">'.$item_name.': x'.$item['qty'].'</div>';
			}
		}
		
		echo '<h3>Your Order</h3>';
		echo $food;
		
		$paid = ($order_info['pay_method'] == 'paypal') ? '<span class="green bold">Paid</span>' : '<span class="error bold">Not Paid</span>';
		echo '<p>Total: $'.number_format($order_info['total'], 2).' - '.$paid.'</p>';
		
	}
	
	return;
}

$item_list = array();

if ($_POST['page_action'] == 'order_food')
{
	if ($_POST['component_id'] != $component_id) { return; }
	
	$items = $db->force_multi_assoc('SELECT * FROM `'.$menu_items.'` WHERE `component_id`=? AND `display`=? ORDER BY `item_name` ASC', $component_id, 1);
	foreach ($items as $item)
	{
		$item_list[$item['item_id']] = $item;
	}
	
	$order = $_POST['order'];
	$total = 0;
	foreach ($order as $item_id => $quantity)
	{
		$ok_numbers = array(0,1,2,3,4,5,6,7,8,9,10);
		if ( (!isset($item_list[$item_id])) or (!in_array($quantity, $ok_numbers)) )
		{
			echo '<h1>Really?</h1><p>Will you stop fucking around with the post data? Seriously. Order definitely not placed... cheater.</p>';
			echo '<meta http-equiv="refresh" content="3;URL=\''.$_SERVER['REQUEST_URI'].'\'">';
			return;
		}
		
		$item_info = $item_list[$item_id];
		$subtotal  = $quantity * $item_info['price'];
		$total += $subtotal;
	}
	
	if ($total == 0)
	{
		echo '<p>You need to order something to continue</p>';
		return;
	}
	
	$deliver = (isset($_POST['deliver'])) ? $_POST['deliver'] : 0;
	$notes   = trim(stripslashes(strip_tags($_POST['notes'])));
	
	$order_id = $db->insert('INSERT INTO `'.$menu_orders.'` (`timestamp`, `user_id`, `component_id`, `total`, `deliver`, `notes`) VALUES (?,?,?,?,?,?)',
		time(), $user_id, $component_id, $total, $deliver, $notes
	);
	
	foreach ($order as $item_id => $quantity)
	{
		if ($quantity > 0)
		{
			$db->run('INSERT INTO `'.$order_items.'` (`item_id`, `qty`, `order_id`) VALUES (?,?,?)',
				$item_id, $quantity, $order_id
			);
		}
	}
	
	header('Location: '. $_SERVER['REQUEST_URI']);
	return;
}

// get all the items, show order form

$items = $db->force_multi_assoc('SELECT * FROM `'.$menu_items.'` WHERE `component_id`=? AND `display`=? ORDER BY `item_name` ASC', $component_id, 1);
if (is_array($items))
{
	$output  = '<table cellpadding="0" cellspacing="1" border="0" class="mod_register_user">';
	$output .= '<tr><th>Item</th><th>Price</th><th>Qty</th></tr>';
	$counter = 0;
	foreach ($items as $item)
	{
		$qty = '<select name="order['.$item['item_id'].']">';
		for ($y = 0; $y<=10; $y++)
		{
			$qty .= '<option value="'.$y.'" '.$selected.'>'.$y.'</option>';
		}
		$qty .= '</select>';
	
		$output .= '<tr>
			<td class="left">'.$item['item_name'].'</td>
			<td class="right">$'.number_format($item['price'],2).'</td>
			<td class="right">'.$qty.'</td></tr>';
	}
	$output .= '</table>';
	
	?>
	<form method="post" action="<?=$_SERVER['REQUEST_URI']?>" onsubmit="return confirm('Are you sure this order is correct?')">
		<input type="hidden" name="component_id" value="<?=$component_id?>" />
		<input type="hidden" name="page_action" value="order_food" />
		<?=$output?>
		
		<h3>Special Notes:</h3>
		<?=$settings['special_notes']?>
		<textarea style="width: 300px; height: 75px" name="notes"></textarea><br />
		<?php
		if ($settings['delivery_enabled'] == 1)
		{
			echo '<p><input type="checkbox" name="deliver" value="1" /> Deliver to your seat</p>';
		}
		?>
		
		
		<input type="submit" value="Place Order" />
	</form>
	<?php
}

?>
