<?php
chdir('../');
require_once('core.php');
if (USER_ACCESS < 5) { exit(); }

if (isset($_POST['page_action']))
{
	$action = $_POST['page_action'];
	if ($action == 'bulk_add')
	{
		$location  = $_POST['location']; // the contentdiv
		$component = $_POST['component']; // the component ID to insert
		$position  = $_POST['position']; // top or bottom of the contentdiv
		$pages     = $_POST['pages']; // the page_id's to do this to...
		
		foreach ($pages as $page_id)
		{
			// we need to get the insert position
			if ($position == 'top')
			{
				// bump everything else up
				$db->run('UPDATE `'.DB_CONTENT_LINKS.'` SET `position`=(`position`+1) WHERE `page_id`=? AND `location`=?', $page_id, $location);
				$db->run('INSERT INTO `'.DB_CONTENT_LINKS.'` (`page_id`, `component_id`, `position`, `location`) VALUES (?,?,?,?)', $page_id, $component, 0, $location);
			}
			else
			{
				// get the next position
				$current_position = $db->result('SELECT `position` FROM `'.DB_CONTENT_LINKS.'` WHERE `page_id`=? AND `location`=? ORDER BY `position` DESC LIMIT 1', $page_id, $location);
				$new_position = ($current_position === FALSE) ? 0 : $current_position + 1;
				$db->run('INSERT INTO `'.DB_CONTENT_LINKS.'` (`page_id`, `component_id`, `position`, `location`) VALUES (?,?,?,?)', $page_id, $component, $new_position, $location);
			}
		}
	}
	exit();
}

$page_id  = $_GET['page_id'];

$theme = $db->result('SELECT `theme` FROM `'.DB_PAGES_TABLE.'` WHERE `page_id`=?', $page_id);
if ($theme === FALSE) { echo 'Error finding theme'; return; }

$body_file = 'themes/'.$theme.'/body.php';
if (!file_exists($body_file)) { echo 'Error finding theme'; return; }

$file_html = file_get_contents($body_file);

// get all the positions...

preg_match_all('/ContentDiv\([\'"]([^\)]+)[\'"]\)/', $file_html, $matches);

if (!isset($matches[1])) { echo 'No ContentDiv\'s Found'; return; }

$locations = '';
foreach ($matches[1] as $spot)
{
	$locations .= '<option value="'.$spot.'">'.$spot.'</option>';
}

$pages = '';
$all_pages = $db->force_multi_assoc('SELECT * FROM `'.DB_PAGES_TABLE.'` WHERE `theme`=?', $theme);
foreach ($all_pages as $page)
{
	$pages .= '<option value="'.$page['page_id'].'">'.$page['name'].'</option>';
}

// stolen from content_add...
$drop    = array();
$last_section = '';

$components = $db->force_multi_assoc('SELECT `component_id`, `description`, `folder` FROM `'.DB_COMPONENT_TABLE.'` ORDER BY `folder` ASC, `description` ASC');
foreach ($components as $component)
{
	$component_id = $component['component_id'];
	
	if ($last_section != $component['folder'])
	{
		if (sizeof($section) > 0)
		{
			$drop[$last_section] = $section;
		}
		
		$last_section = $component['folder'];
		$section = array();
	}
	$section[$component_id] = $component['description'];
	
}
$drop[$last_section] = $section;

$components = '<select name="component">';
if (sizeof($drop) > 0)
{
	foreach ($drop as $folder=>$section)
	{
		$options = GetContentOptions($folder);
		
		$components .= '<optgroup label="'.$options['title'].'">';
		foreach ($section as $key => $val)
		{
			$components .= '<option value="'.$key.'">'.$val.'</option>';
		}
		$components .= '</optgroup>';
	}
}
$components .= '</select>';

?>
<form method="post" action="<?=$body->url('includes/content_add_bulk.php')?>" onsubmit="Pico_BulkAddSubmit(this); return false">
<input type="hidden" name="page_action" value="bulk_add" />
<h3>Choose a Theme Location</h3>
<select name="location">
	<?=$locations?>
</select>
<h3>Choose a Position</h3>
<select name="position">
	<option value="top">Top</option>
	<option value="bottom">Bottom</option>
</select>
<h3>Choose Content</h3>
<?=$components?>
<h3>Choose Page(s)</h3>
<select name="pages[]" multiple="multiple" style="height: 325px">
	<?=$pages?>
</select>
<br />
<input type="submit" value="Add" />
</form>