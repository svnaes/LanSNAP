<?php
// get ticket type

unset($ticket_type);
global $params;

$host   = LS_GetSetting('externaldb_host');
$user   = LS_GetSetting('externaldb_user');
$pass   = LS_GetSetting('externaldb_pass');
$dbname = LS_GetSetting('externaldb_name');
$etable = LS_GetSetting('externaldb_table');
$column = LS_GetSetting('edb_linked_barcode');

$barcode_field = LS_GetSetting('edb_linked_barcode');

if (strlen($host) > 0)
{
	$external_db = new DataBase($host, $user, $pass, $dbname);
	if ($external_db->connected == FALSE)
	{
		echo 'Error with external database connection: ' .$external_db->error;
		return;
	}
}

if ($params[2] == 'find')
{
	$barcode = $params[3];
	
	$user_info = $external_db->assoc('SELECT * FROM `'.$etable.'` WHERE `'.$column.'`=?', $barcode);
	// basic info =====================================================
	
	$username      = $user_info[LS_GetSetting('edb_linked_username')];
	$first_name    = $user_info[LS_GetSetting('edb_linked_first_name')];
	$last_name     = $user_info[LS_GetSetting('edb_linked_last_name')];
	$email_address = $user_info[LS_GetSetting('edb_linked_email')];
	$seat_number   = $user_info[LS_GetSetting('edb_linked_seat')];
	$t_type        = $user_info[LS_GetSetting('edb_linked_ticket_type')];
	$user_password = $user_info[LS_GetSetting('edb_linked_password')];
	
	if (strlen($seat_number) == 0)
	{
		$url = $body->url(CURRENT_ALIAS . '/register');
		echo <<<HTML
<p>Please scan seat barcode:</p>
<form method="post" action="$url">
<input type="hidden" name="page_action" value="select_seat" />
<input type="hidden" name="user_barcode" value="$barcode" />
<input type="text" name="seat_barcode" id="seat_barcode" value="" /> <input type="submit" value="Continue" />
<script type="text/javascript">
var func = function() {
	document.getElementById('seat_barcode').focus();
}
add_load_event(func);
</script>
</form>
HTML;
		return;
	}
	
	$ip_group      = 2;
	
	// get user seat ==================================================
	list($row, $seat_num) = explode('-', $seat_number);
	
	$seat_number = $db->result('SELECT `seat_id` FROM `'.$seat_table.'` t1, `'.$table_table.'` t2 WHERE t1.table_id = t2.table_id and t1.seat_num=? AND t2.row=?',
		$seat_num, $row
	);
	
	if (!is_numeric($seat_number)) { $seat_number = 0; }
	// get user ticket type ===========================================
	
	$tickets = $db->force_multi_assoc('SELECT * FROM `'.$ticket_table.'` ORDER BY `name` ASC');
	if (is_array($tickets))
	{
		foreach ($tickets as $ticket)
		{
			$ticket_id = $ticket['ticket_id'];
			$key       = 'edb_linked_ticket_' . $ticket_id;
			$val       = LS_GetSetting($key);
			
			if ( ($val == $t_type) and (strlen($t_type) > 0) )
			{
				$ticket_type = $ticket_id;
				break;
			}
		}
	}
}

if ($_POST['page_action'] == 'find_user')
{
	$barcode = $_POST['barcode'];
	
	// make a connection to the external database,
	
	$column = LS_GetSetting('edb_linked_barcode');
	
	if (strlen($barcode) > 0)
	{
		// search by barcode
		
		$check = $external_db->result('SELECT count(1) FROM `'.$etable.'` WHERE `'.$column.'`=?', $barcode);
		if ($check == 1)
		{
			$redir_url = $body->url(CURRENT_ALIAS . '/register/find/'.$barcode);
			header('Location: ' . $redir_url);
			exit();
		}
	}
	else
	{
		// search by email
		$column = LS_GetSetting('edb_linked_email');
		$user_info = $external_db->force_multi_assoc('SELECT * FROM `'.$etable.'` WHERE `'.$column.'`=?', $_POST['email_address']);
		//echo $external_db->query;
		//return;
	}
	
	if (sizeof($user_info) == 1)
	{
		$column    = LS_GetSetting('edb_linked_barcode');
		$user_info = $user_info[0];
		$barcode   = $user_info[$column];
		
		// see if this user has a seat
		$seat_number = $user_info[LS_GetSetting('edb_linked_seat')];
		if (strlen($seat_number) > 0) // if they do, move them along
		{ 
			
			$redir_url = $body->url(CURRENT_ALIAS . '/register/find/'.$barcode);
			header('Location: ' . $redir_url);
			exit();
		}
		else // have them pick a seat here by scanning bag barcode
		{
			echo <<<HTML
<p>Please scan seat barcode:</p>
<form method="post" action="$_SERVER[REQUEST_URI]">
<input type="hidden" name="page_action" value="select_seat" />
<input type="hidden" name="user_barcode" value="$barcode" />
<input type="text" name="seat_barcode" id="seat_barcode" value="" /> <input type="submit" value="Continue" />
<script type="text/javascript">
var func = function() {
	document.getElementById('seat_barcode').focus();
}
add_load_event(func);
</script>
</form>
HTML;
			return;
		}
	}
	elseif (sizeof($user_info) > 0)
	{
		echo '<p>Please choose an entry before continuing</p>';
		// we have multiple results to show, stupid lanfest. have the mod pick one
		foreach ($user_info as $user)
		{
			// show link
			$email   = $user[LS_GetSetting('edb_linked_email')];
			$barcode = $user[LS_GetSetting('edb_linked_barcode')];
			$url     = $body->url(CURRENT_ALIAS . '/register/find/'.$barcode);
			
			echo '<div class="lookup_result"><a href="'.$url.'">'.$email.' ('.$barcode.') - Seat: '.$user[LS_GetSetting('edb_linked_seat')].'</a></div>';
		}
		
		return;
	}
	else
	{
		echo '<p class="error">No users found</p>';
	}
}
elseif ($_POST['page_action'] == 'select_seat')
{
	$user_barcode = $_POST['user_barcode'];
	$seat_barcode = $_POST['seat_barcode'];
	
	$user_info = $external_db->assoc('SELECT * FROM `'.$etable.'` WHERE `'.$barcode_field.'`=?', $user_barcode);
	$seat_info = $external_db->assoc('SELECT * FROM `'.$etable.'` WHERE `'.$barcode_field.'`=?', $seat_barcode);
	
	// basic info =====================================================
	
	$username      = $user_info[LS_GetSetting('edb_linked_username')];
	$first_name    = $user_info[LS_GetSetting('edb_linked_first_name')];
	$last_name     = $user_info[LS_GetSetting('edb_linked_last_name')];
	$email_address = $user_info[LS_GetSetting('edb_linked_email')];
	$seat_number   = $seat_info[LS_GetSetting('edb_linked_seat')];
	$t_type        = $user_info[LS_GetSetting('edb_linked_ticket_type')];
	$user_password = $seat_info[LS_GetSetting('edb_linked_password')];
	
	$ip_group      = 2;
	
	// get user seat ==================================================
	list($row, $seat_num) = explode('-', $seat_number);
	
	$seat_number = $db->result('SELECT `seat_id` FROM `'.$seat_table.'` t1, `'.$table_table.'` t2 WHERE t1.table_id = t2.table_id and t1.seat_num=? AND t2.row=?',
		$seat_num, $row
	);
	
	if (!is_numeric($seat_number)) { $seat_number = 0; }
	// get user ticket type ===========================================
	
	$tickets = $db->force_multi_assoc('SELECT * FROM `'.$ticket_table.'` ORDER BY `name` ASC');
	if (is_array($tickets))
	{
		foreach ($tickets as $ticket)
		{
			$ticket_id = $ticket['ticket_id'];
			$key       = 'edb_linked_ticket_' . $ticket_id;
			$val       = LS_GetSetting($key);
			
			if ( ($val == $t_type) and (strlen($t_type) > 0) )
			{
				$ticket_type = $ticket_id;
				break;
			}
		}
	}
}
elseif ($_POST['page_action'] == 'add_user')
{
	foreach ($_POST as $key=>$val)
	{
		$$key = trim(stripslashes($val));
	}
	
	unset($error); // in case
	
	if (strlen($username) < 1)
	{
		$error = 'Invalid Username';
	}
	elseif (strlen($first_name) < 1)
	{
		$error = 'Invalid First Name';
	}
	elseif (strlen($last_name) < 1)
	{
		$error = 'Invalid Last Name';
	}
	elseif (strlen($email_address) < 1)
	{
		$error = 'Invalid E-mail Address';
	}
	elseif (strlen($password) < 4)
	{
		$error = 'Invalid Password';
	}
	elseif (!is_numeric($ip_group))
	{
		$error = 'Missing IP Group';
	}
	elseif  (!is_numeric($ticket_type))
	{
		$error = 'Missing Ticket Type';
	}
	
	if (isset($error))
	{
		echo '<p class="error">'.$error.'</p>';
	}
	else
	{
		// add the user
		$id = $db->insert('INSERT INTO `'.$user_table.'` (`username`, `first_name`, `last_name`, `email_address`, `ip_group`, 
		`ticket_type`, `date_entered`, `seat_number`, `password`) VALUES (?,?,?,?,?,?,?,?,?)',
			$username,
			$first_name,
			$last_name,
			$email_address,
			$ip_group,
			$ticket_type,
			time(),
			$seat_number,
			md5($password)
		);

		//echo $db->query . '<br />'.$id.'<br />';
		//echo $db->error . '<br />';

		if (is_numeric($id))
		{

			$db->run('UPDATE `'.$seat_table.'` SET `user_id`=? WHERE `seat_id`=?', $id, $seat_number);
			//echo $db->query;
			echo '<p class="ok">Added '.$first_name.' successfully!</p>';
			echo '<meta http-equiv="refresh" content="3;url='.$body->url(CURRENT_ALIAS . '/register').'" />'; 
			return;
		}
		else
		{
			echo '<p class="error">Error adding '.$first_name.': '.$db->error.'</p>';
		}
	}
}

$t_html = '<select name="ticket_type"><option value="">Select...</option>';
$tickets = $db->force_multi_assoc('SELECT * FROM `'.$ticket_table.'` ORDER BY `name` ASC');

if (is_array($tickets))
{
	foreach ($tickets as $ticket)
	{
		$price   = '$' . number_format($ticket['price'], 2);
		
		$selected = ($ticket_type == $ticket['ticket_id']) ? 'selected="selected"' : '';
		
		$t_html .= '<option value="'.$ticket['ticket_id'].'" '.$selected.'>'.$ticket['name'].' - '.$price.'</option>';
	}
}
$t_html .= '</select>';

// get available seats
$seat_dropdown = LS_GetSeatsDropdown($component_id, $seat_number);
?>

<form method="post" action="<?=$_SERVER['REQUEST_URI']?>">
<input type="hidden" name="page_action" value="add_user" />
<table class="mod_register_user" cellpadding="0" cellspacing="0" border="0">
<tr>
	<td class="left">Username</td>
	<td class="right"><input type="text" name="username" value="<?=$username?>" autocomplete="off" /></td>
</tr>
<tr>
	<td class="left">First Name</td>
	<td class="right"><input type="text" name="first_name" value="<?=$first_name?>" autocomplete="off" /></td>
</tr>
<tr>
	<td class="left">Last Name</td>
	<td class="right"><input type="text" name="last_name" value="<?=$last_name?>" autocomplete="off" /></td>
</tr>
<tr>
	<td class="left">E-mail Address</td>
	<td class="right"><input type="text" name="email_address" value="<?=$email_address?>" autocomplete="off" /></td>
</tr>
<tr>
	<td class="left">Password</td>
	<td class="right"><input type="text" name="password" autocomplete="off" value="<?=$user_password?>" /></td>
</tr>
<tr>
	<td class="left">Seat Number</td>
	<td class="right"><?=$seat_dropdown?></td>
</tr>
<tr>
	<td class="left">IP Group</td>
	<td class="right">
		<select name="ip_group">
			<option value="">Select...</option>
			<option value="1" <?=($ip_group == 1) ? 'selected="selected"' : ''?>>Staff</option>
			<option value="2" <?=($ip_group == 2) ? 'selected="selected"' : ''?>>Other</option>
		</select>
	</td>
</tr>
<tr>
	<td class="left">Ticket Type</td>
	<td class="right">
		<?=$t_html?>
	</td>
</tr>
</table>

<input type="submit" value="Add User" />
</form>

<?php
if (!isset($external_db)) { return; }
?>

<h3>Find User</h3>

<?php
// get all users by email

$email_column = LS_GetSetting('edb_linked_email');
$user_column  = LS_GetSetting('edb_linked_username');
$emails = $external_db->force_multi_assoc('SELECT DISTINCT(`'.$email_column.'`) AS `email_address` FROM `'.$etable.'` ORDER BY `'.$email_column.'` ASC');
$ebox = '<select name="email_address"><option value="">';
if (is_array($emails))
{
	foreach ($emails as $email)
	{
		$address = $email['email_address'];
		$ebox .= '<option value="'.$address.'">'.$address.'</option>';
	}
}
$ebox .= '</select>';

$usernames = $external_db->force_multi_assoc('SELECT DISTINCT(`'.$user_column.'`) AS `username`, `'.$email_column.'` FROM `'.$etable.'` ORDER BY `'.$user_column.'` ASC');
$ubox = '<select name="usernames"><option value="">';
if (is_array($usernames))
{
	foreach ($usernames as $user)
	{
		$username = $user['username'];
		$email    = $user[$email_column];
		$ubox .= '<option value="'.$email.'">'.$username.'</option>';
	}
}
$ubox .= '</select>';
?>

<form method="post" action="<?=$body->url(CURRENT_ALIAS . '/register')?>">
<input type="hidden" name="page_action" value="find_user" />
Barcode: <input type="text" name="barcode" id="barcode" value="" /> OR E-mail Address: <?=$ebox?> OR Username: <?=$ubox?>
<br />

<input type="submit" value="Find User" />
</form>

<script type="text/javascript">
var func = function() {
	document.getElementById('barcode').focus();
}
add_load_event(func);
</script>
