<?php
global $params;

if ($params[1] == 'register')
{
	echo '<h2>Register User</h2>';
	include('includes/content/lansnap/mod_register_user.php');
	return;
}
elseif ($params[1] == 'users')
{
	echo '<h2>Users</h2>';
	include('includes/content/lansnap/mod_userlist.php');
	return;
}
elseif ($params[1] == 'provision')
{
	echo '<h2>Manual Provision</h2>';
	include('includes/content/lansnap/mod_provision.php');
	return;
}
elseif ($params[1] == 'configure-seats')
{
	echo '<h2>Configure Seats</h2>';
	include('includes/content/lansnap/mod_seats.php');
	return;
}
elseif ($params[1] == 'configure-seats-pre')
{
	        echo '<h2>Configure Seats</h2>';
		include('includes/content/lansnap/mod_seats_pre.php');
		return;
}
elseif ($params[1] == 'seating-chart')
{
	echo '<h2>Seating Chart</h2>';
	include('includes/content/lansnap/mod_seatingchart.php');
	return;
}
elseif ($params[1] == 'bandwidth')
{
	//echo '<h2>Bandwidth Data</h2>';
	//include('includes/content/lansnap/mod_bandwidth.php');
}
elseif ($params[1] == 'survey')
{
	echo '<h2>Survey Results</h2>';
	include('includes/content/lansnap/mod_survey.php');
}
elseif ($params[1] == 'delete-user')
{
	if (USER_ACCESS == 5)
	{
		LS_DeleteUser($params[2]);
	}
	header('Location: ' . $body->url(CURRENT_ALIAS . '/users'));
}
else
{
	// show menu
	
	$seat_url = $body->url('includes/content/lansnap/seating.php?component_id='.$component_id);
	$seat_url2 = $body->url('includes/content/lansnap/seating.php?mode=bags&component_id='.$component_id);
	
	echo '<ul class="mod_menu">
		<li><a href="'.$body->url(CURRENT_ALIAS . '/register').'">Register User</a></li>
		<li>Show Participants: <a href="'.$body->url(CURRENT_ALIAS . '/users').'">List</a> - <a href="'.$body->url(CURRENT_ALIAS . '/seating-chart').'">Chart</a></a></li>
		<li><a href="'.$body->url(CURRENT_ALIAS . '/provision').'">Manual Provisioning</a></li>
		<li><a href="'.$body->url(CURRENT_ALIAS . '/survey').'">Survey Results</a></li>
		<!--li><a href="'.$body->url(CURRENT_ALIAS . '/bandwidth').'">Bandwidth Usage</a></li-->
		<li>Labels: <a href="'.$seat_url.'">Seats</a> / <a href="'.$seat_url2.'">Bags</a></li>
		
		';
	if (USER_ACCESS > 3)
	{
		echo '<li><a href="'.$body->url(CURRENT_ALIAS . '/configure-seats').'">Seat Configuration</a></li>';
		echo '<li><a href="'.$body->url(CURRENT_ALIAS . '/configure-seats-pre').'">Seat Configuration (Pre Reg)</a></li>';
	}
	
	echo '</ul>';
}
?>
