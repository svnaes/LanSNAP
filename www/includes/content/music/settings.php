<?php
if ($_GET['reload'] == true)
{
	chdir('../../../');
	require_once('core.php');
	if (USER_ACCESS < 3) { exit(); }
	$component_id = $_GET['component_id'];
}

$data   = $db->result('SELECT `additional_info` FROM `'.DB_COMPONENT_TABLE.'` WHERE `component_id`=?', $component_id);
$options = unserialize($data);
if (!is_array($options)) { $options = array(); }

$style_dir = 'includes/content/music/players/';
$style_drop = '';
if ($h = opendir($style_dir))
{
    while (false !== ($file = readdir($h)))
	{
        $full_file = $style_dir . $file;
		if  ( (is_dir($full_file)) and (strlen($file) > 2) )
		{
			$config_file = $full_file . '/config.php';
			if (file_exists($config_file))
			{
				include($config_file);
				$selected = ($options['player'] == $file) ? 'selected="selected"' : '';
				$fname = ucwords(str_replace('_', ' ', $file));
				$style_drop .= '<option value="'.$file.'" '.$selected.'>'.$fname.'</option>';
			}
		}
    }
}

?>
<div class="ap_overflow">
<form method="post" action="<?=$body->url('includes/content/music/submit.php')?>" onsubmit="MP3_UpdateOptions(this); return false" />
	<input type="hidden" name="component_id" id="component_id" value="<?=$component_id?>" />
	<input type="hidden" name="page_action" value="update_options" />
	<table border="0" cellpadding="2" cellspacing="1">
	<tr>
		<td>Player</td>
		<td>
			<select name="options[player]">
				<?=$style_drop?>
			</select>
		</td>
	</tr>
	<tr>
		<td>Auto Play</td>
		<td>
			<select name="options[autoplay]">
				<option <?=($options['autoplay'] == 'true') ? 'selected="selected"' : ''?> value="true">On</option>
				<option <?=($options['autoplay'] == 'false') ? 'selected="selected"' : ''?> value="false">Off</option>
			</select>
		</td>
	</tr>
<?php
if ( (isset($options['player'])) and (strlen($options['player']) > 0) )
{
	// get additional options
	$external = 'includes/content/music/players/'.$options['player'].'/config.php';
	include($external);
	
	$settings = $config['additional_settings'];
	$table = '';
	
	if (sizeof($settings) > 0)
	{
		foreach ($settings as $key=>$data)
		{
			if ($data['type'] == 'text')
			{
				$val = (isset($options[$key])) ? $options[$key] : $data['default'];
				$field = '<input type="text" value="'.$val.'" name="options['.$key.']" />';
			}
			elseif ($data['type'] == 'select')
			{
				$field = '<select name="options['.$key.']">';
				foreach($data['values'] as $pval)
				{
					if (isset($options[$key]))
					{
						$selected = ($pval == $options[$key]) ? 'selected="selected"' : '';
					}
					else
					{
						$selected = ($pval == $data['default']) ? 'selected="selected"' : '';
					}
					
					switch($pval)
					{
						case 'true':
							$pvaltext = 'Yes'; break;
						case 'false':
							$pvaltext = 'No'; break;
						default:
							$pvaltext = $pval;
					}
					
					$field .= '<option value="'.$pval.'" '.$selected.'>'.$pvaltext.'</option>';
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
	}
	
	echo $table;
}
?>
</table>
<input type="submit" value="Update" name="submitbtn" />
</form>
</div>