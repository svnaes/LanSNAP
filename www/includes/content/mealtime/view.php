<?php

$additional_info = $db->result('SELECT `additional_info` FROM `'.DB_CONTENT.'` WHERE `instance_id`=?', $instance_id);
$meal_choices    = unserialize($additional_info);
if (!is_array($meal_choices)) { return; }

$ok_mealchoices = array();
$meal_prices    = array();
foreach ($meal_choices as $choice)
{
	$option               = $choice['option'];
	$ok_mealchoices[]     = $option;
	$meal_prices[$option] = $choice['price'];
}

$meal_table = DB_PREFIX . 'meal_table';

require_once('includes/content/lansnap/functions.php');
$user_id = LS_GetUserIDByIP($_SERVER['HTTP_X_FORWARDED_FOR']);

if ($_POST['page_action'] == 'update_order')
{
	// verify instance id
	if ($_POST['instance_id'] != $instance_id) { return; }

	$quantity = $_POST['quantity'];
	$save = array();
	foreach ($quantity as $item_name => $item_number)
	{
		// verify item name
		$ok_numbers = array(0,1,2,3,4,5,6,7,8,9,10);
		if ( (!in_array($item_name, $ok_mealchoices)) or (!in_array($item_number, $ok_numbers)) or ($item_number < 0) or ($item_number > 10) )
		{
			echo '<h1>Really?</h1><p>Will you stop fucking around with the post data? Seriously.</p>';
			echo '<meta http-equiv="refresh" content="3;URL=\''.$_SERVER['REQUEST_URI'].'\'">';
			return;
		}
		$save[$item_name] = $item_number;
	}
	
	// if they make it this far, save their order info
	
	$check = $db->result('SELECT count(1) FROM `'.$meal_table.'` WHERE `user_id`=? AND `instance_id`=?',
		$user_id, $instance_id
	);
	
	if ($check == 0)
	{
		// insert
		$db->run('INSERT INTO `'.$meal_table.'` (`user_id`, `instance_id`, `food`) VALUES (?,?,?)',
			$user_id, $instance_id, serialize($save)
		);
	}
	else
	{
		// update
		$db->run('UPDATE `'.$meal_table.'` SET `food`=? WHERE `user_id`=? AND `instance_id`=?',
			serialize($save), $user_id, $instance_id
		);
	}
	
	echo '<p>Thank you for ordering... you are being redirected.</p>';
	echo '<meta http-equiv="refresh" content="3;URL=\''.$_SERVER['REQUEST_URI'].'\'">';
	return;
}

$user_qtys = $db->assoc('SELECT * FROM `'.$meal_table.'` WHERE `user_id`=? AND `instance_id`=?', $user_id, $instance_id);
if (!is_array($user_qtys)) { $user_qtys = array(); }
$food = $user_qtys['food'];
$food = unserialize($food);
if (!is_array($food)) { $food = array(); }

// show a form!

$output = '<table border="0" cellpadding="0" cellspacing="1" class="mod_register_user">';
$output .= '<tr><th>Item</th><th>Price</th><th>Quantity</th></tr>';

for ($x=0; $x<sizeof($meal_choices); $x++)
{
	$option = $meal_choices[$x]['option'];
	$price  = '$' . number_format($meal_choices[$x]['price'], 2);
	
	$qty = '<select name="quantity['.$option.']">';
	for ($y = 0; $y<=10; $y++)
	{
		$selected = ($food[$option] == $y) ? 'selected="selected"' : '';
		$qty .= '<option value="'.$y.'" '.$selected.'>'.$y.'</option>';
	}
	$qty .= '</select>';
	
	$class = ($x % 2 == 0) ? 'a' : 'b';
	
	$output.= <<<HTML
<tr class="$class">
	<td class="left">$option</td>
	<td class="right">$price</td>
	<td class="right">$qty</td>
</tr>
HTML;
}

$output .= '</table>';

?>
<form method="post" action="<?=$_SERVER['REQUEST_URI']?>">
<input type="hidden" name="page_action" value="update_order" />
<input type="hidden" name="instance_id" value="<?=$instance_id?>" />
<?=$output?>
<input type="submit" value="Save" />
</form>

<?php
if (USER_ACCESS > 3)
{
	// need to show totals
	
	$totals = array();
	foreach ($ok_mealchoices as $choice)
	{
		$totals[$choice] = 0;
	}
	
	// get everyone's orders
	
	$orders = $db->force_multi_assoc('SELECT * FROM `'.$meal_table.'` WHERE `instance_id`=?', $instance_id);
	if (is_array($orders))
	{
		foreach ($orders as $order)
		{
			$food = unserialize($order['food']);
			if (!is_array($food)) { $food = array(); }

			$counter = 0;
			foreach ($ok_mealchoices as $choice)
			{
				$qty = $food[$choice];
				if (!is_numeric($qty)) { $qty = 0; }
					$totals[$choice] += $qty;
				if (($counter == 0) and ($qty != 1))
				{
					require_once('includes/content/lansnap/functions.php');
					echo LS_GetUserNameByID($order['user_id']) .'<br />'; 
				}
				$counter++;
			}
		}
	}
	
	$output = '<h3>Totals</h3>';
	$output .= '<table border="0" cellpadding="0" cellspacing="1" class="mod_register_user">';
	$output .= '<tr><th>Item</th><th>Price</th><th>Quantity</th><th>Total</th></tr>';

	$running_total = 0;
	// display totals
	foreach ($totals as $choice=>$qty)
	{
		$price = $meal_prices[$choice];
		$total = $price * $qty;
		
		$running_total += $total;
		
		$output .= '<tr>
			<td class="left">'.$choice.'</td> 
			<td class="right">$'.number_format($price, 2).'</td> 
			<td class="right">'.$qty.'</td> 
			<td class="right">$'.number_format($total, 2).'</td> 
		</tr>';
	}
	
	$output .= '<tr>
		<td class=""></td> 
		<td class=""></td> 
		<td class=""></td> 
		<td class="bold">$'.number_format($running_total, 2).'</td> 
	</tr>';
	
	$output .= '</table>';
	
	echo $output;
}
?>
