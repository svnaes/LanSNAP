<?php
if (!defined('USER_ID')) { exit(); }

$transaction_table = DB_PREFIX . 'pico_payment_transactions';

if ($params[2] == 'details')
{
	$entry_id = $params[3];
	$transaction_info = $db->assoc('SELECT * FROM `'.$transaction_table.'` WHERE `entry_id`=?', $entry_id);
	
	if ($transaction_info['user_id'] != USER_ID)
	{
		return;
	}
	
	$component_info = $db->assoc('SELECT * FROM `'.DB_COMPONENT_TABLE.'` WHERE `component_id`=?', $transaction_info['component_id']);
	// see what kind of component this is
	
	if ($component_info['folder'] == 'shopping_cart')
	{
		$pico_trans_entry_id = $entry_id;
		$component_id = $transaction_info['component_id'];
		
		include('includes/content/shopping_cart/transaction_details.php');
		
		return;
	}
}

$all_transactions = $db->force_multi_assoc('SELECT * FROM `'.$transaction_table.'` WHERE `user_id`=? ORDER BY `timestamp` DESC', USER_ID);
if (!is_array($all_transactions))
{
	echo '<p class="no_transactions">You currently have no transactions to display</p>';
	return;
}

echo '<table border="0" cellpadding="0" cellspacing="0" class="user_transaction_history">
<tr>
	<th>Date</th>
	<th>Transaction ID</th>
	<th>Amount</th>
	<th colspan="2">Type</th>
</tr>';

$counter = 0;

foreach ($all_transactions as $transaction)
{
	$amt = '$' . number_format($transaction['amount_net'], 2);
	
	$component_info = $db->assoc('SELECT * FROM `'.DB_COMPONENT_TABLE.'` WHERE `component_id`=?', $transaction['component_id']);
	$details = '<a href="'.$body->url(CURRENT_ALIAS . '/transaction-history/details/'.$transaction['entry_id']).'">Details</a>';
	
	$class = ($counter % 2 == 0) ? 'a' : 'b'; $counter++;
	
	echo '<tr class="'.$class.'">
	<td>'.date('h:ia m/d/Y', $transaction['timestamp']).'</td>
	<td>'.$transaction['transaction_id'].'</td>
	<td>'.$amt.'</td>
	<td>'.$component_info['description'].'</td>
	<td>'.$details.'</td>
</tr>';
}

echo '</table>';

?>