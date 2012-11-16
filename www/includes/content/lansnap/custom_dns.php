<?php
// get users DNS

require_once('includes/content/lansnap/functions.php');

$ip      = getenv($_SERVER['HTTP_X_FORWARDED_FOR']);
$user_id = LS_GetUserIDByIP($ip);

if ($user_id == 0) { echo 'You cannot use this, sorry.'; return; }

// get user IP info

$ip_table = DB_PREFIX . 'lansnap_addresses';
$ip_info  = $db->assoc('SELECT * FROM `'.$ip_table.'` WHERE `user_id`=? AND `source`=?', $user_id, 0);
$host     = $ip_info['hostname'];

if (strlen($_POST['ls_hostname']) > 0)
{
	// check host name
	$hostname = strtolower(trim(stripslashes(strip_tags($_POST['ls_hostname']))));
	$ok_chars = 'abcdefghijklmnopqrstuvwxyz1234567890';
	$good     = TRUE;
	
	for ($x = 0; $x < strlen($hostname); $x++)
	{
		$char = substr($hostname, $x, 1);
		if (strstr($ok_chars, $char) === FALSE)
		{
			$good = FALSE;
			//echo "BAD: $char<br />";
			break;
		}
	}
	
	if ( ($good == FALSE) or (strlen($hostname) <= 0) or (strlen($hostname) > 20) )
	{
		echo '<p class="error">Invalid DNS Host name</p>';
	}
	else
	{
		// see if this hostname exists to someone else
		
		$check = $db->result('SELECT count(1) FROM `'.$ip_table.'` WHERE `hostname`=?', $hostname);
		if ($check > 0)
		{
			echo '<p class="error">That DNS host name is in use</p>';
		}
		else
		{
			$host = $hostname; // for later
			$db->run('UPDATE `'.$ip_table.'` SET `hostname`=? WHERE `user_id`=? AND `source`=?', $hostname, $user_id, 0);
			$ip_address = $ip_info['ip_address'];
			$domain = $hostname . '.' . LS_GetSetting('domain_tld') . '.lan';
			
			// TODO: configure this
			// see if this user exists
			$dns_db = new DataBase('localhost', 'root', 'jl@0p13!', 'pdns');
			$check  = $dns_db->result('SELECT count(1) FROM `records` WHERE `content`=?', $ip_address);
			if ($check > 0)
			{
				// update
				$dns_db->run('UPDATE `records` SET `name`=? WHERE `content`=?',
					$domain, $ip_address
				);
			}
			else
			{
				// insert
				$dns_db->run('INSERT INTO `records` (`domain_id`, `name`, `content`, `type`, `ttl` , `prio` , `auth`) VALUES (?,?,?,?,?,?,?)',
					1, $domain, $ip_address, 'A', 120, 'NULL', 1
				);
			}
			
			echo '<p>DNS Host name Updated</p>';
			return;
		}
	}
}

if (strlen($host) > 0)
{
	echo '<p>Your current DNS hostname is: <b>'.$host.'.'.LS_GetSetting('domain_tld').'.lan</b></p>';
}

?>

<form method="post" action="<?=$_SERVER['REQUEST_URI']?>">
<input type="text" name="ls_hostname" value="<?=$host?>" />.<?=LS_GetSetting('domain_tld')?>.lan <input type="submit" value="Update" />
</form>
