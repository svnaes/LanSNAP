<?php
/* functions.php
 *
 * various functions used in the application
 * 
 */

$request = basename($_SERVER['REQUEST_URI']);
if ($request == 'functions.php') { echo 'You cannot access this file directly'; exit(); }

function CookieDomain()
{
	$domain = str_replace('www.', '', $_SERVER['SERVER_NAME']);
	//$domain = '.' . $domain;
	return $domain;
}

function ReadToEcho($filename)
{
	if ( (file_exists($filename)) and (!is_dir($filename)) )
	{
		if (filesize($filename) > 0)
		{
			$h = fopen($filename, "r");
			$contents = fread($h, filesize($filename));
			echo $contents;
		}
	}
}

function ReadTheme($filename, $url)
{
	if ( (file_exists($filename)) and (!is_dir($filename)) )
	{
		if (filesize($filename) > 0)
		{
			$h = fopen($filename, "r");
			$contents = fread($h, filesize($filename));
			$contents = str_replace('[URL]', $url, $contents);
			echo $contents;
			fclose($h);
		}
	}
}

function UserAccessDrop($name, $current_access, $select = 1)
{
	$drop = '<select name="'.$name.'">';
	$access_levels = array(
		1=>'Registered User',
		2=>'Author',
		3=>'Moderator',
		4=>'Administrator',
		5=>'Super Administrator',
	);
	
	foreach ($access_levels as $key=>$val)
	{
		if ($current_access >= $key)
		{
			$selected = ($select == $key) ? ' selected="selected"' : '';
			$drop .= '<option value="'.$key.'"'.$selected.'>'.$val.'</option>';
		}
	}
	$drop .= '</select>';
	return $drop;
}

function AccessLevel($level)
{
	$access_levels = array(
		0=>'Anyone',
		1=>'Registered User',
		2=>'Author',
		3=>'Moderator',
		4=>'Administrator',
		5=>'Super Administrator',
	);
	
	return $access_levels[$level];
}

function Stamp($timestamp = 0)
{
	if ($timestamp == 0)
	{
		return 'n/a';
	}
	return date('h:i F, j Y', $timestamp);
}

function AccessDrop($name, $select = 0)
{
	$drop = '<select name="'.$name.'">';
	
	$access_levels = array(
		0=>'Anyone',
		1=>'Registered User',
		2=>'Author',
		3=>'Moderator',
		4=>'Administrator',
		5=>'Super Administrator',
	);
	
	foreach ($access_levels as $key=>$val)
	{
		$selected = ($select == $key) ? ' selected="selected"' : '';
		$drop .= '<option value="'.$key.'"'.$selected.'>'.$val.'</option>';
	}
	$drop .= '</select>';
	return $drop;
}

function GroupDrop($page_id, $dropdown_name)
{
	//DB_PAGES_TABLE
	global $db;
	$group_table = DB_PREFIX . 'pico_groups';
	
	$all_groups = $db->result('SELECT `groups` FROM `'.DB_PAGES_TABLE.'` WHERE `page_id`=?', $page_id);
	$current_groups = ($all_groups != null) ? explode(',', $all_groups) : array();
	
	$dropdown = '<select name="'.$dropdown_name.'[]" multiple="multiple" style="height: 100px; width: 200px">';
	
	$groups = $db->force_multi_assoc('SELECT `group_id`, `name` FROM `'.$group_table.'` ORDER BY `name` ASC');
	if ( (!is_array($groups)) or (sizeof($groups) == 0) )
	{
		$dropdown .= '</select>';
		return $dropdown;
	}
	
	foreach ($groups as $group)
	{
		$selected = (in_array($group['group_id'], $current_groups)) ? 'selected="selected"' : '';
		$dropdown .= '<option value="'.$group['group_id'].'" '.$selected.'>'.$group['name'].'</option>';
	}
	$dropdown .= '</select>';
	
	return $dropdown;
}

function ViewtypeDrop($name, $select = 0)
{
	$drop = '<select name="'.$name.'">';
	
	$view_types = array(
		0=>'Always Same',
		1=>'Different Each Page',
		2=>'Different Each Sub-Page',
		3=>'Different Everywhere',
		4=>'Different For Each Main Section'
	);
	
	foreach ($view_types as $key=>$val)
	{
		$selected = ($select == $key) ? ' selected="selected"' : '';
		$drop .= '<option value="'.$key.'"'.$selected.'>'.$val.'</option>';
	}
	$drop .= '</select>';
	return $drop;
}

function PageNameToAlias($page_name)
{
	$page_name = strtolower($page_name);
	$page_name = str_replace(' ', '-', $page_name);
	$page_name = str_replace('_', '-', $page_name);
	$page_name = preg_replace('/[^a-z0-9\-]/', '', $page_name); // only letters, numbers, dashes
	while (strstr($page_name, '--'))
	{
		$page_name = (str_replace('--', '-', $page_name));
	}
	return $page_name;
}

function PrettyVar($var)
{
	return ucwords(strtolower(str_replace('_', ' ', $var)));
}

function CheckInstanceID($component_id, $instance_id)
{
	global $db;
	$count = $db->result('SELECT count(*) FROM `'.DB_CONTENT.'` WHERE `instance_id`=?', $instance_id);
	if ($count == 0)
	{
		$db->run('INSERT INTO `'.DB_CONTENT.'` (`instance_id`, `component_id`) VALUES (?,?)', $instance_id, $component_id);
	}
}

function GetComponent($component_id, $page_id, $req_uri)
{
	if (strlen($req_uri) == 0) { $req_uri = $_SERVER['REQUEST_URI']; }
	// for a single component call, more than likely by reload
	global $db, $body;
	
	ob_start();
	$component_info = $db->assoc('SELECT * FROM `'.DB_COMPONENT_TABLE.'` WHERE `component_id`=?', $component_id);
	if ( ($component_info != FALSE) and (USER_ACCESS >= $component_info['access']) )
	{
		$instance_id = GenerateInstanceID($component_id, $component_info['view_setting'], $page_id, $req_uri);
		
		CheckInstanceID($component_id, $instance_id);
		$component_options = GetContentOptions($component_info['folder']);
		
		$inc_file = 'includes/content/'.$component_info['folder'].'/'.$component_options['content_file'];
		if ((file_exists($inc_file)) and (!is_dir($inc_file)))
		{
			// same as directly below
			if (USER_ACCESS > 2) { echo '<div class="content_box_bg"><div class="pico_move" id="move_'.$component_id.'"></div>'; }
			echo '<div class="header"></div>';
			echo '<div class="content">';
			include($inc_file);
			echo '</div>';
			echo '<div class="footer"></div>';
			if (USER_ACCESS > 2) { echo '<div class="clear"></div></div>'; }
		}
	}
	$return = ob_get_contents();
	ob_end_clean();
	return $return;
}

function GetContent($container, $page_id, $req_uri = '')
{
	if (strlen($req_uri) == 0) { $req_uri = $_SERVER['REQUEST_URI']; }
	global $db, $body;
	$display_data = $db->force_multi_assoc('SELECT * FROM `'.DB_CONTENT_LINKS.'` WHERE `page_id`=? AND `location`=? ORDER BY `position` ASC', $page_id, $container);
	ob_start();
	
	if ( ($display_data != FALSE) and (sizeof($display_data) > 0) )
	{
		foreach ($display_data as $entry)
		{
			$component_id = $entry['component_id'];
			$component_info = $db->assoc('SELECT * FROM `'.DB_COMPONENT_TABLE.'` WHERE `component_id`=?', $component_id);
			
			if ( ($component_info != FALSE) and (USER_ACCESS >= $component_info['access']) )
			{
				$instance_id = GenerateInstanceID($component_id, $component_info['view_setting'], $page_id, $req_uri);
				
				$component_options = GetContentOptions($component_info['folder']);
				CheckInstanceID($component_id, $instance_id);
				
				$inc_file = 'includes/content/'.$component_info['folder'].'/'.$component_options['content_file'];
				if ((file_exists($inc_file)) and (!is_dir($inc_file)))
				{
					echo '<div id="box_'.$component_id.'">';
					if (USER_ACCESS > 2) { echo '<div class="content_box_bg"><div class="pico_move" id="move_'.$component_id.'"></div>'; }
					echo '<div class="header"></div>';
					echo '<div class="content">';
					include($inc_file);
					echo '</div>';
					echo '<div class="footer"></div>';
					if (USER_ACCESS > 2) { echo '<div class="clear"></div></div>'; }
					echo '</div>';
				}
			}
		}
	}
	
	if (USER_ACCESS > 2) { echo '<div class="pico_move" id="move_'.$container.'"></div>'; }
	$return = ob_get_contents();
	ob_end_clean();
	
	
	return $return;
}

function ContentDiv($name)
{
	$bg_layer = '';
	$html = '<div id="'.$name.'">';
	if (USER_ACCESS > 2) { $html .= '<div class="content_div_bg">'; }
	
	// get the components in this page and content div
	$html .= GetContent($name, CURRENT_PAGE);
	
	if (USER_ACCESS > 2) { $html .= '</div>'; }
	
	$html .= '</div>';
	echo $html;
}

function GetContentOptions($content_folder)
{
	$content_file = 'includes/content/'.$content_folder.'/config.php';
	if (file_exists($content_file))
	{
		include($content_file);
		return $options;
	}
}

function GetContentDirs()
{
	$return_array = array();
	$content_dir = 'includes/content/';
	if ($h = opendir($content_dir))
	{
		while (false !== ($file = readdir($h)))
		{
			$full_file = $content_dir . $file;
			if ( (is_dir($full_file)) and ($file != '.') and ($file != '..') )
			{
				$check_config = $full_file . '/config.php';
				if (file_exists($check_config))
				{
					$return_array[] = $check_config;
				}
			}
		}
	}
	return $return_array;
}

function IncludeIf($file)
{
	if ((file_exists($file)) and (!is_dir($file)))
	{
		$_ext = array_pop(explode('.', basename($file)));
		$ext = strtolower($_ext);
		if ($ext == 'php')
		{
			include($file);
		}
		else
		{
			ReadToEcho($file);
		}
	}
}

function TopDir($dirs)
{
	$dirs = dirname($dirs);
	$pieces = explode('/', $dirs);
	
	do
	{
		$top_dir = array_pop($pieces);
	} while ((strlen($top_dir) == 0) and (sizeof($pieces) > 0));
	
	return $top_dir;
}

function GenerateInstanceID($component_id, $view_setting, $page_id, $req_uri)
{
	global $body, $db;
	$site_prefix = $body->url('');
	
	$pico_uri = substr($req_uri, sizeof($site_prefix));
	
	if ($view_setting == 0)
	{
		// component only
		return md5($component_id);
	}
	elseif ($view_setting == 1)
	{
		return md5($component_id.'/'.$page_id);
	}
	elseif ($view_setting == 2)
	{
		$pieces = explode('/', $pico_uri);
		$subpage = $pieces[1];
		return md5($component_id.'/'.$page_id.'/'.$subpage);
	}
	elseif ($view_setting == 3)
	{
		return md5($component_id.'/'.$page_id.'/'.$pico_uri);
	}
	elseif ($view_setting == 4)
	{
		$sh    = DB_PREFIX . 'pico_site_heirarchy';
		$start = $db->result('SELECT `entry_id` FROM `'.$sh.'` WHERE `page_id`=?', $page_id);
		
		if (is_numeric($start))
		{
			do
			{
				$parent = $db->result('SELECT `parent` FROM `'.$sh.'` WHERE `entry_id`=?', $start);
				if ((is_numeric($parent)) and ($parent != 0))
				{
					$start = $parent;
				}
				
			} while ($parent != 0); 
			$page_id = $db->result('SELECT `page_id` FROM `'.$sh.'` WHERE `entry_id`=?', $start);
		}
		
		$instance_id = md5($component_id.'/'.$page_id);
		return $instance_id;
	}
}

function asorti($arr) {
   $arr2 = $arr;
   foreach($arr2 as $key => $val) {
      $arr2[$key] = strtolower($val);
   }
 
   asort($arr2);
   foreach($arr2 as $key => $val) {
      $arr2[$key] = $arr[$key];
   }

   return $arr2;
} 

function ThemeDrop($name, $selected_dir)
{
	//echo "selected: $selected\n";
	$content_dir = 'themes/';
	if ($h = opendir($content_dir))
	{
		while (false !== ($file = readdir($h)))
		{
			$full_file = $content_dir . $file;
			if ( (is_dir($full_file)) and ($file != '.') and ($file != '..') )
			{
				$check_body = $full_file . '/body.php';
				if (file_exists($check_body))
				{
					$return_array[] = $file;
				}
			}
		}
	}
	
	$dropdown = '<select name="'.$name.'">';
	if (sizeof($return_array) > 0)
	{
		foreach ($return_array as $dir)
		{
			//echo "DIR: $dir SELECTED: $selected<br />";
			$selected = ($dir == $selected_dir) ? 'selected="selected"' : '';
			$dropdown .= '<option value="'.$dir.'" '.$selected.'>'.$dir.'</option>';
		}
	}
	$dropdown .= '</select>';
	return $dropdown;
}

function decrypt($text, $key = 'D154D41822354321')
{
	//$key = 'D154D41391E97489';
	$text = pack("H*", strtolower($text));
	$text = mcrypt_decrypt(MCRYPT_RIJNDAEL_128, $key, $text, MCRYPT_MODE_ECB, $key);
	$text = substr($text, 0, -1 * ord(substr($text,-1,1)));
	return trim($text);
}

function encrypt($text, $key = 'D154D41822354321')
{
	$pad_length = 16 - (strlen($text) % 16);
	$text .= str_repeat(chr($pad_length), $pad_length);
	//$key = 'D154D41391E97489';
	
	$text = mcrypt_encrypt(MCRYPT_RIJNDAEL_128, $key, $text, MCRYPT_MODE_ECB, $key);
	$len = strlen($text);
	$newtext = '';
	for ($x=0; $x<$len; $x++)
	{
		$newtext .= str_pad(dechex(ord(substr($text,$x,1))),2,"0",STR_PAD_LEFT);
	}
	$text = strtoupper($newtext);
	return $text;
}

function generate_text($length = 8)
{
	$phrase   = "";
	$possible = "23456789ABCDEFGHJKMNPQRSTUVWXYZ"; 
	$i        = 0; 
	
	while ($i < $length)
	{ 
		// pick a random character from the possible ones
		$char = substr($possible, mt_rand(0, strlen($possible)-1), 1);
		
		if (!strstr($phrase, $char))
		{ 
			$phrase .= $char;
			$i++;
		}
	}
	return $phrase;
}

function PicoSetting($field, $value = null)
{
	global $db;
	if ($value == null)
	{
		// get value
		$return = $db->result('SELECT `keyvalue` FROM `'.PICO_SETTINGS.'` WHERE `keyfield`=?', $field);
		return $return;
	}
	else
	{
		// set value
		$check = $db->result('SELECT count(1) FROM `'.PICO_SETTINGS.'` WHERE `keyfield`=?', $field);
		if ($check == 1)
		{
			$db->run('UPDATE `'.PICO_SETTINGS.'` SET `keyvalue`=? WHERE `keyfield`=?', $value, $field);
		}
		else
		{
			$db->run('INSERT INTO `'.PICO_SETTINGS.'` (`keyvalue`,`keyfield`) VALUES (?,?)', $value, $field);
		}
	}
}

function SortSelectOptions($a, $b)
{
	$a = strtolower($a['display']);
	$b = strtolower($b['display']);
	
	if ($a == $b) {
        return 0;
    }
    return ($a < $b) ? -1 : 1;
}

function Pico_ConnectFTP()
{
	// get ftp settings
	$ftp_host = PicoSetting('host');
	$ftp_port = (int) PicoSetting('port');
	$ftp_path = PicoSetting('path');
	$ftp_user = PicoSetting('username');
	$ftp_pass = PicoSetting('password');
	require_once('includes/ftp/ftp_class.php');
	
	ob_start(); // surpress normal ftp class output
	
	if (!is_numeric($ftp_port)) { $ftp_port = 21; }
	
	$ftp = new ftp(TRUE);
	$ftp->Verbose = FALSE;
	$ftp->LocalEcho = FALSE;
	
	if(!$ftp->SetServer($ftp_host, $ftp_port, TRUE)) 
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
			if (!$ftp->login($ftp_user, $ftp_pass)) {
				$ftp->quit();
				$ftp_error[] = "Login failed\n";
			}
		}
	}
	
	ob_end_clean();
	
	if (sizeof($ftp_error) == 0)
	{
		$ftp->chdir($ftp_path);
		return $ftp;
	}
	else
	{
		return $ftp_error;
	}
}

function CheckWritable($path)
{
	if (!is_writable($path))
	{
		$ftp = Pico_ConnectFTP();
		if (is_object($ftp))
		{
			if (is_dir($path))
			{
				$ftp->chmod($path, 0777);
			}
			else
			{
				$ftp->chmod($path, 0666);
			}
			return TRUE;
		}
		else
		{
			echo '<p class="error">Error: '.$path.' is not writable, please check your FTP settings</p>';
			echo '<pre>'.print_r($ftp, TRUE).'</pre>';
		}
	}
	return TRUE;
}

function SiteGetHeirarchy($parent = 0)
{
	global $db;
	$sh_table = DB_PREFIX . 'pico_site_heirarchy';
	
	$return = array();
	
	$items = $db->force_multi_assoc('SELECT * FROM `'.$sh_table.'` WHERE `parent`=? ORDER BY `position` ASC', $parent);
	
	if (is_array($items))
	{
		foreach ($items as $item)
		{
			$additional_nav = array();
			
			$children = SiteGetHeirarchy($item['entry_id']);
			
			if ( (is_numeric($item['page_id'])) and ($item['page_id'] != 0) )
			{
				// see if this page has any components and drive a sub nav
				$component_ids = $db->force_multi_assoc('SELECT DISTINCT `component_id` AS `component_id` FROM `'.DB_CONTENT_LINKS.'` WHERE `page_id`=?', $item['page_id']);
				if (is_array($component_ids))
				{
					foreach ($component_ids as $id)
					{
						$component_id = $id['component_id'];
						$folder       = $db->result('SELECT `folder` FROM `'.DB_COMPONENT_TABLE.'` WHERE `component_id`=?', $component_id);
						$nav_path     = 'includes/content/'.$folder.'/navigate.php';
						
						if (is_file($nav_path))
						{
							unset($sub_nav);
							include($nav_path);
							if (isset($sub_nav[$component_id]))
							{
								foreach ($sub_nav[$component_id] as $sub_item)
								{
									$children[] = $sub_item;
								}
							}
						}
					}
				}
			}
			
			//echo '<pre>'.print_r($children, true).'</pre>';
			
			
			$return[] = array(
				'id'=>$item['entry_id'],
				'children'=> $children,
				'show_in_nav'=>$item['show_in_nav'],
				'linked'=>$item['linked']
			);
		}
	}
	return $return;
}

function DeleteSHItem($id)
{
	// see if this item has any children
	global $db;
	$sh_table  = DB_PREFIX . 'pico_site_heirarchy';
	$sub_items =  $db->force_multi_assoc('SELECT * FROM `'.$sh_table.'` WHERE `parent`=? ORDER BY `position` ASC', $id);
	if (is_array($sub_items))
	{
		foreach ($sub_items as $item)
		{
			DeleteSHItem($item['entry_id']);
		}
	}
	$info = $db->assoc('SELECT * FROM `'.$sh_table.'` WHERE `entry_id`=?', $id);
	$db->run('UPDATE `'.$sh_table.'` SET `position` = (`position`-1) WHERE `parent`=? AND `position`>?', $info['parent'], $info['position']);
	$db->run('DELETE FROM `'.$sh_table.'` WHERE `entry_id`=?', $id);
}

function SiteHeirarchyDisplay($data)
{
	global $db, $body;
	$sh_table = DB_PREFIX . 'pico_site_heirarchy';
	
	if (sizeof($data) > 0)
	{
		echo '<ul>';
		foreach ($data as $item)
		{
			$id = $item['id'];
			$info = $db->assoc('SELECT * FROM `'.$sh_table.'` WHERE `entry_id`=?', $id);
			
			if (!isset($item['sub_link']))
			{
				if ($info['page_id'] != 0)
				{
					// page
					$name = $db->result('SELECT `name` FROM `'.DB_PAGES_TABLE.'` WHERE `page_id`=?', $info['page_id']);
				}
				else
				{
					$name = $info['text'];
				}
				
				$linked =  ($item['linked'] == 1) ? 'Unlink' : 'Link';
				
				$up     = '<img class="icon click" src="'.$body->url('includes/icons/arrow-up.png').'" onclick="Pico_SHMoveItem('.$id.', \'up\')" />';
				$down   = '<img class="icon click" src="'.$body->url('includes/icons/arrow-down.png').'" onclick="Pico_SHMoveItem('.$id.', \'down\')" />';
				$delete = '<img class="icon click" src="'.$body->url('includes/icons/delete.png').'" onclick="Pico_SHDeleteItem('.$id.')" />';
				$add    = '<img class="icon click" src="'.$body->url('includes/icons/plus.png').'" onclick="Pico_SHAddItem('.$id.')" />';
				$hide   = '<img class="icon click" src="'.$body->url('includes/icons/edit.png').'" onclick="Pico_SHHideItem('.$id.')" />';
				$unlink = '<span class="click" onclick="Pico_SHUnlinkItem('.$id.')">'.$linked.'</span>';
			}
			else
			{
				$name   = $item['name'];
				$up     = '';
				$down   = '';
				$delete = '';
				$add    = '';
				$hide   = '';
				$unlink = '';
			}
			
			
			$class = ($item['show_in_nav'] == 1) ? 'active' : 'inactive';
			
			echo '<li><table border="0" cellpadding="0" cellspacing="0"><tr><td>'.$up.$down. '</td><td class="'.$class.'">' . $name .'</td><td>'.$delete.$add.$hide.$unlink.'</td></tr></table>';
			if (sizeof($item['children']) > 0)
			{
				SiteHeirarchyDisplay($item['children']);
			}
			echo '</li>';
		}
		echo '</ul>';
	}
}

function Pico_GroupProfileDropdown($name = 'group_profile', $selected_profile = 0)
{
	global $db;
	$profile_list = DB_PREFIX . 'user_profile_list';
	
	$profiles = $db->force_multi_assoc('SELECT * FROM `'.$profile_list.'` ORDER BY `name` ASC');
	$dropdown = '<select name="'.$name.'"><option value="0">None</option>';
	if (is_array($profiles))
	{
		foreach ($profiles as $profile)
		{
			$selected = ($selected_profile == $profile['profile_id']) ? 'selected="selected"' : '';
			$dropdown .= '<option value="'.$profile['profile_id'].'" '.$selected.'>'.$profile['name'].'</option>';
		}
	}
	
	$dropdown .= '</select>';
	return $dropdown;
}

function Pico_GroupFieldType($name = 'field_type', $selected_val = '')
{
	$field_types = array(
		'text'       => 'Text',
		'radio'      => 'Radio',
		'dropdown'   => 'Dropdown',
		'checkbox'   => 'Check Box',
		'check_list' => 'Check Box List',
		'terms'      => 'Terms/Conditions',
		'lg_text'    => 'Large Text',
		'date'       => 'Date',
		'info'       => 'Info',
	);
	
	$dropdown = '<select name="'.$name.'">';
	foreach ($field_types as $key=>$val)
	{
		$selected = ($selected_val == $key) ? 'selected="selected"' : '';
		$dropdown .= '<option value="'.$key.'" '.$selected.'>'.$val.'</option>';
	}
	$dropdown .= '</select>';
	return $dropdown;
}

function Pico_GroupFieldPattern($name = 'field_pattern', $selected_val = '')
{
	$field_types = array(
		'none'     => 'None',
		'email'    => 'Email',
		'phone'    => 'Phone',
		'alpha'    => 'Alpha',
		'numeric'  => 'Number',
		'alphanum' => 'Alpha/Numeric',
	);
	
	$dropdown = '<select name="'.$name.'">';
	foreach ($field_types as $key=>$val)
	{
		$selected = ($selected_val == $key) ? 'selected="selected"' : '';
		$dropdown .= '<option value="'.$key.'" '.$selected.'>'.$val.'</option>';
	}
	$dropdown .= '</select>';
	return $dropdown;
}

function Pico_GroupBillingField($name = 'field_pattern', $selected_val = '')
{
	$field_types = array(
		'' => '',
		'address' => 'Address',
		'city'    => 'City',
		'state'   => 'State',
		'zip_code'=> 'Zip Code',
		'phone'   => 'Phone Number',
	);
	
	$dropdown = '<select name="'.$name.'">';
	foreach ($field_types as $key=>$val)
	{
		$selected = ($selected_val == $key) ? 'selected="selected"' : '';
		$dropdown .= '<option value="'.$key.'" '.$selected.'>'.$val.'</option>';
	}
	$dropdown .= '</select>';
	return $dropdown;
}

// take a given profile id, return array containing field names and html output and other info
function Pico_GetProfileFieldData($profile_id, $values = array())
{
	global $db;
	$profile_fields = DB_PREFIX . 'user_profile_fields';
	
	$fields = $db->force_multi_assoc('SELECT * FROM `'.$profile_fields.'` WHERE `profile_id`=? AND `display`=? ORDER BY `position` ASC', $profile_id, 1);
	$return = array();
	
	if (is_array($fields))
	{
		foreach ($fields as $f)
		{
			$item = array();
			$item['name']     = $f['name'];
			$item['pattern']  = $f['pattern'];
			$item['required'] = $f['required'];
			$item['options']  = $f['options'];
			$item['caption']  = $f['caption'];
			$item['type']     = $f['type'];
			$item['id']       = $f['field_id'];
			
			$f_id = $f['field_id'];
			
			$value = $values['field_' . $f_id];
			
			switch($f['type'])
			{
				case 'text':
					$html = '<input type="text" name="field_'.$f_id.'" value="'.$value.'" />';
					break;
				case 'radio':
					$html = '';
					$options = explode("\n", trim($f['options']));
					if (sizeof($options) > 0)
					{
						foreach ($options as $o)
						{
							$value   = str_replace('"', '\"', $value);
							$o_value = str_replace('"', '\"', $o);
							$checked = ($value == $o_value) ? 'checked="checked"' : '';
							
							$html .= '<input type="radio" name="field_'.$f_id.'" value="'.$o.'" '.$checked.' /> ' . $o;
						}
					}
					break;
				case 'dropdown':
					$html = '<select name="field_'.$f_id.'">';
					$options = explode("\n", trim($f['options']));
					if (sizeof($options) > 0)
					{
						foreach ($options as $o)
						{
							$value   = str_replace('"', '\"', $value);
							$o_value = str_replace('"', '\"', $o);
							$selected = ($value == $o_value) ? 'selected="selected"' : '';
							
							$html .= '<option value="'.$o_value.'" '.$selected.'>'.$o.'</option>';
						}
					}
					$html .= '</select>';
					break;
				case 'checkbox':
					$checked = ($value == 1) ? 'checked="checked"' : '';
					$html = '<input type="checkbox" name="field_'.$f_id.'" value="1" '.$checked.' />';
					break;
				case 'check_list':
					$html = '';
					$options = explode("\n", trim($f['options']));
					if (!is_array($value)) { $value = array(); }
					
					if (sizeof($options) > 0)
					{
						foreach ($options as $o)
						{
							$o_value = str_replace('"', '\"', $o);
							$checked = (in_array($o, $value)) ? 'checked="checked"' : '';
							
							$html .= '<input type="checkbox" name="field_'.$f_id.'[]" value="'.$o_value.'" '.$checked.' /> ' . $o . '<br />';
						}
					}
					break;
				case 'terms':
					$checked = ($value == 1) ? 'checked="checked"' : '';
					$html = '<textarea class="terms" readonly="readonly">'.$f['options'].'</textarea><br />';
					$html .= '<input type="checkbox" name="field_'.$f_id.'" value="1" '.$checked.' /> ' . $f['caption'];
					break;
				case 'lg_text':
					$html = '<textarea class="text" name="field_'.$f_id.'">'.$value.'</textarea>';
					break;
				case 'date':
					$html = 'Month: <input type="text" name="field_'.$f_id.'[month]" size="2" maxlength="2" value="'.$value['month'].'" /> ';
					$html .= 'Day: <input type="text" name="field_'.$f_id.'[day]" size="2" maxlength="2" value="'.$value['day'].'" /> ';
					$html .= 'Year: <input type="text" name="field_'.$f_id.'[year]" size="4" maxlength="4" value="'.$value['year'].'" />';
					break;
				case 'info':
					$html = ''; // blank
					break;
			}
			
			$item['html'] = $html;
			$return[] = $item;
		}
	}
	return $return;
}

function Pico_SubmitPaypalRequest($test_mode, $curl_post)
{
	$pp_url = ($test_mode == 1) ? 'https://api-3t.sandbox.paypal.com/nvp' : 'https://api-3t.paypal.com/nvp';
	
	$ch = curl_init($pp_url);
	curl_setopt($ch, CURLOPT_POST, 1); // set POST method
	curl_setopt($ch, CURLOPT_RETURNTRANSFER,1);
	
	$curl_post_line = '';
	foreach ($curl_post as $key=>$val)
	{
		if (strlen($curl_post_line) > 0)
		{
			$curl_post_line .= '&';
		}
		//$val = urlencode($val);
		$curl_post_line .= "$key=$val";
	}

	curl_setopt($ch, CURLOPT_POSTFIELDS, $curl_post_line);
	
	$result = curl_exec($ch); // run the whole process
	curl_close($ch); 

	$results = explode('&', $result);
	$pp_response = array();
	foreach ($results as $result)
	{
		list($key, $val) = explode('=', $result);
		$val = urldecode($val);
		$pp_response[$key] = $val;
	}
	
	return $pp_response;
}

function Pico_GetUserGroupId($user_id)
{
	global $db;
	
	$group_table = DB_PREFIX . 'pico_groups';
	$groups      = $db->force_multi_assoc('SELECT * FROM `'.$group_table.'` ORDER BY `name` ASC');
	
	if (is_array($groups))
	{
		foreach ($groups as $group)
		{
			$users = explode(',', $group['users']);
			if (in_array($user_id, $users))
			{
				$group_id = $group['group_id'];
				return $group_id;
			}
		}
	}
	
	return FALSE;
}

function Pico_GetUserProfileId($user_id)
{
	global $db;
	
	$group_table = DB_PREFIX . 'pico_groups';
	$groups      = $db->force_multi_assoc('SELECT * FROM `'.$group_table.'` ORDER BY `name` ASC');
	
	if (is_array($groups))
	{
		foreach ($groups as $group)
		{
			$users = explode(',', $group['users']);
			if (in_array($user_id, $users))
			{
				$profile_id = $group['profile_id'];
				break;
			}
		}
	}
	
	if (!is_numeric($profile_id))
	{
		return FALSE;
	}
	else
	{
		return $profile_id;
	}
}

function Pico_GetProfileBillingField($profile_id, $billing_type)
{
	global $db;
	$profile_fields = DB_PREFIX . 'user_profile_fields';
	$field_id = $db->result('SELECT `field_id` FROM `'.$profile_fields.'` WHERE `profile_id`=? AND `billing`=? LIMIT 1', $profile_id, $billing_type);
	
	if (is_numeric($field_id))
	{
		$field_name = 'field_' . $field_id;
		return $field_name;
	}
	else
	{
		return false;
	}
}

function Pico_SubmitAuthnetPayment($total, $cc_num, $cc_month, $cc_year, $cc_ccv, $first_name, $last_name, $address, $zip_code, $company = '', $order_name = 'Online Cart Order')
{
	global $db;
	// get pico payment settings
	$payment_settings = DB_PREFIX . 'pico_payment_settings';
	$settings         = $db->assoc('SELECT * FROM `'.$payment_settings.'`');
	
	$login = $settings['authnet_api_login_id'];
	$key   = $settings['authnet_api_transaction_key'];

	if ($settings['test_mode'])
	{
		//$post_url = "https://test.authorize.net/gateway/transact.dll";
		$post_url = "https://secure.authorize.net/gateway/transact.dll";
		$test_r = 1;
	}
	else
	{
		$post_url = "https://secure.authorize.net/gateway/transact.dll";
		$test_r = 0;
	}
	
	$post_values = array(
		// the API Login ID and Transaction Key must be replaced with valid values
		"x_login"			=> $login,
		"x_tran_key"		=> $key,
		"x_customer_ip"		=> getenv('REMOTE_ADDR'),

		//5GzGr6PK3pa3B84W

		"x_version"			=> "3.1",
		"x_delim_data"		=> "TRUE",
		"x_delim_char"		=> "|",
		"x_relay_response"	=> "FALSE",

		"x_type"			=> "AUTH_CAPTURE",
		"x_method"			=> "CC",
		"x_card_num"		=> $cc_num,
		"x_exp_date"		=> $cc_month . $cc_year,
		"x_card_code"		=> $cc_ccv,

		"x_amount"			=> $total,
		"x_description"		=> $order_name,

		"x_first_name"		=> $first_name,
		"x_last_name"		=> $last_name,
		"x_company"			=> $company,
		"x_zip"				=> $zip_code,
		"x_address"			=> $address
	);
	
	if ($test_mode)
	{
		$post_values['x_test_request'] = 1;
	}
	
	// This section takes the input fields and converts them to the proper format
	// for an http post.  For example: "x_login=username&x_tran_key=a1B2c3D4"
	$post_string = "";
	foreach( $post_values as $key => $value )
		{ $post_string .= "$key=" . urlencode( $value ) . "&"; }
	$post_string = rtrim( $post_string, "& " );

	// This sample code uses the CURL library for php to establish a connection,
	// submit the post, and record the response.
	// If you receive an error, you may want to ensure that you have the curl
	// library enabled in your php configuration
	$request = curl_init($post_url); // initiate curl object
		curl_setopt($request, CURLOPT_HEADER, 0); // set to 0 to eliminate header info from response
		curl_setopt($request, CURLOPT_RETURNTRANSFER, 1); // Returns response data instead of TRUE(1)
		curl_setopt($request, CURLOPT_POSTFIELDS, $post_string); // use HTTP POST to send form data
		curl_setopt($request, CURLOPT_SSL_VERIFYPEER, FALSE); // uncomment this line if you get no gateway response.
		$post_response = curl_exec($request); // execute curl post and store results in $post_response
		// additional options may be required depending upon your server configuration
		// you can find documentation on curl options at http://www.php.net/curl_setopt
	//curl_close ($request); // close curl object

	// This line takes the response and breaks it into an array using the specified delimiting character
	$response = explode($post_values["x_delim_char"],$post_response);

	// more info about the response found here: http://developer.authorize.net/guides/AIM/Transaction_Response/Fields_in_the_Payment_Gateway_Response.htm
	
	return $response;
	
	/*
	if ($response[0] == 1)
	{
		// approved
		$order_message .= 'Authorize.net approved this transaction in the amount of '.$response[9].'. (Transaction id ' . $response[6] .')';
		$order_success = TRUE;
	}
	elseif ($response[0] == 2)
	{
		// declined
		$order_output .= '<h1>Credit Card Declined</h1> Your information was declined. Please try again. If the problem persists please contact us.';

	}
	elseif ($response[0] == 3)
	{
		// error
		$order_output .= '<h1>Credit Card Error</h1> There was an error processing your information. Please try again later. If the problem persists please contact us.';
	}
	elseif ($response[0] == 4)
	{
		// held
		$order_message .= 'Warning, this order was put on a PENDING status from Authorize.net! Please verify the transaction was completed before continuing! (Transaction id ' . $response[6] .')';
		$order_success = TRUE;
	}*/
}

?>