<?php
// config for external newsletter application portal so that we get the right fields

$fields = array(
	'mc' => array(
		'api_key' => array(
			'name' => 'API Key', 
			'desc' => 'API Key provided by MailChimp.'
		),
	),
	'ic' => array(
		'api_key' => array(
			'name' => 'Application ID', 
			'desc' => 'Pico app id for iContact: <a href="http://app.icontact.com/icp/login/index.php?error=0&relurl=http://app.sandbox.icontact.com/icp/core/externallogin" target="_blank">P7a4OAORPexnHqXImg6CU9rcIMmiNGhs</a>.'
		),
		'api_username' => array(
			'name' => 'API Username', 
			'desc' => 'Your iContact Username'
		),
		'api_password' => array(
			'name' => 'API Password', 
			'desc' => 'Your iContact application password, this is NOT your iContact account password'
		),
	),
	'cc' => array(
		'api_username' => array(
			'name' => 'API Username', 
			'desc' => 'Your Constant Contact username'
		),
		'api_password' => array(
			'name' => 'API Username', 
			'desc' => 'Your Constant Contact password'
		),
	),
);

?>