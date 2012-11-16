<?php

$component_id = $_GET['component_id'];
$group_id     = $_GET['group_id'];

chdir('../../../');
require_once('core.php');

if ((!defined('USER_ACCESS')) or (USER_ACCESS < 3)) { exit(); }

$group_payment_settings = DB_PREFIX . 'user_group_payment_settings';

// get payment settings

$payment_configs = $db->force_multi_assoc('SELECT * FROM `'.$group_payment_settings.'` WHERE `component_id`=? AND `group_id`=? AND `display`=? ORDER BY `duration` ASC',
	$component_id, $group_id, 1
);

$output = '';

if (is_array($payment_configs))
{
	$output = '<table border="0" cellpadding="0" cellspacing="0" class="admin_list">';
	$output .= '<tr>
		<th>Description</th>
		<th>Length</th>
		<th>Cost</th>
		<th>Actions</th>
	</tr>';
	
	$counter = 0;
	foreach ($payment_configs as $config)
	{
		$class  = ($counter % 2 == 0) ? 'a' : 'b'; $counter++;
		$edit   = '<img src="'.$body->url('includes/icons/edit.png').'" title="Edit" class="icon click" onclick="US_PaymentSettings('.$component_id.', '.$group_id.', '.$config['entry_id'].')" />';
		$delete = '<img src="'.$body->url('includes/icons/delete.png').'" title="Delete" class="icon click" onclick="US_DeletePayment('.$component_id.', '.$group_id.', '.$config['entry_id'].')" />';
		
		$cost = '$' . number_format($config['cost'], 2);
		
		$output .= '<tr class="'.$class.'">
			<td>'.$config['description'].'</td>
			<td>'.$config['duration'].' days</td>
			<td>'.$cost.'</td>
			<td>'.$edit.$delete.'</td>
		</tr>';
	}
	
	$output .= '</table>';
}

if ($_GET['edit_id'] != 0)
{
	$action = 'edit_payment';
	$button = 'Edit';
	$extra  = '<input type="hidden" name="edit_id" value="'.$_GET['edit_id'].'" />';
	$info   = $db->assoc('SELECT * FROM `'.$group_payment_settings.'` WHERE `entry_id`=?', $_GET['edit_id']);
}
else
{
	$action = 'add_payment';
	$button = 'Add';
	$extra  = '';
	$info   = array();
}

?>
<div class="ap_overflow">
	<?php
	if ($_GET['edit_id'] == 0)
	{
		echo '<h3>Payment Settings</h3>' . $output . '<h3>Add Payment</h3>';
	}
	else
	{
		echo '<h3>Edit Payment</h3>';
	}
	?>
	<form method="post" action="<?=$body->url('includes/content/user_signup/submit.php')?>" onsubmit="US_AddPayment(this); return false" style="height: auto">
		<input type="hidden" name="page_action" value="<?=$action?>" />
		<input type="hidden" name="component_id" value="<?=$component_id?>" />
		<input type="hidden" name="group_id" value="<?=$group_id?>" />
		<?=$extra?>
		
		<table border="0" cellpadding="0" cellspacing="0" class="admin_list">
		<tr>
			<td>Description</td>
			<td><textarea name="description" class="ap_textarea"><?=$info['description']?></textarea></td>
		</tr>
		<tr>
			<td>Length (in days)<br />0 = unlimited</td>
			<td><input type="text" name="duration" value="<?=$info['duration']?>" /></td>
		</tr>
		<tr>
			<td>Cost</td>
			<td><input type="text" name="cost" value="<?=$info['cost']?>" /></td>
		</tr>
		</table>
		<input type="submit" value="<?=$button?>" />
	</form>
	
	<p class="click" onclick="US_ReloadMain(<?=$component_id?>)">[Back]</p>
</div>