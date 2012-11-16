<?php
if (USER_ACCESS == 0) { return; }

$ai = $db->result('SELECT `additional_info` FROM `'.DB_CONTENT.'` WHERE `component_id`=?', $component_id);
$ai = unserialize($ai);
if (!is_array($ai)) { $ai = array(); }

if (!is_numeric($ai['source'])) { echo 'Component needs to be configured before using'; return; }
require_once('includes/content/paid_users/functions.php');
$pu_table    = DB_PREFIX . 'user_signups';

$user_config = $db->assoc('SELECT * FROM `'.$pu_table.'` WHERE `component_id`=?', $ai['source']);
$fields   = $user_config['additional_fields'];
$fields   = unserialize($fields);
if (!is_array($fields)) { $fields = array(); }

// set up the fields again

$field = array();
$field['name'] = 'E-mail Address';
$field['required'] = 'required';
$field['type'] = 'text';
$field['pattern'] = 'email';
array_unshift($fields, $field);

$field = array();
$field['name'] = 'Last Name';
$field['required'] = 'required';
$field['type'] = 'text';
array_unshift($fields, $field);

$field = array();
$field['name'] = 'First Name';
$field['required'] = 'required';
$field['type'] = 'text';
array_unshift($fields, $field);

$user_data = $db->assoc('SELECT * FROM `'.DB_USER_TABLE.'` WHERE `id`=?', USER_ID);
$data = unserialize($user_data['additional_data']);
if (!is_array($data)) { $data = array(); }

if (sizeof($data) > 0)
{
	$counter = 0;
	foreach ($data as $key=>$val)
	{
		$data[$counter] = $val;
		$counter++;
	}
}

array_unshift($data, $user_data['email_address']);
array_unshift($data, $user_data['last_name']);
array_unshift($data, $user_data['first_name']);

if ($_POST['submit_action'] == 'post_form')
{
	$data = $_POST['fields'];
	
	// verify the image...
	$verify = FieldsFormVerify($fields, $data);
	$success = $verify['success'];
	$results = $verify['results'];
	
	if ($success)
	{
		$save = $results;
		
		$first_name    = array_shift($save);
		$last_name     = array_shift($save);
		$email_address = array_shift($save);
		
		$db->run('UPDATE `'.DB_USER_TABLE.'` SET `first_name`=?, `last_name`=?, `email_address`=?, `additional_data`=? WHERE `id`=?',
			$first_name, $last_name, $email_address, serialize($save), USER_ID
		);
		
		echo '<h3>Updated</h3><div class="ok">Your information has been updated</div></h3>';
		return;
	}
	else
	{
		$error = $results;
		echo '<h3>Error</h3><div class="error">'.$error.'</div>';
	}
}

$html_form = FieldsCreateForm($fields, $data);
echo $html_form;

?>