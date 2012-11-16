<?php
header('Content-type: text/css');
chdir('../');

$page_id = $_GET['page_id'];
if (!is_numeric($page_id))
{
	unset($page_id);
}
else
{
	define('CURRENT_PAGE', $page_id);
}

require_once('core.php');

$page_theme = $db->result('SELECT `theme` FROM `'.DB_PAGES_TABLE.'` WHERE `page_id`=?', CURRENT_PAGE);
$theme_url = $body->base_url . 'themes/'.$page_theme.'/';

ReadTheme('site/main.css', $body->base_url); // main css

if (USER_ACCESS > 2)
{
	// include admin CSS file
	ReadTheme('site/admin.css', $body->base_url);
}

ReadTheme('themes/common.css', $body->base_url . 'themes/');
ReadTheme('themes/'.$page_theme.'/style.css', $theme_url);

// based on the page number load some css

// based on page # include necessary javascript

$links_data = $db->force_multi_assoc('SELECT DISTINCT `component_id` FROM `'.DB_CONTENT_LINKS.'` WHERE `page_id`=?', CURRENT_PAGE);
if ( (sizeof($links_data) > 0) and ($links_data != FALSE ) )
{
	foreach ($links_data as $data)
	{
		$component_id = $data['component_id'];
		// get the foler
		$folder = $db->result('SELECT `folder` FROM `'.DB_COMPONENT_TABLE.'` WHERE `component_id`=?', $component_id);
		
		if ($folder != FALSE)
		{
			$options = GetContentOptions($folder);
			$inc_file = 'includes/content/'.$folder.'/'.$options['static_css'];
			
			if (USER_ACCESS > 3) { echo "\n/* $inc_file: */\n\n"; }
			
			ReadTheme($inc_file, $body->base_url);
			
			/*
			if (USER_ACCESS > 2)
			{
				$inc_file = 'includes/content/'.$folder.'/'.$options['admin_css'];
				IncludeIf($inc_file);
			}
			*/
		}
		
		$css = $db->result('SELECT `css` FROM `'.DB_COMPONENT_TABLE.'` WHERE `component_id`=?', $component_id);
		if ($css != FALSE) { 
			if (USER_ACCESS > 3) { echo "\n/* component $component_id: */\n\n"; }
			$css = str_replace('[URL]', $body->base_url, $css);
			echo $css . "\n";
		}
	}
}

$content_dirs = GetContentDirs();
foreach ($content_dirs as $dir)
{
	$parts   = explode('/', $dir);
	$folder  = $parts[2];
	$options = GetContentOptions($folder);
	
	
	$inc_file = 'includes/content/'.$folder.'/'.$options['admin_css'];
	if (USER_ACCESS > 3) { echo "\n/* $inc_file: */\n\n"; }
	ReadTheme($inc_file, $body->base_url);
}
?>