<?php

if ($_GET['reload'] == 1)
{
	$instance_id = $_GET['instance_id'];
	$component_id = $_GET['component_id'];
	chdir('../../../');
	require_once('core.php');
}

if ( (!defined('USER_ACCESS')) or (USER_ACCESS < 3) ) { exit(); }

$additional_info = $db->result('SELECT `additional_info` FROM `'.DB_CONTENT.'` WHERE `instance_id`=?', $instance_id);
$meal_choices    = unserialize($additional_info);
if (!is_array($meal_choices)) { $meal_choices = array(); }

$meal_table = DB_PREFIX . 'meal_table';

$db->run(<<<SQL
CREATE TABLE IF NOT EXISTS `$meal_table` (
	`instance_id` VARCHAR(32) NOT NULL,
	`user_id` BIGINT(11),
	`food` BLOB
)
SQL
);

$output = '<table border="0" cellpadding="0" cellspacing="1" class="admin_list">';
$output .= '<tr><th>Option</th><th>Price</th></tr>';

for ($x=0; $x<sizeof($meal_choices); $x++)
{
	$option = $meal_choices[$x]['option'];
	$price  = $meal_choices[$x]['price'];
	
	$class = ($x % 2 == 0) ? 'a' : 'b';
	
	$output.= <<<HTML
<tr class="$class">
	<td><input type="text" name="meal_choices[$x][option]" value="$option" /></td>
	<td><input type="text" name="meal_choices[$x][price]" value="$price" /></td>
</tr>
HTML;
}

$class = ($x % 2 == 0) ? 'a' : 'b';

$output.= <<<HTML
<tr class="$class">
	<td><input type="text" name="meal_choices[$x][option]" value="" /></td>
	<td><input type="text" name="meal_choices[$x][price]" value="" /></td>
</tr>
</table>
HTML;


?>
<div class="ap_overflow">
	<h3>Meal Choices</h3>
	<form method="post" action="<?=$body->url('includes/content/mealtime/submit.php')?>" onsubmit="MT_Submit(this); return false">
	<input type="hidden" name="component_id" value="<?=$component_id?>" />
	<input type="hidden" name="instance_id" value="<?=$instance_id?>" />
	<input type="hidden" name="page_action" value="update_meal" />
	<?=$output?>
	<input type="submit" value="Update" />
	</form>
</div>