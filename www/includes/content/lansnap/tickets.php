<?php

if ($_GET['reload'] == 1)
{
	chdir('../../../');
	require_once('core.php');
}

if ( (!defined('USER_ACCESS')) or (USER_ACCESS < 4) ) { exit(); }

require_once('includes/content/lansnap/functions.php');
$ticket_table = DB_PREFIX . 'lansnap_tickets';

$t_html = '';
$tickets = $db->force_multi_assoc('SELECT * FROM `'.$ticket_table.'` ORDER BY `name` ASC');

if (is_array($tickets))
{
	$counter = 0;
	foreach ($tickets as $ticket)
	{
		$class   = ($counter % 2 == 0) ? 'a': 'b'; $counter++;
		$price   = '$' . number_format($ticket['price'], 2);
		$delete  = '<img src="'.$body->url('includes/icons/delete.png').'" class="icon click" onclick="LS_DeleteTicket('.$ticket['ticket_id'].')" />';
		$t_html .= '<tr class="'.$class.'">
		<td>'.$ticket['name'].'</td>
		<td>'.$price.'</td>
		<td width="20">'.$delete.'</td>
		</tr>';
	}
}

?>
<div class="ap_overflow">
	<form method="post" action="<?=$body->url('includes/content/lansnap/submit.php')?>" onsubmit="LS_UpdateTickets(this); return false" />
		<input type="hidden" name="page_action" value="update_tickets" /> 
		<table border="0" cellpadding="2" cellspacing="1" class="admin_list">
			<tr>
				<th>Ticket Name</th>
				<th colspan="2">Price</th>
			</tr>
			<?=$t_html?>
			<tr>
				<td><input type="text" name="new_ticket[name]" /></td>
				<td colspan="2"><input type="text" name="new_ticket[price]" /> <input type="submit" value="Add" /></td>
			</tr>
		</table>
	</form>
</div>