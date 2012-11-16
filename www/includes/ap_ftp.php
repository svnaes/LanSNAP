<?php
chdir('../');
require_once('core.php');
if (USER_ACCESS < 4) { echo 'You do not have access to this. Please contact your site administrator'; exit(); }

$settings_table = DB_PREFIX . 'pico_settings';

// make sure settings are there for older versions

$db->run(<<<SQL
CREATE TABLE IF NOT EXISTS `$settings_table` (
	`keyfield` varchar(32) NOT NULL,
	`keyvalue` varchar(255)
);
SQL
);

if (isset($_POST['page_action']))
{
	$action = $_POST['page_action'];
	if ($action == 'update_ftp')
	{
		include('includes/ftp/ftp_class.php');
		$ftp_error = array();
		$ftp_settings = $_POST['ftp'];
		
		ob_start(); // surpress normal ftp class output
		$ftp = new ftp(TRUE);
		$ftp->Verbose = FALSE;
		$ftp->LocalEcho = FALSE;
		
		if (strlen($ftp_settings['port']) > 0)
		{
			$ftp_settings['port'] = (int) $ftp_settings['port'];
		}
		else
		{
			$port = 21;
		}
		
		if(!$ftp->SetServer($ftp_settings['host'], $ftp_settings['port'], TRUE)) 
		{
			$ftp->quit();
			$ftp_error[] = "Setting server failed\n";
		}
		else
		{
			if (!$ftp->connect()) {
				$ftp_error[] = "Cannot connect\n";
			}
			else
			{
				if (!$ftp->login($ftp_settings['username'], $ftp_settings['password'])) {
					$ftp->quit();
					$ftp_error[] = "Login failed\n";
				}
			}
		}
		
		if (sizeof($ftp_error) == 0)
		{
			$path = $ftp_settings['path'];
			// see if we have the right path
			if (!$ftp->chdir($path))
			{
				$ftp_error[] = "Invalid path: $path\n";
			}
			else
			{
				$ftp->nlist("-la");
				$list = $ftp->rawlist(".", "-lA");
				
				if (sizeof($list) > 0)
				{
					foreach($list as $k=>$v)
					{
						// more readable list stats
						$list[$k]=$ftp->parselisting($v);
					}
				}
				
				// see if pico.findme is in here
				
				$correct_path = FALSE;
				
				foreach ($list as $file)
				{
					if ($file['name'] == 'pico.findme')
					{
						$correct_path = TRUE;
						break;
					}
				}
				
				if ($correct_path)
				{
					// good to go!
					
					$ftp_log = ob_get_contents();
					ob_end_clean();
					
					echo "FTP Connection Successful. Settings Saved";
					
					PicoSetting('host', $ftp_settings['host']);
					PicoSetting('port', $ftp_settings['port']);
					PicoSetting('path', $ftp_settings['path']);
					PicoSetting('username', $ftp_settings['username']);
					PicoSetting('password', $ftp_settings['password']);
					
					exit();
				}
			}
		}
		else
		{
			$ftp_log = ob_get_contents();
			ob_end_clean();
			
			echo "There were errors connecting:\n";
			
			foreach ($ftp_error as $e)
			{
				echo $e;
			}
			
			/*
			echo "Log:\n===========\n";
			echo $ftp_log;*/
			
			echo "Settings not saved";
			
			exit();
		}
	}
}

/*
$db->run('INSERT INTO `'.$settings_table.'` (`keyfield`, `keyvalue`) VALUES (?,?)', 'host', $_SESSION['FTP_INFORMATION']['host']);
		$db->run('INSERT INTO `'.$settings_table.'` (`keyfield`, `keyvalue`) VALUES (?,?)', 'port', $_SESSION['FTP_INFORMATION']['port']);
		$db->run('INSERT INTO `'.$settings_table.'` (`keyfield`, `keyvalue`) VALUES (?,?)', 'username', $_SESSION['FTP_INFORMATION']['username']);
		$db->run('INSERT INTO `'.$settings_table.'` (`keyfield`, `keyvalue`) VALUES (?,?)', 'password', $_SESSION['FTP_INFORMATION']['password']);
		$db->run('INSERT INTO `'.$settings_table.'` (`keyfield`, `keyvalue`) VALUES (?,?)', 'path', $_SESSION['FTP_INFORMATION']['path']);
		$db->run('INSERT INTO `'.$settings_table.'` (`keyfield`, `keyvalue`) VALUES (?,?)', 'version', '1.0');
*/
?>
<form method="post" action="<?=$body->url('includes/ap_ftp.php')?>" onsubmit="Pico_SaveFTP(this); return false;">
<input type="hidden" name="page_action" value="update_ftp" />
<p>Update your FTP settings below.</p>
<table border="0" cellpadding="0" cellspacing="2">
<tr>
	<td>Host</td>
	<td><input type="text" name="ftp[host]" value="<?=PicoSetting('host')?>" /></td>
</tr>
<tr>
	<td>Port</td>
	<td><input type="text" name="ftp[port]" value="<?=PicoSetting('port')?>" /></td>
</tr>
<tr>
	<td>Username</td>
	<td><input type="text" name="ftp[username]" value="<?=PicoSetting('username')?>" /></td>
</tr>
<tr>
	<td>Password</td>
	<td><input type="text" name="ftp[password]" value="<?=PicoSetting('password')?>" /></td>
</tr>
<tr>
	<td>Path to Pico</td>
	<td><input type="text" name="ftp[path]" value="<?=PicoSetting('path')?>" /></td>
</tr>
<tr>
	<td colspan="2" style="text-align: center">
		<input type="submit" value="Validate/Save" />
	</td>
</tr>
</table>
</form>