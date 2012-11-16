<?php
chdir('../../../');
require_once('core.php');

if ( (!defined('USER_ACCESS')) or (USER_ACCESS < 3) ) { exit(); }

$ticket_table = DB_PREFIX . 'lansnap_tickets';
$component_id = $_GET['component_id'];
require_once('includes/content/lansnap/functions.php');

// test to see if there is a connection

echo '<div class="ap_overflow">';

$host = LS_GetSetting('externaldb_host');
if (strlen($host) > 0)
{
	// test it
	$user   = LS_GetSetting('externaldb_user');
	$pass   = LS_GetSetting('externaldb_pass');
	$dbname = LS_GetSetting('externaldb_name');
	$table  = LS_GetSetting('externaldb_table');
	
	$box1_val = LS_GetSetting('edb_linked_username');
	$box2_val = LS_GetSetting('edb_linked_email');
	$box3_val = LS_GetSetting('edb_linked_barcode');
	$box4_val = LS_GetSetting('edb_linked_seat');
	$box5_val = LS_GetSetting('edb_linked_first_name');
	$box6_val = LS_GetSetting('edb_linked_last_name');
	$box7_val = LS_GetSetting('edb_linked_ticket_type');
	$box8_val = LS_GetSetting('edb_linked_password');
	
	$external_db = new DataBase($host, $user, $pass, $dbname);
	
	if ($external_db->connected == FALSE)
	{
		echo $external_db->error;
	}
	else
	{
		
		?>
<h3>Linked Database Config</h3>
		<?php
		
		$columns = $external_db->force_multi_assoc('SHOW COLUMNS FROM `'.$table.'`');
		if (is_array($columns))
		{
			// table is formed and exists
			$box1 = LS_LinkedSettingDropdown($columns, $box1_val, 'settings[edb_linked_username]');
			$box2 = LS_LinkedSettingDropdown($columns, $box2_val, 'settings[edb_linked_email]');
			$box3 = LS_LinkedSettingDropdown($columns, $box3_val, 'settings[edb_linked_barcode]');
			$box4 = LS_LinkedSettingDropdown($columns, $box4_val, 'settings[edb_linked_seat]');
			$box5 = LS_LinkedSettingDropdown($columns, $box5_val, 'settings[edb_linked_first_name]');
			$box6 = LS_LinkedSettingDropdown($columns, $box6_val, 'settings[edb_linked_last_name]');
			$box7 = LS_LinkedSettingDropdown($columns, $box7_val, 'settings[edb_linked_ticket_type]');
			$box8 = LS_LinkedSettingDropdown($columns, $box8_val, 'settings[edb_linked_password]');
			
			$form_url = $body->url('includes/content/lansnap/submit.php');
			
			echo <<<HTML
<form method="post" action="$form_url" onsubmit="LS_UpdateSettings(this); return false" style="height: auto" />
	<input type="hidden" name="page_action" value="update_settings" /> 
	<table border="0" cellpadding="2" cellspacing="1" class="admin_list">
		<tr class="a">
			<td>Username</td>
			<td>$box1</td>
		</tr>
		<tr class="b">
			<td>E-mail Address</td>
			<td>$box2</td>
		</tr>
		<tr class="a">
			<td>Barcode</td>
			<td>$box3</td>
		</tr>
		<tr class="b">
			<td>Seat</td>
			<td>$box4</td>
		</tr>
		<tr class="a">
			<td>First Name</td>
			<td>$box5</td>
		</tr>
		<tr class="b">
			<td>Last Name</td>
			<td>$box6</td>
		</tr>
		<tr class="a">
			<td>Ticket Type</td>
			<td>$box7</td>
		</tr>
		<tr class="b">
			<td>Password</td>
			<td>$box8</td>
		</tr>
	
HTML;
			
			if (strlen($box7_val) > 0)
			{
				// ticket type config.
				
				// get all ticket types in lansnap
				$tickets = $db->force_multi_assoc('SELECT * FROM `'.$ticket_table.'` ORDER BY `name` ASC');
				$remote_tickets = $external_db->force_multi_assoc('SELECT DISTINCT(`'.$box7_val.'`) AS `ticket_type` FROM `'.$table.'`');
				
				echo '<tr><td colspan="2" class="bold">Ticket Linkage</td></tr>';
				
				if (is_array($tickets))
				{
					$counter = 1;
					foreach ($tickets as $ticket)
					{
						$class     = ($counter % 2 == 0) ? 'a' : 'b'; $counter++;
						$ticket_id = $ticket['ticket_id'];
						$key       = 'edb_linked_ticket_' . $ticket_id;
						$val       = LS_GetSetting($key);
						
						echo '<tr class="'.$class.'">';
						echo '<td>'.$ticket['name'].'</td>';
						echo '<td>'.LS_LinkedTicketsDropdown($remote_tickets, $val, "settings[$key]").'</td>';
						echo '</tr>';
					}
				}
				
				// get all ticket types in remote config
				
				// make form
			}
			
			echo <<<HTML
	</table>
	<input type="submit" value="Update" />
</form>
HTML;
			
			
			
			echo '<p class="bold">Import Lanfest Signup CSV</p>';
			$upload_path = $body->url('includes/content/lansnap/import.php');
			$uploader = new Uploader($upload_path, 'LS_ImportLanfestCSV', '', '.csv', 'CSV File', '000000', 'ffffff');
			echo $uploader->Output();
		}
		else
		{
			echo '<p class="error">Unable to locate table: '.$table.'</p>';
		}
	}
}

?>
<h3>Database Settings</h3>
<p>Configure the database that LANsnap will communicate with to pull registrant information</p>
<form method="post" action="<?=$body->url('includes/content/lansnap/submit.php')?>" onsubmit="LS_UpdateSettings(this); return false" style="height: auto" />
	<input type="hidden" name="page_action" value="update_settings" /> 
	<table border="0" cellpadding="2" cellspacing="1" class="admin_list">
		<tr class="a">
			<td>External DB Host</td>
			<td><input type="text" name="settings[externaldb_host]" value="<?=LS_GetSetting('externaldb_host')?>" /></td>
		</tr>
		<tr class="b">
			<td>External DB Name</td>
			<td><input type="text" name="settings[externaldb_name]" value="<?=LS_GetSetting('externaldb_name')?>" /></td>
		</tr>
		<tr class="a">
			<td>External DB Username</td>
			<td><input type="text" name="settings[externaldb_user]" value="<?=LS_GetSetting('externaldb_user')?>" /></td>
		</tr>
		<tr class="b">
			<td>External DB Password</td>
			<td><input type="text" name="settings[externaldb_pass]" value="<?=LS_GetSetting('externaldb_pass')?>" /></td>
		</tr>
		<tr class="a">
			<td>External DB Table</td>
			<td><input type="text" name="settings[externaldb_table]" value="<?=LS_GetSetting('externaldb_table')?>" /></td>
		</tr>
	</table>
	<input type="submit" value="Update" />
</form>
<p class="click" onclick="LS_EditHome(<?=$component_id?>)">[back]</p>
</div>