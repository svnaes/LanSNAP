<?php
// get all the links....

$links = $db->force_multi_assoc('SELECT `page_id`, `name` FROM `'.DB_PAGES_TABLE.'`');
$all_links = array();
if (sizeof($links) > 0)
{
	foreach ($links as $link)
	{
		$all_links[$link['page_id']] = $link['name'] . ' - Page';
	}
}

$links = $db->force_multi_assoc('SELECT `link_id`, `name` FROM `'.DB_LINKS.'`');
if ( (sizeof($links) > 0) and (is_array($links)) )
{
	foreach ($links as $link)
	{
		$all_links['l_'.$link['link_id']] = $link['name'] . ' - URL';
	}
}

$available_links = '';
$all_links = asorti($all_links, SORT_STRING);
if (sizeof($all_links) > 0)
{
	foreach($all_links as $id=>$desc)
	{
		$available_links .= '<option value="'.$id.'">'.$desc.'</option>';
	}
}

// get all the current links

$content = $db->result('SELECT `content` FROM `'.DB_CONTENT.'` WHERE `instance_id`=?', $instance_id);
if ($content == FALSE) { $content = ''; }

$data = explode(',', $content);
if (!is_array($data)) { $data = array(); }

$current_links = array();
if (sizeof($data) > 0)
{
	foreach($data as $key=>$val)
	{
		if (strlen($val) > 0)
		{
			$current_links[$key] = $val;
		}
	}
}

$current_link_text = '';

foreach ($current_links as $key=>$val)
{
	// $key is really just the position
	// $val has all the money
	
	$link    = explode('_', $val);
	$link_id = array_pop($link);
	$tabbed  = FALSE;
	
	if (in_array('l', $link))
	{
		// get link name from link table
		$link_name = $all_links['l_' . $link_id];
	}
	else
	{
		// link name from pages
		$link_name = $all_links[$link_id];
	}
	
	if (in_array('t', $link))
	{
		$link_name .= ' (tabbed)';
	}
	
	$current_link_text .= '<option value="'.$val.'">'.$link_name.'</option>';
}

?>
<form method="post" id="linkmenu_form" action="<?=$body->url('includes/content/linkmenu/submit.php')?>" onsubmit="LM_LinkSubmit(this); return false">
<input type="hidden" name="page_action" value="update_links" />
<input type="hidden" name="instance_id" value="<?=$instance_id?>" />
<input type="hidden" name="component_id" value="<?=$component_id?>" />
<table border="0" cellpadding="2" cellspacing="0" id="lm_select">
<tr>
	<td valign="top" width="250">
		Current Links<br />
		<select id="current_links" name="current_links[]" multiple="multiple" style="width: 100%; height: 425px">
			<?=$current_link_text?>
		</select>
	</td><td valign="top" width="75">
		Actions<br />
		<button style="width: 75px" onclick="LM_SelectAdd(); return false">&lt;&lt;</button><br />
		<button style="width: 75px" onclick="LM_SelectDelete(); return false">&gt;&gt;</button><br />
		<button style="width: 75px" onclick="LM_SelectUp(); return false">Up</button><br />
		<button style="width: 75px" onclick="LM_SelectDown(); return false">Down</button><br />
		<input type="checkbox" name="is_tabbed" id="is_tabbed" /> <label for="is_tabbed">Tabbed</label>
	</td><td valign="top" width="250">
		Available Links<br />
		<select name="available_links" multiple="multiple"  style="width: 100%; height: 95%">
			<?=$available_links?>
		</select>
	</tr>
</tr>
</table>
<input type="submit" value="Update" />
</form>