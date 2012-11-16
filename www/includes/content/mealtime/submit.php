<?php
chdir('../../../');
require_once('core.php');

if ( (!defined('USER_ACCESS')) or (USER_ACCESS < 3) ) { exit(); }

$action = $_REQUEST['page_action'];

if ($action == 'update_meal')
{
	$meal_choices = $_POST['meal_choices'];
	$instance_id = $_POST['instance_id'];
	$save = array();
	
	// santize
	$counter = 0;
	foreach ($meal_choices as $key=>$val)
	{
		$option = trim(stripslashes($val['option']));
		$price = trim(stripslashes($val['price']));
		
		if (strlen($option) > 0)
		{
			$save[$counter]['option'] = $option;
			$save[$counter]['price'] = $price;
			$counter++;
		}
	}
	print_r($meal_choices);
	print_r($save);
	$result = $db->result('UPDATE `'.DB_CONTENT.'` SET `additional_info`=? WHERE `instance_id`=?', serialize($save), $instance_id);
}
?>