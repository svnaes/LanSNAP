<?php
$request = $_SERVER['REQUEST_URI'];
if ($request == 'login.php') { echo 'You cannot access this file directly'; exit(); }

$body = new Body();
$body->base_url = $config['domain_path'];

if (isset($_POST['login_username']))
{
	// user is trying to login
	
	$username = $_POST['login_username'];
	$password = md5($_POST['login_password']);
	
	$user_id = $db->result('SELECT `id` FROM `'.DB_USER_TABLE.'` WHERE `username`=? AND `password`=?', $username, $password);
	if ($user_id != FALSE)
	{
		// see if a session exists
		session_start();
		$domain = CookieDomain();
		
		if (isset($_COOKIE['keep_session']))
		{
			// log them out
			
			$session_data = unserialize(base64_decode($_COOKIE['keep_session']));
			$db->run('UPDATE `'.DB_USER_TABLE.'` SET `session_id`=? WHERE `session_id`=?', '', $session_data['session_id']);
			
			setcookie('keep_session', '', time() - 3600, '/', $domain);
			setcookie(session_name(), '', time() - 42000, '/');
			session_destroy();
			session_start();
		}
		
		$session_id = session_id();
		$ip         = getenv('REMOTE_ADDR');
		$db->run('UPDATE `'.DB_USER_TABLE.'` SET `last_login`=?, `last_ip`=?, `session_id`=? WHERE `id`=?', time(), $ip, $session_id, $user_id);
		
		// establish cookie
		
		$session_data = array(
			'ip_address' => $ip,
			'session_id' => $session_id,
		);
		
		$sd = base64_encode(serialize($session_data));
		$good = setcookie('keep_session', $sd, time()+1209600, '/', $domain);
		
		if ($request == $body->url('login'))
		{
			$request = $body->url('');
		}
		
		header('Location: ' . $request);
		exit();
	}
	else
	{
		$error = 'Invalid username or password';
	}
}

// login page output only. doesn't interact with index

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
<head>
	<title><?=$body->title?></title>
	<link href="<?=$body->url('site/style.php?page_id='.CURRENT_PAGE)?>" type="text/css" rel="stylesheet" />
	<script type="text/javascript">
	/* global variables */
	var CURRENT_PAGE = '<?=CURRENT_PAGE?>';
	var CURRENT_ALIAS = '<?=CURRENT_ALIAS?>';
	var REQUEST_URI = '<?=$_SERVER['REQUEST_URI']?>';
	var BASE_URL = '<?=$body->base_url?>';
	</script>
	<script type="text/javascript" src="<?=$body->url('site/javascript.php' . ((USER_ACCESS > 2) ? '?mode=reload' : ''))?>"></script>
	<meta name="description" content="<?=$page_details['description']?>" />
	<meta name="keywords" content="<?=$page_details['keywords']?>" />
<?=$body->get_head()?>
</head>
<body>

<div id="page_container">
	<div id="top">
		<div id="top_main">
<?php

if (isset($error))
{
?>
<div class="error"><?=$error?></div>
<?php
}

?>
			<div class="clear"></div>
			<div id="login">
				<div id="login_title">Please Log In</div>
				<form method="post" action="<?=$request?>">
				<table border="0" cellpadding="3" cellspacing="1" id="login_form">
				<tr>
					<td>Username</td>
					<td><input type="text" name="login_username" id="login_username" /></td>
				</tr>
				<tr>
					<td>Password</td>
					<td><input type="password" name="login_password" /></td>
				</tr>
				</table>
				<input type="submit" value="Log In" />
				</form>
			</div>
		</div>
	</div>
</div>
<script type="text/javascript">
var func = function() {
	var obj = document.getElementById('login_username');
	obj.focus();
}
add_load_event(func);
</script>
</body>
</html>
<?php
// don't process any more
exit();
?>