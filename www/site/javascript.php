<?php
chdir('../');
header('Content-type: text/javascript');

$rewrite = FALSE;

// cache output

if ($_GET['mode'] != 'reload')
{
	$cache_file = 'site/javascript.cache';
	
	if (file_exists($cache_file)) 
	{
		$_5_mins_ago = time() - 300;
		if ( ($_5_mins_ago > filemtime($cache_file)) or (filesize($cache_file) == 0) )
		{
			$rewrite = TRUE;
		}
		else
		{
			$javascript = file_get_contents($cache_file);
			echo $javascript;
			exit();
		}
	}
}

require_once('core.php');
ob_start();

ReadToEcho('site/core.js');
echo "\n";
// if admin include admin.js (not there yet)

if (USER_ACCESS > 2)
{
	ReadToEcho('site/admin.js');
	
	// check to see if we are using the new CSS editor
	if (file_exists('includes/editarea/edit_area_loader.js'))
	{
		ReadToEcho('includes/editarea/edit_area_loader.js');
	}
	else
	{
		// load the old one
		ReadToEcho('includes/codepress/codepress.js');
	}
	
	ReadToEcho('includes/uploader/browse_manager.js');
	echo "\n";
}

// include all JS

$content_dirs = GetContentDirs();
foreach ($content_dirs as $dir)
{
	$parts   = explode('/', $dir);
	$folder  = $parts[2];
	$options = GetContentOptions($folder);
	$inc_file = 'includes/content/'.$folder.'/'.$options['static_js'];
	IncludeIf($inc_file);
}

// get all JS from components

$links_data = $db->force_multi_assoc('SELECT DISTINCT `component_id` FROM `'.DB_CONTENT_LINKS.'`');
if ( (sizeof($links_data) > 0) and ($links_data != FALSE ) )
{
	foreach ($links_data as $data)
	{
		$component_id = $data['component_id'];
		$js = $db->result('SELECT `javascript` FROM `'.DB_COMPONENT_TABLE.'` WHERE `component_id`=?', $component_id);
		if ($js != FALSE) { echo $js . "\n"; }
	}
}

if (USER_ACCESS > 2)
{
	$content_dirs = GetContentDirs();
	foreach ($content_dirs as $dir)
	{
		$parts   = explode('/', $dir);
		$folder  = $parts[2];
		$options = GetContentOptions($folder);
		$inc_file = 'includes/content/'.$folder.'/'.$options['admin_js'];
		IncludeIf($inc_file);
	}
}

$javascript = ob_get_contents();
ob_end_clean();

echo $javascript;

if ( ($rewrite) and (is_writable($cache_file)) and (USER_ACCESS == 0) )
{
	$h = fopen($cache_file, 'w');
	fwrite($h, $javascript);
	fclose($h);
}

?>