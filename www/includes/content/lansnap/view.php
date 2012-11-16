<?php

$settings_table = DB_PREFIX . 'lansnap_settings';
$ticket_table   = DB_PREFIX . 'lansnap_tickets';
$user_table     = DB_PREFIX . 'lansnap_users';
$ip_table       = DB_PREFIX . 'lansnap_addresses';
$domain_table   = DB_PREFIX . 'lansnap_domains';

$layout_table  = DB_PREFIX . 'lansnap_layouts';
$table_table   = DB_PREFIX . 'lansnap_layout_tables';
$seat_table    = DB_PREFIX . 'lansnap_layout_seats';
$traffic_table = DB_PREFIX . 'lansnap_traffic';

$survey_questions = DB_PREFIX . 'lansnap_survey';
$survey_results   = DB_PREFIX . 'lansnap_survey_results';

require_once('includes/content/lansnap/functions.php');

if (USER_ACCESS > 1)
{
	echo '<h1>LANsnap</h1>';
	include('includes/content/lansnap/mod_register.php');
	echo '<p><a href="'.$body->url(CURRENT_ALIAS).'">Admin Home</a></p>';
}
else
{
	$ip =  getenv('REMOTE_ADDR');
	if (substr($ip, 0, 3) == '172')
	{
		global $params;
		if ($params[1] == 'thank-you')
		{
			echo LS_GetSetting('user_complete_registration');
			return;
		}
		else
		{
			include('includes/content/lansnap/user_register.php');
		}
	}
	return;
}
?>