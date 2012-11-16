<?php
/* core.php
 *
 * main file for inclusion into any page, script, or external source to establish 
 * database connection, establish constants, and verify/prepare session
 */
$request = basename($_SERVER['REQUEST_URI']);
if ($request == 'core.php') { echo 'You cannot access this file directly'; exit(); }

if (file_exists('includes/config.php'))
{
	require_once('includes/config.php');
	require_once('includes/functions.php');
	require_once('includes/database.class.php');
	require_once('includes/body.class.php');
	require_once('includes/upload.class.php');
}
else
{
	session_start();
	header('Cache-control: private');
	require_once('includes/ftp/ftp_class.php');
	require_once('includes/functions.php');
	require_once('includes/database.class.php');
	require_once('install/install.php');
	exit();
}


// establish a connection

$db = new DataBase($config['dbhost'], $config['dbuser'], $config['dbpass'], $config['dbname']);

if ($db->connected == FALSE)
{
	echo $db->error;
	exit();
}
else
{
	define('DB_PREFIX', $config['dbprefix']);
	define('DB_USER_TABLE', $config['dbprefix'] . 'pico_users');
	define('DB_PAGES_TABLE', $config['dbprefix'] . 'pico_pages');
	define('DB_CONTENT_LINKS', $config['dbprefix'] . 'pico_content_links');
	define('DB_COMPONENT_TABLE', $config['dbprefix'] . 'pico_components');
	define('DB_CONTENT', $config['dbprefix'] . 'pico_content');
	define('DB_LINKS', $config['dbprefix'] . 'pico_links');
	define('DB_DROPDOWN', $config['dbprefix'] . 'pico_dropdown');
	define('DB_TRANSACTION_LOG', $config['dbprefix'] . 'pico_payment_transactions');
	define('PICO_SETTINGS', $config['dbprefix'] . 'pico_settings');
	
	define('ADMIN_EMAIL', $config['admin_email']);
	define('ADMIN_FROM', $config['admin_from']);
	

	if (defined('CURRENT_PAGE'))
	{
		// this is here in case we are defining the current page id before referencing core, such as 
		// style.php, or javascript.php, or maybe some sort of javascript we need somewheres
		$current_alias = $db->result('SELECT `alias` FROM `'.DB_PAGES_TABLE.'` WHERE `page_id`=?', CURRENT_PAGE);
		if ($current_alias != FALSE)
		{
			define('CURRENT_ALIAS', $current_alias);
		}
	}
	else
	{
		$request = $_SERVER['REQUEST_URI'];
		if (substr($request, 0, strlen($config['domain_path'])) == $config['domain_path'])
		{
			$request = substr($request, strlen($config['domain_path']));
		}
		
		$parts = explode('/', $request); // Break into an array
		// Lets look at the array of items we have:
		$params = array();
		foreach ($parts as $part)
		{
			if (strlen($part) > 0)
			{
				$params[] = $part;
			}
		}
		
		if (isset($params[0]))
		{
			$bool = ($params[0] == 'print') ? TRUE : FALSE;
			
			$ca = ($bool) ? $params[1] : $params[0];
			define('CURRENT_ALIAS', $ca);
			define('PRINTER_FRIENDLY', $bool);
			
			if (PRINTER_FRIENDLY)
			{
				array_shift($params);
			}
		}
		else
		{
			define('PRINTER_FRIENDLY', FALSE);
			// get the default page
			$default_alias = $db->result('SELECT `alias` FROM `'.DB_PAGES_TABLE.'` WHERE `is_default`=1');
			if ($default_alias != FALSE)
			{
				define('CURRENT_ALIAS', $default_alias);
			}
		}
	}
}

if ( (defined('CURRENT_ALIAS')) and (!defined('CURRENT_PAGE')) )
{
	$page_details = $db->assoc('SELECT * FROM `'.DB_PAGES_TABLE.'` WHERE `alias`=?', CURRENT_ALIAS);
	if ($page_details != false)
	{
		define('CURRENT_PAGE', $page_details['page_id']);
	}
	else
	{
		// whatever was keyed in does not exist
		$default_id = $db->result('SELECT `page_id` FROM `'.DB_PAGES_TABLE.'` WHERE `is_default`=1');
		if ($default_id != FALSE)
		{
			define('CURRENT_PAGE', $default_id);
		}
	}
}

if (CURRENT_ALIAS == 'login')
{
	include('includes/login.php');
}
else
{
	if (isset($_COOKIE['keep_session']))
	{
		//echo 'still in there!';
		$session_data = unserialize(base64_decode($_COOKIE['keep_session']));
		if (!is_array($session_data))
		{
			exit();
		}
		$user_data = $db->assoc('SELECT * FROM `'.DB_USER_TABLE.'` WHERE `session_id`=? AND `last_ip`=?', $session_data['session_id'], $session_data['ip_address']);
		if ($user_data != false)
		{
			$user_ip = getenv('REMOTE_ADDR');
			if ($session_data['ip_address'] != $user_ip)
			{
				// user is coming in from same computer diff IP please update info
				$db->run('UPDATE `'.DB_USER_TABLE.'` SET `last_login`=?, `last_ip`=? WHERE `id`=?', time(), $user_ip, $user_data['id']);
			
				// establish cookie
				$domain = CookieDomain();
				
				$session_data = array(
					'ip_address' => $user_ip,
					'session_id' => $session_data['session_id'],
				);
				
				$sd = base64_encode(serialize($session_data));
				$good = setcookie('keep_session', $sd, time()+1209600, '/', $domain);
			}
			session_id($user_data['session_id']);
			define('USER_ACCESS', $user_data['access']);
			define('USER_ID', $user_data['id']);
		}
		else
		{
			// destroy cookie
			$domain = CookieDomain();
			setcookie('keep_session', '', time() - 3600, '/', $domain);
			
			$ip = getenv('REMOTE_ADDR');
			
			if (substr($ip, 0, 3) == '10.')
			{
				define('USER_ACCESS', 1);
			}
			else
			{
				define('USER_ACCESS', 0);
			}
		}
	}
	else
	{
		$ip = getenv('REMOTE_ADDR');
		if (substr($ip, 0, 3) == '10.')
		{
			define('USER_ACCESS', 1);
		}
		else
		{
			define('USER_ACCESS', 0);
		}
	}

	// start a session
	session_start();
	header('Cache-control: private');

	// start a body for primary output

	$body = new Body();
	$body->base_url = $config['domain_path'];
	define('PICO_BASEPATH', $config['domain_path']);
}
?>