<?php
require_once('includes/ftp/ftp_class.php');

$settings_table = DB_PREFIX . 'pico_settings';

$info = $db->assoc('SELECT * FROM `'.$settings_table.'`');
$settings = array();
foreach ($info as $i)
{
	$settings[$i['keyfield']] = $i['keyvalue'];
}

ob_start(); // surpress normal ftp class output
$ftp_error = array();
$ftp = new ftp(TRUE);
$ftp->Verbose = FALSE;
$ftp->LocalEcho = FALSE;
if(!$ftp->SetServer($settings['host'], (int)$settings['port'], TRUE)) {
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
		if (!$ftp->login($settings['username'], $settings['password'])) {
			$ftp->quit();
			$ftp_error[] = "Login failed\n";
		}
	}
}

if (!isset($error))
{
	$path = $settings['path'];
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
			// delete install files
			
			if ($h = opendir('install'))
			{
				while (false !== ($file = readdir($h)))
				{
					$f = 'install/'.$file;
					if (is_file($f))
					{
						$ftp->delete($f);
					}
				}
			}
			
			// delete install folder
			if (!$ftp->rmdir('install'))
			{
				$ftp_error[] = 'Could not delete install folder';
			}
			
			// chmod includes 755
			
			if (!$ftp->chmod('includes', 0755))
			{
				$ftp_error[] = "Error chmod'ing includes folder\n";
			}
			
			// chmod htaccess and config file
			
			if (!$ftp->chmod('.htaccess', 0644))
			{
				$ftp_error[] = "Error chmod'ing .htacess file\n";
			}
			
			if (!$ftp->chmod('includes/config.php', 0644))
			{
				$ftp_error[] = "Error chmod'ing config file\n";
			}
		}
		else
		{
			$ftp_error[] = 'Could not find pico.findme, please ensure that the FTP path you specified was valid';
		}

		$ftp->quit();
	}
}

$ftp_log = ob_get_contents();
ob_end_clean();

if (sizeof($ftp_error) == 0)
{
	echo 'Cleanup successful. You may now close this window.';
}
else
{
	foreach ($ftp_error as $error)
	{
		echo '<div class="error">'.$error.'</div>';
	}
}
?>