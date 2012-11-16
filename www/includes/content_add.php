<?php
chdir('../');
require_once('core.php');
if (USER_ACCESS < 3) { exit(); }

$location = $_GET['location'];
$type     = $_GET['type'];
$page_id  = $_GET['page_id'];

if ($type == 1)
{
	$drop    = array();
	$last_section = '';
	//$section = array();
	
	$components = $db->force_multi_assoc('SELECT `component_id`, `description`, `folder` FROM `'.DB_COMPONENT_TABLE.'` ORDER BY `folder`');
	foreach ($components as $component)
	{
		$component_id = $component['component_id'];
		$count = $db->result('SELECT count(*) FROM `'.DB_CONTENT_LINKS.'` WHERE `page_id`=? AND `component_id`=?', $page_id, $component_id);
		
		if ($count == 0)
		{
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
	}
	$drop[$last_section] = $section;
	
	$components = '<select name="copy_component">';
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
<div class="ap_title">Select a component to copy:</div>
<form method="post" action="<?=$body->url('includes/ap_actions.php')?>" onsubmit="Pico_AddContentSubmit(this); return false" id="add_content_form">
<input type="hidden" name="ap_action"    value="add_content" />
<input type="hidden" name="location"     value="<?=$location?>" />
<input type="hidden" name="page_id"      value="<?=$_GET['page_id']?>" />
<input type="hidden" name="ru"           value="<?=$_GET['ru']?>" />
<?=$components?><br />
<input type="submit" name="submitbtn" value="Copy" />
</form>

	<?php
	
	exit();
}

$content_files  = GetContentDirs();
$content_select = '<select name="content_type" onchange="Pico_SelectContent(this)" class="float_left"><option value=""></option>';
$descriptions   = '';

$content_select_options = array();

foreach($content_files as $file)
{
	include($file);
	$value = TopDir($file);
	
	$content_select_options[] = array(
		'value'=>$value,
		'display'=>$options['title']
	);
	//$content_select .= '<option value="'.$value.'">'.$options['title'].'</option>';
	$descriptions .= '<div id="content_description_'.$value.'" class="content_description">'.$options['description'].'</div>';
}

usort($content_select_options, 'SortSelectOptions');

foreach ($content_select_options as $option)
{
	$content_select .= '<option value="'.$option['value'].'">'.$option['display'].'</option>';
}

$content_select .= '</select>';



?>
<form method="post" action="<?=$body->url('includes/ap_actions.php')?>" onsubmit="Pico_AddContentSubmit(this); return false" id="add_content_form">
<input type="hidden" name="ap_action" value="add_content" />
<input type="hidden" name="location"  value="<?=$location?>" />
<input type="hidden" name="page_id"   value="<?=$_GET['page_id']?>" />
<input type="hidden" name="ru"        value="<?=$_GET['ru']?>" />

<table border="0" cellpadding="2" cellspacing="1">
<tr>
	<td class="bold">Content Type</td>
	<td><?=$content_select?><div id="content_type_indicator" class="indicator"></div></td>
</tr>
<tr>
	<td colspan="2"><?=$descriptions?></td>
</tr>
<tr>
	<td class="bold">Description</td>
	<td><input type="text" class="ap_text float_left" name="description" onkeyup="Pico_VerifyContentDescription(this)" /><div id="content_description_indicator" class="indicator"></div></td>
</tr>
<tr>
	<td class="bold">View Setting</td>
	<td><?=ViewtypeDrop('view_setting')?></td>
</tr>
<tr>
	<td class="bold">Required Access</td>
	<td><?=AccessDrop('access')?></td>
</tr>
</table>
<input type="submit" name="submitbtn" value="Add" disabled="disabled" />
</form>