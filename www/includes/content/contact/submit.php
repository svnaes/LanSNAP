<?php
chdir('../../../');
require_once('core.php');
if (USER_ACCESS < 3) { exit(); }
require_once('includes/content/contact/functions.php');

$action = $_REQUEST['page_action'];
$contact_table = DB_PREFIX . 'pico_contact_form';
$history_table = DB_PREFIX . 'pico_contact_history';

if ($action == 'update')
{
	$component_id = $_POST['component_id'];
	
	// update table if needed
	
	$fields = $db->assoc('SHOW COLUMNS FROM `'.$contact_table.'`');
	$all_fields = array();
	foreach ($fields as $f)
	{
		$all_fields[] = $f['Field'];
	}
	
	if (!in_array('layout', $all_fields))
	{
		$db->run('ALTER TABLE `'.$contact_table.'` ADD COLUMN `layout` VARCHAR(10)');
	}
	if (!in_array('submit_button', $all_fields))
	{
		$db->run('ALTER TABLE `'.$contact_table.'` ADD COLUMN `submit_button` TEXT');
	}
	if (!in_array('preview_message', $all_fields))
	{
		$db->run('ALTER TABLE `'.$contact_table.'` ADD COLUMN `preview_message` TEXT');
	}
	
	$recipient_address = stripslashes($_POST['recipient_address']);
	$from_subject      = stripslashes($_POST['from_subject']);
	$complete_message  = stripslashes($_POST['complete_message']);
	$layout            = stripslashes($_POST['layout']);
	$submit_button     = stripslashes($_POST['submit_button']);
	$remove_button     = stripslashes($_POST['remove_button']);
	$preview_message   = stripslashes($_POST['preview_message']);
	
	if ($remove_button == 1)
	{
		$submit_button = '';
	}
	
	$check = $db->result('SELECT count(*) FROM `'.$contact_table.'` WHERE `component_id`=?', $component_id);
	if ( (int) $check === 0)
	{
		$insert = $db->insert('INSERT INTO `'.$contact_table.'` (`recipient_address`, `from_subject`, `complete_message`, `component_id`, `layout`, `submit_button`, `preview_message`) VALUES (?,?,?,?,?,?,?)',
			$recipient_address, $from_subject, $complete_message, $component_id, $layout, $submit_button, $preview_message
		);
	}
	else
	{
		$update = $db->run('UPDATE `'.$contact_table.'` SET `recipient_address`=?, `from_subject`=?, `complete_message`=?, `layout`=?, `submit_button`=?, `preview_message`=? WHERE `component_id`=?', 
			$recipient_address, $from_subject, $complete_message, $layout, $submit_button, $preview_message, $component_id
		);
	}
	
	exit();
}

if ($action == 'move_field')
{
	$component_id = $_GET['component_id'];
	$field_id     = $_GET['field_id'];
	$direction    = $_GET['direction'];
	
	$fields = $db->result('SELECT `fields` FROM `'.$contact_table.'` WHERE `component_id`=?', $component_id);
	$fields = unserialize($fields);
	if (!is_array($fields)) { $fields = array(); }
	
	$max = sizeof($fields);
	
	$new_position = ($direction == 'up') ? $field_id - 1 : $field_id + 1;
	if ( ($new_position >= 0) and ($new_position < $max) )
	{
		// its ok to swap
		$temp = $fields[$field_id];
		$fields[$field_id] = $fields[$new_position];
		$fields[$new_position]= $temp;
	}
	
	$update = $db->run('UPDATE `'.$contact_table.'` SET `fields`=? WHERE `component_id`=?', serialize($fields), $component_id);
	exit();
}

if ($action == 'delete_field')
{
	$component_id = $_GET['component_id'];
	$field_id     = $_GET['field_id'];
	$fields = $db->result('SELECT `fields` FROM `'.$contact_table.'` WHERE `component_id`=?', $component_id);
	$fields = unserialize($fields);
	if (!is_array($fields)) { $fields = array(); }
	
	
	$new_fields = array();
	for ($x = 0; $x < sizeof($fields); $x++)
	{
		if ($x != $field_id)
		{
			$new_fields[] = $fields[$x];
		}
	}
	
	$update = $db->run('UPDATE `'.$contact_table.'` SET `fields`=? WHERE `component_id`=?', serialize($new_fields), $component_id);
	exit();
}


if ($action == 'edit_field')
{
	$component_id = $_GET['component_id'];
	$edit_field   = $_GET['field_id'];
	include('includes/content/contact/fields.php');
	exit();
}

if ($action == 'update_fields')
{
	$component_id = $_GET['component_id'];
	include('includes/content/contact/fields.php');
	exit();
}

if ($action == 'edit_field_post')
{
	$field_id = $_POST['field_id'];
	$action   = 'add_field';
}
if ($action == 'add_field')
{
	$component_id = $_POST['component_id'];
	$fields = $db->result('SELECT `fields` FROM `'.$contact_table.'` WHERE `component_id`=?', $component_id);
	$fields = unserialize($fields);
	if (!is_array($fields)) { $fields = array(); }
	
	$new_field = array();
	$info = $_POST['field'];
	$name = stripslashes($info['name']);
	if (strlen($name) > 0)
	{
		$new_field['name']     = $name;
		$new_field['caption']  = stripslashes($info['caption']);
		$new_field['type']     = $info['type'];
		$new_field['pattern']  = $info['pattern'];
		$new_field['required'] = $info['required'];
		$new_field['directory_source'] = $info['directory_source'];
		$new_field['directory_field']  = $info['directory_field'];
		
		$options = explode("\n", stripslashes($info['options']));
		
		$new_field['options'] = array();
		foreach ($options as $o)
		{
			$o = trim($o);
			if (strlen($o) > 0)
			{
				$new_field['options'][] = $o;
			}
		}
		
		if (isset($field_id))
		{
			$fields[$field_id] = $new_field;
			echo '!!!!!';
		}
		else
		{
			$fields[] = $new_field;
		}
		
		$update = $db->run('UPDATE `'.$contact_table.'` SET `fields`=? WHERE `component_id`=?', serialize($fields), $component_id);
	}
	exit();
}

if ($action == 'clear_download')
{
	$component_id = $_GET['component_id'];
	$storage = 'includes/content/contact/storage/' . $component_id . '/';
	if (file_exists($storage))
	{
		// see if there are any files in this folder, if so, delete
		if (is_dir($storage))
		{
			if ($dh = opendir($storage))
			{
				while (($file = readdir($dh)) !== false)
				{
					$full_file = $storage . $file;
					if (filetype($full_file) == 'file')
					{
						unlink($full_file);
					}
				}
				closedir($dh);
			}
		}
		rmdir($storage);
	}
}

if ($action == 'clear_history')
{
	$component_id = $_GET['component_id'];
	$db->run('DELETE FROM `'.$history_table.'` WHERE `component_id`=?', $component_id);
}

if ($action == 'get_directory_fields')
{
	$dir_component_id = $_GET['dir_component_id'];
	$component_id     = $_GET['component_id'];
	
	$output = CF_GetDirectoryFields($component_id, $dir_component_id);
	echo $output;
}

?>