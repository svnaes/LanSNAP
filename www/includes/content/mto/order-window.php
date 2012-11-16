<?php
chdir('../../../');
require_once('core.php');
require_once('includes/content/lansnap/functions.php');

if (USER_ACCESS < 2) { exit(); }

$menu_items   = DB_PREFIX . 'menu_items';
$menu_orders  = DB_PREFIX . 'menu_orders';
$order_items  = DB_PREFIX . 'menu_order_items';
$ls_user_info = DB_PREFIX . 'lansnap_users';

// see how many orders there are
$component_id = $_GET['component_id'];

$open_orders = $db->force_multi_assoc('SELECT * FROM `'.$menu_orders.'` WHERE `component_id`=? AND `status`=? ORDER BY `timestamp` ASC', $component_id, 1);
$num_orders  = (is_array($open_orders)) ? sizeof($open_orders) : 0;

$key = 'num_open_orders_'.$component_id;

if ( ($_SESSION[$key] < $num_orders) or (!isset($_SESSION[$key])) )
{
	// play sound file
	echo '<audio src="'.$body->url('includes/content/mto/order.wav').'" autoplay></audio>';
}

$_SESSION[$key] = $num_orders;

if (is_array($open_orders))
{
	$output  = '<table border="0" cellpadding="0" cellspacing="1" class="order_list_table">';
	$output .= '<tr>
		<th>Username</th>
		<th>Total</th>
		<th>Deliver?</th>
		<th>Seat Number</th>
		<th>Paid?</th>
		<th>Food</th>
		<th>Note</th>
		<th>Actions</th>
	</tr>';
	foreach ($open_orders as $order)
	{
		$username    = LS_GetUserNameByID($order['user_id']);
		$deliver     = ($order['deliver'] == 1) ? 'Yes' : 'No';
		$user_info   = $db->assoc('SELECT * FROM `'.$ls_user_info.'` WHERE `user_id`=?', $order['user_id']);
		$seat_number = LS_GetReadableSeatNumber($user_info['seat_number']);
		$paid        = ($order['pay_method'] == 'paypal') ? 'Yes' : '<span class="error">No</span>';
		
		$close  = '<button onclick="MTO_CloseOrder('.$order['order_id'].')">Close Order</button>';
		$cancel = '<button onclick="MTO_CancelOrder('.$order['order_id'].')">Cancel Order</button>';
		
		// get food items
		$food_items = $db->force_multi_assoc('SELECT * FROM `'.$order_items.'` WHERE `order_id`=?', $order['order_id']);
		$food       = '';
		foreach ($food_items as $item)
		{
			$item_name = $db->result('SELECT `item_name` FROM `'.$menu_items.'` WHERE `item_id`=?', $item['item_id']);
			if ($item['qty'] > 0)
			{
				$food .= '<div class="food_item">'.$item_name.': x'.$item['qty'].'</div>';
			}
		}
		
		$output .= '<tr>
			<td class="left">'.$username.'</div>
			<td class="right">$'.number_format($order['total'], 2).'</div>
			<td class="right">'.$deliver.'</div>
			<td class="right">'.$seat_number.'</div>
			<td class="right">'.$paid.'</div>
			<td class="right">'.$food.'</div>
			<td class="right">'.$order['notes'].'</div>
			<td class="right">'.$close.$cancel.'</div>
		</tr>';
	}
	
	$output .= '</table>';
	echo $output;
}
else
{
	echo 'No orders found';
}

// find total order amount

$total_money = $db->result('SELECT sum(`total`) FROM `'.$menu_orders.'` WHERE `component_id`=? AND `status`=?', $component_id, 2);
echo '<p>Total Income: $'.number_format($total_money, 2).'</p>';

?>
