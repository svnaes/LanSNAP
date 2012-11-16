<?php
$data = $db->result('SELECT `additional_info` FROM `'.DB_COMPONENT_TABLE.'` WHERE `component_id`=?', $component_id);
$gallery_options = unserialize($data);
$config_file     = 'includes/content/media/galleries/'.$gallery_options['gallery_style'].'/config.php';

unset($settings);
include($config_file);
if (!isset($settings))
{
	echo 'No settings for this gallery';
	return;
}

$table = '';

foreach ($settings as $key=>$data)
{
	if ($data['type'] == 'text')
	{
		$val = (isset($gallery_options[$key])) ? $gallery_options[$key] : $data['default'];
		$field = '<input type="text" value="'.$val.'" name="'.$key.'" />';
	}
	elseif ($data['type'] == 'select')
	{
		$field = '<select name="'.$key.'">';
		foreach($data['values'] as $pval)
		{
			if (isset($gallery_options[$key]))
			{
				$selected = ($pval == $gallery_options[$key]) ? 'selected="selected"' : '';
			}
			else
			{
				$selected = ($pval == $data['default']) ? 'selected="selected"' : '';
			}
			
			$field .= '<option value="'.$pval.'" '.$selected.'>'.$pval.'</option>';
		}
		$field .= '</select>';
	}
	
	if ($data['type'] == 'info')
	{
		$table .= '<tr><td class="bold" colspan="2">'.$data['text'].'</td></tr>';
	}
	else
	{
		$table .= '<tr><td>'.$data['name'].'</td><td>'.$field.'</td></tr>';
	}
}

?>
<form method="post" action="<?=$body->url('includes/content/media/submit.php')?>" onsubmit="MG_UpdateOptions(this); return false" />
	<input type="hidden" name="component_id" value="<?=$component_id?>" />
	<input type="hidden" name="page_action" value="update_options" />
	<table border="0" cellpadding="2" cellspacing="1">
	<?=$table?>
	</table>
	<input type="submit" value="Update" name="submitbtn" />
</form>
