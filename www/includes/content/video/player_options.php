<h3>Player Options</h3>
<?php

$data = $db->result('SELECT `additional_info` FROM `'.DB_COMPONENT_TABLE.'` WHERE `component_id`=?', $component_id);
$player_options = unserialize($data);
if (!is_array($player_options)) { $player_options = array(); }

$settings = array(
	'newWidth'=>array(
		'name'=>'Width',
		'type'=>'text',
		'default'=>'400',
	),
	'newHeight'=>array(
		'name'=>'Height',
		'type'=>'text',
		'default'=>'300',
	),
	'volAudio'=>array(
		'name'=>'Audio (0-100)',
		'type'=>'text',
		'default'=>'60',
	),
	'autoStart'=>array(
		'name'=>'Auto Start',
		'type'=>'select',
		'values'=>array('true', 'false'),
		'default'=>'false',
	),
	'disableMiddleButton'=>array(
		'name'=>'Disable Middle Button',
		'type'=>'select',
		'values'=>array('true', 'false'),
		'default'=>'true',
	),
	'playSounds'=>array(
		'name'=>'Play Sounds',
		'type'=>'select',
		'values'=>array('true', 'false'),
		'default'=>'true',
	),
	'barColor'=>array(
		'name'=>'Bar Color',
		'type'=>'text',
		'default'=>'0x0066ff',
	),
	'barShadowColor'=>array(
		'name'=>'Bar Shadow Color',
		'type'=>'text',
		'default'=>'0x91bbfb',
	),
	'subbarColor'=>array(
		'name'=>'Sub-Bar Color',
		'type'=>'text',
		'default'=>'0xffffff',
	),
	/*
	'autoHide'=>array(
		'name'=>'Auto Hide Controls',
		'type'=>'select',
		'values'=>array('true', 'false'),
		'default'=>'true',
	),
	'autoHideTime'=>array(
		'name'=>'Auto Hide Time',
		'type'=>'text',
		'default'=>'3',
	),*/
);

$table = '';
foreach ($settings as $key=>$data)
{
	if ($data['type'] == 'text')
	{
		$val = (isset($player_options[$key])) ? $player_options[$key] : $data['default'];
		$field = '<input type="text" value="'.$val.'" name="option_'.$key.'" />';
	}
	elseif ($data['type'] == 'select')
	{
		$field = '<select name="option_'.$key.'">';
		foreach($data['values'] as $pval)
		{
			if (isset($player_options[$key]))
			{
				$selected = ($pval == $player_options[$key]) ? 'selected="selected"' : '';
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
<form method="post" action="<?=$body->url('includes/content/video/submit.php')?>" onsubmit="VP_UpdateOptions(this); return false">
<input type="hidden" name="component_id" value="<?=$component_id?>" />
<input type="hidden" name="page_action" value="update_options" />
<table border="0" cellpadding="2" cellspacing="1">
	<?=$table?>
</table>
<input type="submit" name="submitbtn" value="Update" />
</form>