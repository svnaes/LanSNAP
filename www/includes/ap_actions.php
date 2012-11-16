<?php
chdir('../');
if ( (isset($_GET['page_id'])) and (is_numeric($_GET['page_id'])) )
{
	define('CURRENT_PAGE', $_GET['page_id']);
}
require_once('core.php');
$action = $_REQUEST['ap_action'];

if (USER_ACCESS < 3) { exit(); }

if ($action == 'move_content')
{
	$component_id = $_GET['component_id'];
	$page_id      = $_GET['page_id'];
	$destination  = $_GET['destination'];
	
	$parts = explode('_', $destination);
	
	$info = $db->assoc('SELECT `location`, `position` FROM `'.DB_CONTENT_LINKS.'` WHERE `page_id`=? AND `component_id`=?', $page_id, $component_id);
	$current_position  = $info['position'];
	$current_location  = $info['location'];
	
	if (preg_match('/move\_\d+/', $destination))
	{
		$destination = array_pop($parts);
		$destination_info = $db->assoc('SELECT `location`, `position` FROM `'.DB_CONTENT_LINKS.'` WHERE `page_id`=? AND `component_id`=?', $page_id, $destination);
		$destination_position = $destination_info['position'];
		
		$destination_location = $destination_info['location'];
		
		//$destination_position = (int) $destination_position;
		//$current_position     = (int) $current_postion;
		
		//echo "destination: $destination_position\ncurrent: $current_position\n|";
		// if destination is numeric it is moving to somewhere in the same container
		
		//echo "$current_location\n$destination_location\n";
		
		if ($current_location != $destination_location)
		{
			$result = $db->run('UPDATE `'.DB_CONTENT_LINKS.'` SET `position`=(`position`-1) WHERE `position` > ? AND `page_id`=? AND `location`=?', $current_position, $page_id, $current_location);
			$result = $db->run('UPDATE `'.DB_CONTENT_LINKS.'` SET `position`=(`position`+1) WHERE `position`>=? AND `page_id`=? AND `location`=?', $destination_position, $page_id, $destination_location);
			$result = $db->run('UPDATE `'.DB_CONTENT_LINKS.'` SET `position`=?, `location`=? WHERE `page_id`=? AND `component_id`=?', $destination_position, $destination_location, $page_id, $component_id);
			echo $current_location . '|' . $destination_location;
		}
		else
		{
			if ($destination_position < $current_position)
			{
				$result = $db->run('UPDATE `'.DB_CONTENT_LINKS.'` SET `position`=(`position`+1) WHERE `position`<? AND `position`>=? AND `page_id`=? AND `location`=?', $current_position, $destination_position, $page_id, $current_location);
				//if ($result) { echo '!' . $db->rows; } else { echo '?' . $db->error; }
				//echo "|";
			}
			elseif ($destination_position > $current_position) // do nothing if they are the same defeats the purpose of why we are here
			{
				$destination_position--;
				//$result = $db->run('UPDATE `'.DB_CONTENT_LINKS.'` SET `position`=(`position`+1) WHERE `position`>=? AND `page_id`=? AND `location`=?', $destination, $page_id, $current_location);
				$result = $db->run('UPDATE `'.DB_CONTENT_LINKS.'` SET `position`=(`position`-1) WHERE `position` <= ? AND `position` > ? AND `page_id`=? AND `location`=?', $destination_position, $current_position, $page_id, $current_location);
			}
			$result = $db->run('UPDATE `'.DB_CONTENT_LINKS.'` SET `position`=? WHERE `page_id`=? AND `component_id`=?', $destination_position, $page_id, $component_id);
			echo $current_location;
		}
	}
	else
	{
		// else its moving to the bottom of a container
		array_shift($parts);
		
		$destination = implode('_', $parts);
		$destination_info = $db->assoc('SELECT `location`, `position` FROM `'.DB_CONTENT_LINKS.'` WHERE `page_id`=? AND `component_id`=?', $page_id, $destination);
		
		$result = $db->run('UPDATE `'.DB_CONTENT_LINKS.'` SET `position`=(`position`-1) WHERE `position`>? AND `page_id`=? AND `location`=?', $current_position, $page_id, $current_location);
		$new_position = $db->result('SELECT `position` FROM `'.DB_CONTENT_LINKS.'` WHERE `page_id`=? AND `location`=? ORDER BY `position` DESC LIMIT 1', $page_id, $destination);
		if ($new_position === FALSE) { $new_position = 0; } else { $new_position = $new_position + 1; }
		$result = $db->run('UPDATE `'.DB_CONTENT_LINKS.'` SET `position`=?, `location`=? WHERE `page_id`=? AND `component_id`=?', $new_position, $destination, $page_id, $component_id);
		
		if ($current_location != $destination)
		{
			echo $current_location . '|' . $destination;
		}
		else
		{
			echo $current_location;
		}
	}
	
	exit();
}

if ($action == 'delete_content')
{
	$component_id = $_POST['component_id'];
	$page_id      = $_POST['page_id'];
	
	if ($_POST['delete_action'] == 0)
	{
		// delete from the links table where page id = ?
		
		$info = $db->assoc('SELECT `location`, `position` FROM `'.DB_CONTENT_LINKS.'` WHERE `page_id`=? AND `component_id`=?', $page_id, $component_id);
		
		if ($info != FALSE)
		{
			$location = $info['location'];
			$position = $info['position'];
			$repos  = $db->run('UPDATE `'.DB_CONTENT_LINKS.'` SET `position`=(`position`-1) WHERE `page_id`=? AND `location`=? AND `position`>?', $page_id, $location, $position);
			$delete = $db->run('DELETE FROM `'.DB_CONTENT_LINKS.'` WHERE `component_id`=? AND `page_id`=? LIMIT 1', $component_id, $page_id);
		}
	}
	else
	{
		// we have to remove all occurances of this component
		
		$links = $db->force_multi_assoc('SELECT * FROM `'.DB_CONTENT_LINKS.'` WHERE `component_id`=?', $component_id);
		foreach ($links as $link)
		{
			$location = $link['location'];
			$position = $link['position'];
			$page_id  = $link['page_id'];
			
			$repos  = $db->run('UPDATE `'.DB_CONTENT_LINKS.'` SET `position`=(`position`-1) WHERE `page_id`=? AND `location`=? AND `position`>?', $page_id, $location, $position);
			$delete = $db->run('DELETE FROM `'.DB_CONTENT_LINKS.'` WHERE `component_id`=? AND `page_id`=? LIMIT 1', $component_id, $page_id);
		}
		
		if ($_POST['delete_action'] == 2)
		{
			// clear out the instances and component id's
			$db->run('DELETE FROM `'.DB_CONTENT.'` WHERE `component_id`=?', $component_id);
			$db->run('DELETE FROM `'.DB_COMPONENT_TABLE.'` WHERE `component_id`=?', $component_id);
		}
	}
	exit();
}

if ($action == 'update_component')
{
	$component_id = $_POST['component_id'];
	$access       = $_POST['access'];
	$edit_lock    = (isset($_POST['edit_lock'])) ? $_POST['edit_lock'] : 0;
	$delete_lock  = (isset($_POST['delete_lock'])) ? $_POST['delete_lock'] : 0;
	$desc         = stripslashes($_POST['description']);
	
	$result = $db->run('UPDATE `'.DB_COMPONENT_TABLE.'` SET `access`=?, `edit_lock`=?, `description`=?, `delete_lock`=? WHERE `component_id`=? LIMIT 1', $access, $edit_lock, $desc, $delete_lock, $component_id);
	if (!$result) { echo $db->error; }
	exit();
}

if ($action == 'save_js')
{
	$component_id = $_POST['component_id'];
	$js           = stripslashes($_POST['js']);
	
	$result = $db->run('UPDATE `'.DB_COMPONENT_TABLE.'` SET `javascript`=? WHERE `component_id`=? LIMIT 1', $js, $component_id);
	if (!$result) { echo $db->error; }
	exit();
}

if ($action == 'save_css')
{
	$component_id = $_POST['component_id'];
	$css          = stripslashes($_POST['css']);
	
	$result = $db->run('UPDATE `'.DB_COMPONENT_TABLE.'` SET `css`=? WHERE `component_id`=? LIMIT 1', $css, $component_id);
	if (!$result) { echo $db->error; }
	exit();
}

if ($action == 'reload_container')
{
	$component_id = $_GET['component_id'];
	$req_uri      = urldecode($_REQUEST['ru']);
	$page_id      = $_GET['page_id'];
	
	echo GetComponent($component_id, $page_id, $req_uri);
	exit();
}

if ($action == 'reload_column')
{
	$column  = urldecode($_GET['column']);
	$req_uri = urldecode($_REQUEST['ru']);
	$page_id = $_GET['page_id'];

	// get parent location from component id
	echo GetContent($column, $page_id, $req_uri);
}

if ($action == 'get_scripts')
{
	$component_id = $_GET['component_id'];
	if (!is_numeric($component_id)) { exit(); }

	$component_details = $db->assoc('SELECT * FROM `'.DB_COMPONENT_TABLE.'` WHERE `component_id`=?', $component_id);
	if ($component_details != FALSE)
	{
		$folder = $component_details['folder'];
		$options = GetContentOptions($folder);
		
		$files = array();
		
		$static_file = 'includes/content/'.$folder.'/'.$options['static_js'];
		if ( (file_exists($static_file)) and (!is_dir($static_file)) )
		{
			$static_url = $body->url($static_file);
			$files[]    = $static_url;
		}
		
		/*
		$static_file = 'includes/content/'.$folder.'/'.$options['admin_js'];
		if ( (file_exists($static_file)) and (!is_dir($static_file)) )
		{
			$static_url = $body->url($static_file);
			$files[]    = $static_url;
		}*/
		
		$static_file = 'includes/content/'.$folder.'/'.$options['static_css'];
		if ( (file_exists($static_file)) and (!is_dir($static_file)) )
		{
			$static_url = $body->url($static_file);
			$files[]    = $static_url;
		}
		
		/*
		$static_file = 'includes/content/'.$folder.'/'.$options['admin_css'];
		if ( (file_exists($static_file)) and (!is_dir($static_file)) )
		{
			$static_url = $body->url($static_file);
			$files[]    = $static_url;
		}*/
		
		$output = implode('|', $files);
		echo $output;
	}
	exit();
}

if ($action == 'add_content')
{
	if (USER_ACCESS < 3) { exit(); }
	$location     = $_POST['location'];
	$access       = $_POST['access'];
	$description  = stripslashes($_POST['description']);
	$folder       = $_POST['content_type'];
	$page_id      = $_POST['page_id'];
	$view_setting = $_POST['view_setting'];
	$req_uri      = urldecode($_POST['ru']);
	$continue     = FALSE;
	
	if ( (!isset($_POST['copy_component'])) or (!is_numeric($_POST['copy_component'])) )
	{
		$component_id = $db->insert('INSERT INTO `'.DB_COMPONENT_TABLE.'` (`access`, `view_setting`, `folder`, `description`) VALUES (?,?,?,?)',
			$access, $view_setting, $folder, $description
		);
	}
	else
	{
		$component_id      = $_POST['copy_component'];
		$component_details = $db->assoc('SELECT * FROM `'.DB_COMPONENT_TABLE.'` WHERE `component_id`=?', $component_id); 
		$folder            = $component_details['folder'];
		$view_setting      = $component_details['view_setting'];
	}
	
	if (is_numeric($component_id))
	{
		$instance_id = GenerateInstanceID($component_id, $view_setting, $page_id, $req_uri);
		echo '<input type="hidden" id="js_instance_id" value="'.$instance_id.'" />'; // to reload javascript properly
		echo '<input type="hidden" id="js_component_id" value="'.$component_id.'" />'; // to reload javascript properly
		echo '<input type="hidden" id="js_location" value="'.$location.'" />'; // to reload javascript properly
		//echo $instance_id;
		
		// get position
		$position = $db->result('SELECT `position` FROM `'.DB_CONTENT_LINKS.'` WHERE `page_id`=? AND `location`=? ORDER BY `position` DESC LIMIT 1', $page_id, $location);
		if ($position === FALSE) { $position = 0; } else { $position = $position + 1; }
		// add it to the compontent links table
		
		$insert = $db->run('INSERT INTO `'.DB_CONTENT_LINKS.'` (`page_id`, `component_id`, `position`, `location`) VALUES (?,?,?,?)', $page_id, $component_id, $position, $location);
		if ($insert)
		{
			// see if we have an instance in there yet
			CheckInstanceID($component_id, $instance_id);
			
			// see if this has a separate install, if so, include it, else bring up the edit screen
			$options      = GetContentOptions($folder);
			$install_file = 'includes/content/'.$folder.'/'.$options['install_file'];
			$edit_file    = 'includes/content/'.$folder.'/'.$options['edit_file'];
			if ( (file_exists($install_file)) and (!is_dir($install_file)) )
			{
				include($install_file);
			}
			elseif ( (file_exists($edit_file)) and (!is_dir($edit_file)) )
			{
				$continue = TRUE;
				$action   = 'load_edit';
				//include($edit_file);
			}
			else
			{
				echo 'Content added!';
			}
		}

	}
	if (!$continue)
	{
		exit();
	}
}

if ($action == 'load_edit')
{
	$component_id = (!isset($component_id)) ? $_GET['component_id'] : $component_id;
	$page_id      = (!isset($page_id)) ? $_GET['page_id'] : $page_id;
	$req_uri      = (!isset($req_uri)) ? urldecode($_GET['ru']) : $req_uri;

	$component_details = $db->assoc('SELECT * FROM `'.DB_COMPONENT_TABLE.'` WHERE `component_id`=?', $component_id);
	$view_setting      = $component_details['view_setting'];
	$instance_id       = GenerateInstanceID($component_id, $view_setting, $page_id, $req_uri);
	$folder            = $component_details['folder'];
	$options           = GetContentOptions($folder);
	
	$edit_file    = 'includes/content/'.$folder.'/'.$options['edit_file'];
	if ( (file_exists($edit_file)) and (!is_dir($edit_file)) )
	{
		if ((isset($options['on_edit_load'])) and (strlen($options['on_edit_load']) > 0) )
		{
			echo '<input type="hidden" id="on_edit_load" value="'.$options['on_edit_load'].'" />';
		}
		if ((isset($options['on_ap_close'])) and (strlen($options['on_ap_close']) > 0) )
		{
			echo '<input type="hidden" id="on_ap_close" value="'.$options['on_ap_close'].'" />';
		}
		echo '<table id="edit_table" cellpadding="0" cellspacing="0">
		<tr><td valign="top" id="ap_edit_right">';
		
		echo '<div id="co_main" class="co_hidden" style="display: block">';
		if ( ($component_details['edit_lock'] == 0) or (USER_ACCESS >= 4) )
		{
			include($edit_file);
		}
		else
		{
			echo 'This component has been locked by the site administrator';
		}
		echo '</div>';
		
		if (USER_ACCESS >= 4)
		{
			$css_text = (strlen($component_details['css']) > 0) ? $component_details['css'] : "/* --- CSS for Component $component_id --- */\n";
		
			$css = '
			<form method="post" action="'.$body->url('includes/ap_actions.php').'" onsubmit="Pico_SaveCSS(this); return false">
			<input type="hidden" name="ap_action" value="save_css" />
			<input type="hidden" name="component_id" value="'.$component_id.'" />
			<input type="hidden" name="css" value="" />
			<textarea name="ta_css_edit" id="ta_css_edit" style="width: 100%; height: 95%" class="codepress css linenumbers-on">'.$css_text.'</textarea><br />
			<input type="submit" value="Save" />
			</form>';
			
			$js = '
			<form method="post" action="'.$body->url('includes/ap_actions.php').'" onsubmit="Pico_SaveJS(this); return false">
			<input type="hidden" name="ap_action" value="save_js" />
			<input type="hidden" name="component_id" value="'.$component_id.'" />
			<input type="hidden" name="js" value="" />
			<textarea name="ta_js_edit" id="ta_js_edit" style="width: 100%; height: 95%" class="codepress css linenumbers-on">'.$component_details['javascript'].'</textarea><br />
			<input type="submit" value="Save" />
			</form>';
			
			$settings = '
			<div>Component ID: '.$component_id.'</div>
			<div>Instance ID: '.$instance_id.'</div>
			
			<form method="post" action="'.$body->url('includes/ap_actions.php').'" onsubmit="Pico_UpdateComponent(this); return false">
			<input type="hidden" name="ap_action" value="update_component" />
			<input type="hidden" name="component_id" value="'.$component_id.'" />

			<table border="0" cellpadding="2" cellspacing="1">
			<tr>
				<td class="bold">Description</td>
				<td><input type="text" name="description" class="ap_text" value="'.$component_details['description'].'" /></td>
			</tr>
			<tr>
				<td class="bold">Required Access</td>
				<td>'.AccessDrop('access', $component_details['access']).'</td>
			</tr>
			<tr>
				<td class="bold">Edit Lock</td>
				<td><input type="checkbox" name="edit_lock" value="1" '.($component_details['edit_lock']==1?'checked="checked" ':'').'/></td>
			</tr>
			<tr>
				<td class="bold">Delete Lock</td>
				<td><input type="checkbox" name="delete_lock" value="1" '.($component_details['delete_lock']==1?'checked="checked" ':'').'/></td>
			</tr>
			</table>
			
			<input type="submit" value="Update" />
			</form>';
		}
		else
		{
			$css      = 'You do not have access to this section.';
			$js       = 'You do not have access to this section.';
			$settings = 'You do not have access to this section.';
		}
		
		
		
		// all the other hidden things need to go here (javascript, css, settings)
		echo '<div id="co_css" class="co_hidden">'.$css.'</div>';
		echo '<div id="co_js" class="co_hidden">'.$js.'</div>';
		echo '<div id="co_display" class="co_hidden">'.$settings.'</div>';
		
		$lower = '';
		if ( (is_array($options['edit_options'])) and (sizeof($options['edit_options']) > 0) )
		{
			foreach ($options['edit_options'] as $option)
			{
				if (USER_ACCESS >= $option['access'])
				{
					echo '<div id="'.$option['container'].'" class="co_hidden">';
					$inc_file = 'includes/content/'.$folder.'/'.$option['inc_file'];
					if ( (file_exists($inc_file)) and (!is_dir($inc_file)) )
					{
						include($inc_file);
					}
					// include the file
					echo '</div>';
					
					$later .= '<li class="click" onclick="Pico_COShow(\''.$option['container'].'\', this)">'.$option['link_text'].'</li>';
				}
			}
		}
		
		
		echo '</td><td width="150" valign="top">';
		// menu options
		echo '<div id="co_title">Options</div>';
		echo '<ul id="component_options">';
		echo '<li class="click" onclick="Pico_COShow(\'co_main\', this)">Content</li>';
		echo '<li class="click" onclick="Pico_COShow(\'co_css\', this, Pico_LoadCssEditor)">CSS</li>';
		echo '<li class="click" onclick="Pico_COShow(\'co_js\', this, Pico_LoadJsEditor)">Javascript</li>';
		echo '<li class="click" onclick="Pico_COShow(\'co_display\', this)">Display Settings</li>';
		echo $later;
		echo '</td></tr></table>';
	}
	exit();
}


if ($action == 'delete_user')
{
	$delete_user = $db->assoc('SELECT * FROM `'.DB_USER_TABLE.'` WHERE `id`=?', $_GET['user_id']);
	
	if ( ($delete_user['access'] <= USER_ACCESS) and ($delete_user['id'] != USER_ID) )
	{
		$db->run('DELETE FROM `'.DB_USER_TABLE.'` WHERE `id`=? LIMIT 1', $_GET['user_id']);
	}
	exit();
}

if ($action == 'delete_page')
{
	$page_to_delete = $_POST['page_id'];
	$result = $db->run('DELETE FROM `'.DB_PAGES_TABLE.'` WHERE `page_id`=? LIMIT 1', $page_to_delete);
	if ($result != FALSE)
	{
		if (is_numeric($_POST['new_home_page']))
		{
			$db->run('UPDATE `'.DB_PAGES_TABLE.'` SET `is_default`=1 WHERE `page_id`=?', $_POST['new_home_page']);
		}
		
		// delete from the content-links table
		$db->run('DELETE FROM `'.DB_CONTENT_LINKS.'` WHERE `page_id`=?', $page_to_delete);
	}
	exit();
}

if ($action == 'edit_user')
{
	
	$a = $_POST['access'];
	$f = $_POST['first_name'];
	$l = $_POST['last_name'];
	$e = $_POST['email_address'];
	
	$user_id = $_POST['user_id'];
	
	$result = $db->run('UPDATE `'.DB_USER_TABLE.'` SET 
		`access`=?,
		`email_address`=?,
		`first_name`=?,
		`last_name`=? WHERE `id`=?',
		$a, $e, $f, $l, $user_id
	);
	
	if ($result == FALSE) { echo 'SQL ERROR: ' . $db->error; }
	
	if ( (strlen($_POST['password']) > 5) and ($_POST['password'] != '000000') )
	{
		$p = md5($_POST['password']);
		$result = $db->run('UPDATE `'.DB_USER_TABLE.'` SET `password`=? WHERE `id`=?', $p, $user_id);
	}
	
	exit();
}

if ($action == 'add_user')
{
	$u = $_POST['username'];
	$p = md5($_POST['password']);
	$a = $_POST['access'];
	$f = $_POST['first_name'];
	$l = $_POST['last_name'];
	$e = $_POST['email_address'];
	
	$result = $db->run('INSERT INTO `'.DB_USER_TABLE.'` (`username`, `password`, `access`, `email_address`, `first_name`, `last_name`) VALUES (?,?,?,?,?,?)',
		$u, $p, $a, $e, $f, $l
	);
	
	if ($result == FALSE) { echo 'SQL ERROR: ' . $db->error; }
	exit();
}

if ($action == 'check_user')
{
	$username = urldecode($_GET['username']);
	$count = $db->result('SELECT count(*) FROM `'.DB_USER_TABLE.'` WHERE `username`=?', $username);
	$return = ($count == 0) ? 'GOOD' : 'BAD';
	echo $return;
	exit();
}

if ($action == 'edit_page')
{
	$p = $_POST['page'];
	$c = $_POST['current_page'];
	
	foreach ($p as $key=>$val)
	{
		if (is_string($val))
		{
			$p[$key] = stripslashes($val);
		}
	}
	
	$p['name'] = stripslashes($p['name']);
	
	$alias = PageNameToAlias($p['name']);
	
	if (is_array($_POST['group_access']))
	{
		$groups = $_POST['group_access'];
		$g = (sizeof($groups) > 0) ? implode(',', $groups) : null;
	}
	else
	{
		$g = null;
	}
	
	$db->run('UPDATE `'.DB_PAGES_TABLE.'` SET `name`=?, `alias`=?, `theme`=?, `www_title`=?, `keywords`=?, `description`=?, `user_access`=?, `groups`=? WHERE `page_id`=?', 
		$p['name'], $alias, $p['theme'], $p['www_title'], $p['keywords'], $p['description'], $p['user_access'], $g, $c);
	
	if ($p['is_default'] == 1)
	{
		$db->run('UPDATE `'.DB_PAGES_TABLE.'` SET `is_default`=?', 0);
		$db->run('UPDATE `'.DB_PAGES_TABLE.'` SET `is_default`=1 WHERE `page_id`=?', $c);
	}
	
	echo $alias;
	exit();
}

if ($action == 'bulk_clone')
{
	$page_id = $_POST['page_id'];
	$ptc     = $_POST['pages_to_clone'];
	$data    = explode("\n", $ptc);
	$new_pages = array();
	
	foreach ($data as $potential_page)
	{
		$potential_page = trim($potential_page);
		if (strlen($potential_page) > 0)
		{
			// check to make sure that it doesnt exist
			$potential_alias = PageNameToAlias($potential_page);
			$count = $db->result('SELECT count(*) FROM `'.DB_PAGES_TABLE.'` WHERE `name`=? OR `alias`=?', $potential_page, $potential_alias);
			if ($count == 0)
			{
				$new_pages[$potential_alias] = $potential_page;
			}
		}
	}
	
	if (sizeof($new_pages) > 0)
	{
		$page_details = $db->assoc('SELECT * FROM `'.DB_PAGES_TABLE.'` WHERE `page_id`=?', $page_id);
		foreach ($new_pages as $alias => $name)
		{
			$new_page_id = $db->insert('INSERT INTO `'.DB_PAGES_TABLE.'` (`name`, `alias`, `theme`, `www_title`, `keywords`, `description`, `user_access`, `is_default`, `groups`) VALUES (?,?,?,?,?,?,?,?,?)', 
				$name, $alias, $page_details['theme'], $page_details['www_title'], $page_details['keywords'], $page_details['description'], $page_details['user_access'], 0, $page_details['groups']
			);
			if ($new_page_id != FALSE)
			{
				$link_info = $db->force_multi_assoc('SELECT * FROM `'.DB_CONTENT_LINKS.'` WHERE `page_id`=?', $page_id);
				if (sizeof($link_info) > 0)
				{
					foreach($link_info as $info)
					{
						$check = $db->run('INSERT INTO `'.DB_CONTENT_LINKS.'` (`page_id`, `component_id`, `position`, `location`) VALUES (?,?,?,?)', $new_page_id, $info['component_id'], $info['position'], $info['location']);
					}
				}
			}
		}
		echo $page_details['alias'];
	}
	exit();
}

if ($action == 'clone_page')
{
	$page_id = $_POST['page_id'];
	$p       = $_POST['page'];
	$alias   = PageNameToAlias($p['name']);
	
	$page_details = $db->assoc('SELECT * FROM `'.DB_PAGES_TABLE.'` WHERE `page_id`=?', $page_id);
	
	if ($page_details != FALSE)
	{
		$new_page_id = $db->insert('INSERT INTO `'.DB_PAGES_TABLE.'` (`name`, `alias`, `theme`, `www_title`, `keywords`, `description`, `user_access`, `is_default`, `groups`) VALUES (?,?,?,?,?,?,?,?,?)', 
			$p['name'], $alias, $page_details['theme'], $page_details['www_title'], $page_details['keywords'], $page_details['description'], $page_details['user_access'], 0, $page_details['groups']
		);
		
		if ($new_page_id != FALSE)
		{
			$link_info = $db->force_multi_assoc('SELECT * FROM `'.DB_CONTENT_LINKS.'` WHERE `page_id`=?', $page_id);
			if (sizeof($link_info) > 0)
			{
				foreach($link_info as $info)
				{
					$check = $db->run('INSERT INTO `'.DB_CONTENT_LINKS.'` (`page_id`, `component_id`, `position`, `location`) VALUES (?,?,?,?)', $new_page_id, $info['component_id'], $info['position'], $info['location']);
					//if ($check == FALSE) { echo $db->error . "\n"; }
				}
			}
			echo $alias;
		}
	}
	exit();
}

if ($action == 'add_page')
{
	$p = $_POST['page'];
	$c = $_POST['current_page'];
	
	$alias = PageNameToAlias($p['name']);
	
	if ($p['is_default'] == 1)
	{
		$db->run('UPDATE `'.DB_PAGES_TABLE.'` SET `is_default`=?', 0);
	}
	else
	{
		$p['is_default'] = 0;
	}
	
	if (is_array($_POST['group_access']))
	{
		$groups = $_POST['group_access'];
		$g = (sizeof($groups) > 0) ? implode(',', $groups) : null;
	}
	else
	{
		$g = null;
	}
	
	$check = $db->run('INSERT INTO `'.DB_PAGES_TABLE.'` (`name`, `alias`, `theme`, `www_title`, `keywords`, `description`, `user_access`, `is_default`, `groups`) VALUES (?,?,?,?,?,?,?,?,?)', 
		$p['name'], $alias, $p['theme'], $p['www_title'], $p['keywords'], $p['description'], $p['user_access'], $p['is_default'], $g
	);
	
	echo $alias;
	exit();
}
if ($action == 'check_page')
{
	$page_name = urldecode($_GET['page_name']);
	$alias     = PageNameToAlias($page_name);
	
	if ( (strlen($page_name) == 0) or (strlen($alias) == 0) )
	{
		echo 'BAD';
		exit();
	}
	
	$page_id   = $db->result('SELECT `page_id` FROM `'.DB_PAGES_TABLE.'` WHERE `name`=? OR `alias`=?', $page_name, $alias);
	if ($page_id == FALSE)
	{
		echo 'GOOD';
		exit();
	}
	else
	{
		if ( ($page_id == $_GET['current_page']) and ($_GET['post_action'] == 'edit_page') )
		{
			echo 'GOOD';
			// it's OK to rename the page we are on the same thing :)
			exit();
		}
		else
		{
			echo 'BAD';
			exit();
		}
	}
}
if ($action == 'add_sh_item')
{
	$sh_table  = DB_PREFIX . 'pico_site_heirarchy';
	$parent    = $_POST['parent'];
	
	$item_name = trim(stripslashes($_POST['item_name']));
	$item_url  = trim(stripslashes($_POST['item_url']));
	
	$position  = $db->result('SELECT `position` FROM `'.$sh_table.'` WHERE `parent`=? ORDER BY `position` DESC LIMIT 1', $parent);
	
	if (is_array($_POST['page_ids']))
	{
		$page_ids  = $_POST['page_ids'];
		$new_position = (is_numeric($position)) ? $position + 1 : 0;
		
		foreach ($page_ids as $page_id)
		{
			$db->run('INSERT INTO `'.$sh_table.'` (`page_id`, `text`, `parent`, `external_url`, `position`) VALUES (?,?,?,?,?)',
				$page_id, '', $parent, '', $new_position
			);
			$new_position++;
		}
	}
	else
	{
		$new_position = (is_numeric($position)) ? $position + 1 : 0;
		$db->run('INSERT INTO `'.$sh_table.'` (`page_id`, `text`, `parent`, `external_url`, `position`) VALUES (?,?,?,?,?)',
			0, $item_name, $parent, $item_url, $new_position
		);
	}
}
if ($action == 'sh_item_delete')
{
	$id = $_GET['id'];
	if (!is_numeric($id)) { exit(); }
	DeleteSHItem($id);
}
if ($action == 'sh_item_move')
{
	$id        = $_GET['id'];
	$direction = $_GET['direction'];
	if (!is_numeric($id)) { exit(); }
	$sh_table  = DB_PREFIX . 'pico_site_heirarchy';
	$info      = $db->assoc('SELECT * FROM `'.$sh_table.'` WHERE `entry_id`=?', $id);
	
	$position = $info['position'];
	$new_position = ($direction == 'up') ? $position - 1 : $position + 1;
	
	$move_id = $db->result('SELECT `entry_id` FROM `'.$sh_table.'` WHERE `position`=? AND `parent`=?', $new_position, $info['parent']);
	if (is_numeric($move_id))
	{
		// swap
		$db->run('UPDATE `'.$sh_table.'` SET `position`=? WHERE `entry_id`=?', $new_position, $id);
		$db->run('UPDATE `'.$sh_table.'` SET `position`=? WHERE `entry_id`=?', $position, $move_id);
	}
}
if ($action == 'sh_hide_delete')
{
	$id       = $_GET['id'];
	$sh_table = DB_PREFIX . 'pico_site_heirarchy';
	$info     = $db->assoc('SELECT * FROM `'.$sh_table.'` WHERE `entry_id`=?', $id);
	$new_val  = ($info['show_in_nav'] == 1) ? 0 : 1;
	$db->run('UPDATE `'.$sh_table.'` SET `show_in_nav`=? WHERE `entry_id`=?', $new_val, $id);
}
if ($action == 'sh_unlink_delete')
{
	$id       = $_GET['id'];
	$sh_table = DB_PREFIX . 'pico_site_heirarchy';
	$linked   = $db->result('SELECT `linked` FROM `'.$sh_table.'` WHERE `entry_id`=?', $id);
	
	$new_linked = ($linked == 1) ? 0 : 1;
	$db->run('UPDATE `'.$sh_table.'` SET `linked`=? WHERE `entry_id`=?', $new_linked, $id);
}
// user profiles
if ($action == 'add_user_profile')
{
	$profile_list = DB_PREFIX . 'user_profile_list';
	$profile_name = trim(stripslashes($_POST['profile_name']));
	$db->run('INSERT INTO `'.$profile_list.'` (`name`) VALUES (?)', $profile_name);
}

if ($action == 'edit_user_profile')
{
	$profile_list = DB_PREFIX . 'user_profile_list';
	$profile_name = trim(stripslashes($_POST['profile_name']));
	$db->run('UPDATE `'.$profile_list.'` SET `name`=? WHERE `profile_id`=?', $profile_name, $_POST['edit_id']);
}

if ($action == 'delete_group')
{
	$group_id = $_GET['group_id'];
	$group_table = DB_PREFIX . 'pico_groups';
	$db->run('DELETE FROM `'.$group_table.'` WHERE `group_id`=?', $group_id);
}

if ($action == 'delete_user_profile')
{
	$profile_list = DB_PREFIX . 'user_profile_list';
	$group_table = DB_PREFIX . 'pico_groups';
	// check to make sure this profile is not being used by a group
	$profile_id = $_GET['profile_id'];
	
	$check = $db->result('SELECT count(1) FROM `'.$group_table.'` WHERE `profile_id`=?', $profile_id);
	if ($check != 0)
	{
		echo 'Cannot delete this profile, there are User Groups using it.';
	}
	else
	{
		$db->run('DELETE FROM `'.$profile_list.'` WHERE `profile_id`=?', $profile_id);
	}
	
	// if it is: echo warning, else delete it
}

if (($action == 'add_profile_field') or ($action == 'edit_profile_field'))
{
	foreach ($_POST as $key=>$val)
	{
		$$key = stripslashes(trim($val));
	}
	
	if (strlen($field_name) == 0)
	{
		exit('Please enter a field name');
	}
	
	$profile_list   = DB_PREFIX . 'user_profile_list';
	$profile_fields = DB_PREFIX . 'user_profile_fields';
	
	$profile_value_table = DB_PREFIX . 'user_profile_values_'.$profile_id;
	$check = $db->result('SELECT count(1) FROM `'.$profile_fields.'` WHERE `profile_id`=?', $profile_id);
	if ($check == 0)
	{
		// we need to create the profile value table
		$db->run(<<<SQL
CREATE TABLE IF NOT EXISTS `$profile_value_table` (
	`user_id` BIGINT(11) NOT NULL,
	`created` BIGINT(11) NOT NULL,
	`last_updated` BIGINT(11) NOT NULL,
	PRIMARY KEY(`user_id`)
)
SQL
);
	}
	
	// add field to the profile list
	
	$required = (is_numeric($field_required)) ? $field_required : 0;
	
	if ($action == 'add_profile_field')
	{
		if ($check == 0)
		{
			$position = 0;
		}
		else
		{
			$position = $db->result('SELECT `position` FROM `'.$profile_fields.'` WHERE `profile_id`=? ORDER BY `position` DESC LIMIT 1', $profile_id);
			$position = $position+1;
		}
		
		$field_id = $db->insert('INSERT INTO `'.$profile_fields.'` (`profile_id`, `name`, `type`, `pattern`, `required`, `options`, `caption`, `position`) VALUES (?,?,?,?,?,?,?,?)',
			$profile_id, $field_name, $field_type, $field_pattern, $required, $field_options, $field_caption, $position
		);
		
		$field_name = 'field_' . $field_id;
		
		// add field to the values database
		
		switch ($field_type)
		{
			case 'text':
				$db->run('ALTER TABLE `'.$profile_value_table.'` ADD COLUMN `'.$field_name.'` VARCHAR(255) NOT NULL DEFAULT \'\'');
				break;
			case 'radio':
				$db->run('ALTER TABLE `'.$profile_value_table.'` ADD COLUMN `'.$field_name.'` VARCHAR(255) NOT NULL DEFAULT \'\'');
				break;
			case 'dropdown':
				$db->run('ALTER TABLE `'.$profile_value_table.'` ADD COLUMN `'.$field_name.'` VARCHAR(255) NOT NULL DEFAULT \'\'');
				break;
			case 'checkbox':
				$db->run('ALTER TABLE `'.$profile_value_table.'` ADD COLUMN `'.$field_name.'` TINYINT(1)');
				break;
			case 'check_list':
				$db->run('ALTER TABLE `'.$profile_value_table.'` ADD COLUMN `'.$field_name.'` BLOB');
				break;
			case 'terms':
				$db->run('ALTER TABLE `'.$profile_value_table.'` ADD COLUMN `'.$field_name.'` TINYINT(1)');
				break;
			case 'lg_text':
				$db->run('ALTER TABLE `'.$profile_value_table.'` ADD COLUMN `'.$field_name.'` TEXT');
				break;
			case 'date':
				$db->run('ALTER TABLE `'.$profile_value_table.'` ADD COLUMN `'.$field_name.'` BIGINT(11)');
				break;
		}
	}
	else
	{
		$db->run('UPDATE `'.$profile_fields.'` SET `name`=?, `pattern`=?, `required`=?, `options`=?, `caption`=?, `billing`=? WHERE `field_id`=?',
			$field_name, $field_pattern, $required, $field_options, $field_caption, $field_billing, $field_id
		);
	}
}

if ($action == 'move_profile_field')
{
	$field_id  = $_GET['field_id'];
	$direction = $_GET['direction'];
	
	$profile_fields = DB_PREFIX . 'user_profile_fields';
	
	$field_info   = $db->assoc('SELECT * FROM `'.$profile_fields.'` WHERE `field_id`=?', $field_id);
	$position     = $field_info['position'];
	$new_position = ($direction == 'up') ? $position-1 : $position+1;
	
	$move_id = $db->result('SELECT `field_id` FROM `'.$profile_fields.'` WHERE `profile_id`=? AND `display`=? AND `position`=?',
		$field_info['profile_id'], 1, $new_position
	);
	
	if (is_numeric($move_id))
	{
		$db->run('UPDATE `'.$profile_fields.'` SET `position`=? WHERE `field_id`=?', $new_position, $field_id);
		$db->run('UPDATE `'.$profile_fields.'` SET `position`=? WHERE `field_id`=?', $position, $move_id);
	}
}

if ($action == 'delete_profile_field')
{
	$field_id       = $_GET['field_id'];
	$profile_fields = DB_PREFIX . 'user_profile_fields';
	$field_info     = $db->assoc('SELECT * FROM `'.$profile_fields.'` WHERE `field_id`=?', $field_id);
	$position       = $field_info['position'];
	
	$db->run('UPDATE `'.$profile_fields.'` SET `position`=(`position`-1) WHERE `profile_id`=? AND `display`=? AND `position`>?', $field_info['profile_id'], 1, $position);
	$db->run('UPDATE `'.$profile_fields.'` SET `position`=?, `display`=? WHERE `field_id`=?', -1, 0, $field_id);
}

if ($action == 'update_payment_settings')
{
	foreach ($_POST as $key=>$val)
	{
		if (is_string($val))
		{
			$$key = trim(stripslashes($val));
		}
	}
	
	$payment_settings = DB_PREFIX . 'pico_payment_settings';
	$db->run('UPDATE `'.$payment_settings.'` SET `payment_method`=?, `test_mode`=?, `admin_email`=?, `pp_address`=?, `authnet_api_login_id`=?, `authnet_api_transaction_key`=?, `vm_merchant_id`=?, `vm_user_id`=?, `vm_pin`=?, `pp_api_user`=?, `pp_api_pass`=?, `pp_api_signature`=?, `shipping_method`=?, `fedex_settings`=?, `ship_settings`=?',
		$payment_method, $test_mode, $admin_email, $pp_address, $authnet_api_login_id, $authnet_api_transaction_key, $vm_merchant_id, $vm_user_id, $vm_pin, $pp_api_user, $pp_api_pass, $pp_api_signature, $shipping_method, serialize($_POST['fedex_settings']), serialize($_POST['ship_settings'])
	);
}

if ($action == 'export_profile_users')
{
	require_once('includes/phpexcel/pico_excel.php');
	$profile_id = $_GET['profile_id'];
	$xls = new PicoExport('Exported Users', 'website_users_'.time().'.xlsx');
	// build headers
	$profile_fields = DB_PREFIX . 'user_profile_fields';
	$group_table    = DB_PREFIX . 'pico_groups';
	$group_info     = array();
	
	$field_list = $db->force_multi_assoc('SELECT * FROM `'.$profile_fields.'` WHERE `profile_id`=? AND `display`=? ORDER BY `position` ASC', $profile_id, 1);
	
	$headers = array();
	
	$headers[] = 'Last Name';
	$headers[] = 'First Name';
	$headers[] = 'E-mail Address';
	$headers[] = 'Expiration';
	$headers[] = 'User Group';
	
	if (is_array($field_list))
	{
		$extra_data = array();
		foreach ($field_list as $field)
		{
			$headers[] = $field['name'];
			$extra_data[] = 'field_' . $field['field_id'];
		}
	}
	
	$xls->addRow($headers);
	
	// get users
	$profile_data_table = DB_PREFIX . 'user_profile_values_' . $profile_id;
	$all_users = $db->force_multi_assoc('SELECT * FROM `'.$profile_data_table.'` P, `'.DB_USER_TABLE.'` D WHERE D.id = P.user_id ORDER BY D.last_name ASC, D.first_name ASC');
	
	if (is_array($all_users))
	{
		foreach ($all_users as $user)
		{
			// get user group
			
			$user_group = Pico_GetUserGroupId($user['user_id']);
			if (is_numeric($user_group))
			{
				if (!isset($group_info[$user_group]))
				{
					$info = $db->assoc('SELECT * FROM `'.$group_table.'` WHERE `group_id`=?', $user_group);
					$group_info[$user_group] = $info;
				}
				
				// its possible that the user has info in this profile, but is no longer a part of this profile
				if ($group_info[$user_group]['profile_id'] == $profile_id)
				{
					$row = array();
					$row[] = $user['last_name'];
					$row[] = $user['first_name'];
					$row[] = $user['email_address'];
					
					$expiration = ($user['user_active'] == 1) ? 'Never' : date('m-d-Y', $user['registration_active']);
					$row[] = $expiration;
					$row[] = $group_info[$user_group]['name'];
					
					if (sizeof($extra_data) > 0)
					{
						foreach ($extra_data as $field)
						{
							$row[] = $user[$field];
						}
					}
					
					$xls->addRow($row);
				}
			}
		}
	}
	
	$xls->output();
}
?>