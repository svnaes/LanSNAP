<?php
if (defined('CURRENT_PAGE'))
{
	// get all the groups this user is in
	$user_groups  = array();
	$groups_table = DB_PREFIX . 'pico_groups';
	$all_groups   = $db->force_multi_assoc('SELECT * FROM `'.$groups_table.'`');
	
	if ( (is_array($all_groups)) and (sizeof($all_groups) > 0) )
	{
		foreach ($all_groups as $group)
		{
			$users = (sizeof($group['users']) > 0) ? explode(',', $group['users']) : array();
			if (in_array(USER_ID, $users))
			{
				$user_groups[] = $group['group_id'];
			}
		}
	}
	
	$page_details = $db->assoc('SELECT * FROM `'.DB_PAGES_TABLE.'` WHERE `page_id`=?', CURRENT_PAGE);
	if ($page_details != false)
	{
		$page_groups = (sizeof($page_details['groups']) > 0) ? explode(',', $page_details['groups']) : array();
		$user_is_in_group = FALSE;
		
		if (sizeof($page_groups) > 0)
		{
			foreach ($page_groups as $group)
			{
				if (in_array($group, $user_groups))
				{
					$user_is_in_group = TRUE;
				}
			}
		}
		
		//echo ($user_is_in_group) ? 'YES' : 'NO';
		//echo '<pre>'.print_r($user_is_in_group, TRUE).'</pre>';
		
		// process the page theme and output it, output will get grabbed by the OB
		$body->title = (strlen($page_details['www_title']) > 0) ? $page_details['www_title'] : $page_details['name'];
		if ((USER_ACCESS >= $page_details['user_access']) or ($user_is_in_group))
		{
			// process the page
			
			$body_theme = 'themes/'.$page_details['theme'].'/print.php';
			if (file_exists($body_theme))
			{
				include($body_theme);
			}
		}
		else
		{
			include('includes/login.php');
		}
	}
	else
	{
		echo '<div class="error">There was an error processing this page</div>';
	}
}
else
{
	echo '<div class="error">There is no default page established</div>';
}

$normal_output = ob_get_contents();
ob_end_clean();

if (defined('STATIC_HTML'))
{
	// this is for things like blog feeds, xml, etc so we can still use SEO friendly URLs but not be restricted to using html below
	echo STATIC_HTML;
	exit();
}

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
<head>
	<title><?=$body->title?></title>
	<meta name="description" content="<?=$page_details['description']?>" />
	<meta name="keywords" content="<?=$page_details['keywords']?>" />
<?=$body->get_head()?>
<?php if (file_exists('google.src')) { echo file_get_contents('google.src'); } ?>
</head>
<body>
<?=$normal_output?>
</body>
</html>